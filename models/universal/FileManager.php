<?php

declare(strict_types=1);

require_once __DIR__ . '/ApiResponse.php';

abstract class FileManager extends ApiResponse
{
    private readonly string $host;
    private const string ROOT_DIRECTORY = __DIR__ . '/../../';
    private const string IMAGE_PATH = '/assets/img/';
    private const string DEFAULT_IMAGE = 'default/default.jpg';
    private const string IMAGE_FOLDER_RELATIVE_RUTE = self::ROOT_DIRECTORY . self::IMAGE_PATH;
    private readonly string $defaultImage;

    protected function __construct()
    {
        parent::__construct();

        $this->setHost();
        $this->setDefaultImage();
    }

    // MARK: GETTERS

    protected function getHost(): string
    {
        return $this->host;
    }

    protected function getDefaultImage(): string
    {
        return $this->defaultImage;
    }

    // MARK: SETTERS

    private function setDefaultImage(): void
    {
        $this->defaultImage = $this->getHost() . self::IMAGE_PATH . self::DEFAULT_IMAGE;
    }

    private function setHost(): void
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        $protocol = $https ? 'https://' : 'http://';

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $this->host = $protocol . $host;
    }

    // MARK: FILE OPERATIONS

    protected function flattenFilesArray(string $inputFileName): array
    {
        if (!isset($_FILES[$inputFileName])) {
            return [];
        }

        $files = $_FILES[$inputFileName];
        $newArray = [];

        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ((int)$files['error'][$i] === 0) {
                    $newArray[] = [
                        'name'     => $files['name'][$i],
                        'type'     => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error'    => $files['error'][$i],
                        'size'     => $files['size'][$i],
                    ];
                }
            }
        } else {
            if ((int)$files['error'] === 0) {
                $newArray[] = $files;
            }
        }

        return $newArray;
    }

    private function generateUniqueFilename(string $originalFilename): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $filename = pathinfo($originalFilename, PATHINFO_FILENAME);
        return $filename . '-' . bin2hex(random_bytes(2)) . '.' . $extension;
    }

    protected function uploadFile(?array $file): ?string
    {
        if ($file === null) {
            return null;
        }

        if (!file_exists(self::IMAGE_FOLDER_RELATIVE_RUTE)) {
            mkdir(self::IMAGE_FOLDER_RELATIVE_RUTE, 0755, true);
        }

        $uniqueFilename = $this->generateUniqueFilename($file['name']);
        $destination = self::IMAGE_FOLDER_RELATIVE_RUTE . $uniqueFilename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException("Error uploading file: " . $uniqueFilename);
        }

        return self::IMAGE_PATH . $uniqueFilename;
    }

    protected function updateFile(?array $file, bool $checkbox, int $fileId): ?string
    {
        if ($file === null) {
            if ($checkbox === true) {
                $this->deleteFile($fileId);
                return null;
            }
            return $this->getFileUrl($fileId);
        }

        $this->deleteFile($fileId);

        $fileUrl = $this->uploadFile($file);
        return $fileUrl;
    }

    protected function deleteFile(int $fileId): void
    {
        $fileUrl = $this->getFileUrl($fileId);

        if ($fileUrl !== null) {
            unlink(self::ROOT_DIRECTORY . $fileUrl);
        }
    }

    protected function deleteAllFiles(): void
    {
        $folder_path = self::ROOT_DIRECTORY . self::IMAGE_PATH;

        $files = glob($folder_path . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function getFileUrl(int $fileId): ?string
    {
        $statement =
            "SELECT portada
            FROM libros
            WHERE id_libro = ?";

        $query = $this->getConnection()->prepare($statement);

        $query->bind_param(
            "i",
            $fileId
        );

        $query->execute();
        $fileUrl = $query->get_result()->fetch_column();
        $query->close();

        return $fileUrl;
    }
}
