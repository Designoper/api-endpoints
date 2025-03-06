<?php

declare(strict_types=1);

require_once __DIR__ . '/ApiResponse.php';

abstract class FileManager extends ApiResponse
{
    private readonly string $host;
    private const string IMAGE_PATH = '/assets/img/';
    protected const string DEFAULT_IMAGE = self::IMAGE_PATH . 'default/default.jpg';
    protected string $extraDirectories = '';
    protected string $uniqueFilename;

    private readonly ?array $file;
    protected readonly bool $deleteCheckbox;

    protected function __construct()
    {
        parent::__construct();

        $this->setHost();
    }

    // MARK: GETTERS

    protected function getHost(): string
    {
        return $this->host;
    }

    protected function getFile(): ?array
    {
        return $this->file;
    }

    // MARK: SETTERS

    private function setHost(): void
    {
        $protocol = $_SERVER['REQUEST_SCHEME'] ?? 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $this->host = $protocol . '://' . $host;
    }

    protected function setFile(?array $file): void
    {
        $this->file = $file;
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

    private function generateUniqueFilename(string $originalFilename): void
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $filename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $this->uniqueFilename = $filename . '-' . bin2hex(random_bytes(2)) . '.' . $extension;
    }

    protected function uploadFileName(): ?string
    {
        if ($this->getFile() === null) {
            return null;
        }

        $this->generateUniqueFilename($this->getFile()['name']);

        return self::IMAGE_PATH . $this->extraDirectories . $this->uniqueFilename;
    }

    protected function uploadFile(): void
    {
        if ($this->getFile() === null) {
            return;
        }

        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . self::IMAGE_PATH . $this->extraDirectories)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . self::IMAGE_PATH . $this->extraDirectories, 0755, true);
        }

        $destination = $_SERVER['DOCUMENT_ROOT'] . self::IMAGE_PATH . $this->extraDirectories . $this->uniqueFilename;

        move_uploaded_file($this->getFile()['tmp_name'], $destination);
    }

    protected function updateFileName(int $fileId): ?string
    {
        if ($this->getFile() === null) {
            if ($this->deleteCheckbox === true) {
                return null;
            }
            $fileUrl = $this->getFileUrl($fileId);
            return $fileUrl;
        }

        $this->generateUniqueFilename($this->getFile()['name']);

        return self::IMAGE_PATH . $this->extraDirectories . $this->uniqueFilename;
    }

    protected function updateFile(?string $filePath): void
    {
        if ($this->getFile() === null) {
            if ($this->deleteCheckbox === true) {
                $this->deleteFile($filePath);
                return;
            }
            return;
        }

        $this->deleteFile($filePath);
        $this->uploadFile();
    }

    protected function deleteFile(?string $filePath): void
    {
        if ($filePath !== null) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $filePath);
        }
    }

    protected function deleteAllFiles(): void
    {
        $folderPath = $_SERVER['DOCUMENT_ROOT'] . self::IMAGE_PATH . $this->extraDirectories;

        if (!is_dir($folderPath)) {
            return;
        }

        $directoryIterator = new DirectoryIterator($folderPath);

        foreach ($directoryIterator as $file) {
            if ($file->isFile()) {
                unlink($file->getRealPath());
            }
        }
    }

    protected function getFileUrl(int $fileId): ?string
    {
        $statement =
            "SELECT portada
            FROM libros
            WHERE id_libro = ?";

        $query = $this->connection->prepare($statement);

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
