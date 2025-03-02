<?php

declare(strict_types=1);

require_once __DIR__ . '/ApiResponse.php';

abstract class FileManager extends ApiResponse
{
    private readonly string $host;
    private const string ROOT_DIRECTORY = __DIR__ . '/../../';
    private const string IMAGE_PATH = 'assets/img/';
    private const string DEFAULT_IMAGE = 'default/default.jpg';

    protected function __construct()
    {
        parent::__construct();

        $this->setHost();
    }

    // MARK: GETTERS

    private function getHost(): string
    {
        return $this->host;
    }

    // MARK: SETTERS

    // private function setHost(): void
    // {
    //     $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    //     $host = $_SERVER['HTTP_HOST'];

    //     $this->host = $esquema . $host;
    // }

    private function setHost(): void
    {
        // Determine protocol, default to HTTP if conditions not met.
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
        $flattened = [];

        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ((int)$files['error'][$i] === 0) {
                    $flattened[] = [
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
                $flattened[] = $files;
            }
        }

        return $flattened;
    }


    protected function uploadFile(?array $file): string
    {
        if ($file === null) {
            return $this->getHost() . '/api-endpoints/assets/img/' . self::DEFAULT_IMAGE;
        }

        $filename = basename($file["name"]);
        $destination = self::ROOT_DIRECTORY . self::IMAGE_PATH . $filename;

        if (!move_uploaded_file($file["tmp_name"], $destination)) {
            throw new RuntimeException("Error uploading file: " . $filename);
        }

        return $this->getHost() . '/api-endpoints/assets/img/' . $filename;
    }

    protected function updateFile(?array $file, bool $checkbox, int $fileId): string
    {
        if ($file === null) {
            if ($checkbox) {
                $this->deleteFile($fileId);
                return $this->getHost() . '/api-endpoints/assets/img/default/default.jpg';
            }
            return $this->getFileUrl($fileId);
        }

        $this->deleteFile($fileId);

        $filename = basename($file["name"]);
        $destination = self::ROOT_DIRECTORY . self::IMAGE_PATH . $filename;

        if (!move_uploaded_file($file["tmp_name"], $destination)) {
            throw new RuntimeException("Failed to move the uploaded file.");
        }

        return $this->getHost() . '/api-endpoints/assets/img/' . $filename;
    }

    protected function deleteFile(int $fileId): void
    {
        $fileUrl = $this->getFileUrl($fileId);

        $defaultImage = strpos($fileUrl, 'default/default.jpg');

        if ($defaultImage === false) {
            $position = strpos($fileUrl, 'assets');
            $relativeImageRoute = substr($fileUrl, $position);
            unlink(self::ROOT_DIRECTORY . $relativeImageRoute);
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

    private function getFileUrl(int $fileId): string
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
