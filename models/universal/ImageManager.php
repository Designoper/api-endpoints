<?php

declare(strict_types=1);

require_once __DIR__ . '/ApiResponse.php';

abstract class ImageManager extends ApiResponse
{
    private readonly string $host;
    private const string PROJECT_ROOT = __DIR__ . '/../../';
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
        $files = isset($_FILES[$inputFileName]) ? $_FILES[$inputFileName] : [];
        $files2 = [];

        if (!empty($files)) {
            $filesByInput = [];

            foreach ($files as $key => $valueArr) {
                // file input "multiple"
                if (is_array($valueArr)) {
                    foreach ($valueArr as $i => $value) {
                        $filesByInput[$i][$key] = $value;
                    }
                }
                // string, normal file input
                else {
                    $filesByInput[] = $files;
                    break;
                }
            }

            $files2 = array_merge($files2, $filesByInput);
        }

        $files3 = [];

        foreach ($files2 as $file) { // filter out empty & errors
            if (!$file['error']) {
                $files3[] = $file;
            }
        }

        return $files3;
    }


    protected function uploadFile(?array $file): string
    {
        if ($file === null) {
            return $this->getHost() . '/api-endpoints/assets/img/default/default.jpg';
        }

        $url_completa = $this->getHost() . '/api-endpoints/assets/img/' . $file["name"];

        $destination = self::PROJECT_ROOT . self::IMAGE_PATH . $file["name"];

        move_uploaded_file(
            $file["tmp_name"],
            $destination
        );

        return $url_completa;
    }

    protected function updateFile(?array $file, bool $checkbox, int $idLibro): string
    {
        if ($file === null && $checkbox === false) {
            $imageCurrentUrl = $this->getFileUrl($idLibro);
            return $imageCurrentUrl;
        }

        if ($file === null && $checkbox === true) {
            $this->deleteFile($idLibro);
            return $this->getHost() . '/api-endpoints/assets/img/default/default.jpg';
        }

        if ($file) {
            $this->deleteFile($idLibro);

            $url_completa = $this->getHost() . '/api-endpoints/assets/img/' . $file["name"];

            $destination = self::PROJECT_ROOT . self::IMAGE_PATH . $file["name"];

            move_uploaded_file(
                $file["tmp_name"],
                $destination
            );

            return $url_completa;
        }
    }

    protected function deleteFile(int $idLibro): void
    {
        $imageUrl = $this->getFileUrl($idLibro);

        $defaultImage = strpos($imageUrl, 'default/default.jpg');

        if ($defaultImage === false) {
            $position = strpos($imageUrl, 'assets');
            $relativeImageRoute = substr($imageUrl, $position);
            unlink(self::PROJECT_ROOT . $relativeImageRoute);
        }
    }

    protected function deleteAllFiles(): void
    {
        $folder_path = self::PROJECT_ROOT . self::IMAGE_PATH;

        $files = glob($folder_path . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function getFileUrl(int $idLibro): string
    {
        $statement =
            "SELECT portada
            FROM libros
            WHERE id_libro = ?";

        $query = $this->getConnection()->prepare($statement);

        $query->bind_param(
            "i",
            $idLibro
        );

        $query->execute();
        $data = $query->get_result()->fetch_column();
        $query->close();

        return $data;
    }
}
