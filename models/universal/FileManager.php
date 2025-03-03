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

    private readonly ?array $file;
    private readonly string $uniqueFilename;
    private readonly string $fileUrl;
    private readonly string $fileDestination;
    private bool $deleteCurrentFileCheckbox = false;

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

    protected function getFileUrl2(): string
    {
        return $this->fileUrl;
    }

    // MARK: SETTERS

    protected function setFile(?array $file): void
    {
        $this->file = $file;
    }

    private function setUniqueFilename(): void
    {
        $extension = pathinfo($this->file['name'], PATHINFO_EXTENSION);
        $filename = pathinfo($this->file['name'], PATHINFO_FILENAME);
        $this->uniqueFilename = $filename . '-' . bin2hex(random_bytes(2)) . '.' . $extension;
    }

    private function setFileDestination(): void
    {
        $this->fileDestination = self::IMAGE_FOLDER_RELATIVE_RUTE . $this->uniqueFilename;
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

    protected function nameUploadFile(): void
    {
        if ($this->file === null) {
            $this->fileUrl = $this->getHost() . self::IMAGE_PATH . self::DEFAULT_IMAGE;
            return;
        }

        $this->setUniqueFilename();
        $this->fileUrl = $this->getHost() . self::IMAGE_PATH . $this->uniqueFilename;
    }



    protected function uploadFile(): void
    {
        if ($this->file === null) {
            return;
        }

        if (!file_exists(self::IMAGE_FOLDER_RELATIVE_RUTE)) {
            if (!mkdir(self::IMAGE_FOLDER_RELATIVE_RUTE, 0755, true)) {
                throw new RuntimeException("Failed to create directory:");
            }
        }

        $this->setFileDestination();

        if (!move_uploaded_file($this->file['tmp_name'], $this->fileDestination)) {
            throw new RuntimeException("Error uploading file");
        }
    }



    protected function updateFile(?array $file, bool $checkbox, int $fileId)
    {
        // if ($file === null) {
        //     if ($checkbox) {
        //         $this->deleteFile($fileId);
        //         return $this->getHost() . self::IMAGE_PATH . self::DEFAULT_IMAGE;
        //     }
        //     return $this->getFileUrl($fileId);
        // }

        // $this->deleteFile($fileId);

        // $fileUrl = $this->uploadFile($file);
        // return $fileUrl;
    }








    protected function deleteFile(int $fileId): void
    {
        $fileUrl = $this->getFileUrl($fileId);

        $defaultImage = strpos($fileUrl, self::DEFAULT_IMAGE);

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
