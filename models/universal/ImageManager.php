<?php

require_once __DIR__ . '/ApiResponse.php';

abstract class ImageManager extends ApiResponse
{
    private readonly string $host;
    private readonly string $projectRoot;
    private string $imagePath = 'assets/img/';

    protected function __construct()
    {
        parent::__construct();

        $this->setHost();
        $this->setProjectRoot();
    }

    // MARK: GETTERS

    private function getHost(): string
    {
        return $this->host;
    }

    private function getImagePath(): string
    {
        return $this->imagePath;
    }

    private function getProjectRoot(): string
    {
        return $this->projectRoot;
    }

    // MARK: SETTERS

    private function setHost(): void
    {
        $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        $host = $_SERVER['HTTP_HOST'];

        $this->host = $esquema . $host;
    }

    private function setProjectRoot(): void
    {
        $this->projectRoot = __DIR__ . '/../../';
    }

    // MARK: FILE OPERATIONS

    protected function flattenFilesArray(string $inputFileName): array
    {
        $files = isset($_FILES[$inputFileName]) ? $_FILES[$inputFileName] : [];
        $files2 = [];

        if (!empty($files)) {
            $filesByInput = [];

            foreach ($files as $key => $valueArr) {
                if (is_array($valueArr)) { // file input "multiple"
                    foreach ($valueArr as $i => $value) {
                        $filesByInput[$i][$key] = $value;
                    }
                } else { // -> string, normal file input
                    $filesByInput[] = $files;
                    break;
                }
            }

            $files2 = array_merge($files2, $filesByInput);
        }

        $files3 = [];

        foreach ($files2 as $file) { // let's filter empty & errors
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

        $destination = $this->getProjectRoot() . $this->getImagePath() . $file["name"];

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

            $destination = $this->getProjectRoot() . $this->getImagePath() . $file["name"];

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
            unlink($this->getProjectRoot() . $relativeImageRoute);
        }
    }

    protected function deleteAllFiles(): void
    {
        $folder_path = $this->getProjectRoot() . $this->getImagePath();

        $files = glob($folder_path . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function getFileUrl(int $idLibro): ?string
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
