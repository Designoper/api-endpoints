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

    private function setHost(): void
    {
        $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        $host = $_SERVER['HTTP_HOST'];

        $this->host = $esquema . $host;
    }

    // MARK: FILE OPERATIONS

    protected function flattenFilesArray(string $inputFileName): array
    {
        if (!isset($_FILES[$inputFileName])) {
            return [];
        }

        $files = $_FILES[$inputFileName];
        $flattened = [];

        // Check if the file input is multiple.
        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                // Only include files without errors.
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
            // Single file input.
            if ((int)$files['error'] === 0) {
                $flattened[] = $files;
            }
        }

        return $flattened;
    }


    protected function uploadFile(?array $file): string
    {
        if ($file === null) {
            return $this->getHost() . '/api-endpoints/assets/img/default/default.jpg';
        }

        $url_completa = $this->getHost() . '/api-endpoints/assets/img/' . $file["name"];

        $destination = self::ROOT_DIRECTORY . self::IMAGE_PATH . $file["name"];

        move_uploaded_file(
            $file["tmp_name"],
            $destination
        );

        return $url_completa;
    }

    protected function updateFile(?array $file, bool $checkbox, int $idFile): string
    {
        if ($file === null) {
            if ($checkbox) {
                $this->deleteFile($idFile);
                return $this->getHost() . '/api-endpoints/assets/img/default/default.jpg';
            }
            return $this->getFileUrl($idFile);
        }

        $this->deleteFile($idFile);

        $filename = basename($file["name"]);
        $destination = self::ROOT_DIRECTORY . self::IMAGE_PATH . $filename;

        if (!move_uploaded_file($file["tmp_name"], $destination)) {
            throw new RuntimeException("Failed to move the uploaded file.");
        }

        return $this->getHost() . '/api-endpoints/assets/img/' . $filename;
    }

    protected function deleteFile(int $idFile): void
    {
        $imageUrl = $this->getFileUrl($idFile);

        $defaultImage = strpos($imageUrl, 'default/default.jpg');

        if ($defaultImage === false) {
            $position = strpos($imageUrl, 'assets');
            $relativeImageRoute = substr($imageUrl, $position);
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

    private function getFileUrl(int $idFile): string
    {
        $statement =
            "SELECT portada
            FROM libros
            WHERE id_libro = ?";

        $query = $this->getConnection()->prepare($statement);

        $query->bind_param(
            "i",
            $idFile
        );

        $query->execute();
        $data = $query->get_result()->fetch_column();
        $query->close();

        return $data;
    }
}
