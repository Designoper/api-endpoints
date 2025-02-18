<?php

require_once __DIR__ . '/ApiResponse.php';

abstract class ImageManager extends ApiResponse
{
    private readonly string $host;
    private string $imagePath = 'assets/img/';
    private string $additionalDirs = '';
    private string $projectRoot = __DIR__ . '/../../';
    private string $defaultImage = 'default.jpg';

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

    private function getImagePath(): string
    {
        return $this->imagePath;
    }

    private function getAdditionalDirs(): string
    {
        return $this->additionalDirs;
    }

    private function getProjectRoot(): string
    {
        return $this->projectRoot;
    }

    private function getDefaultImage(): string
    {
        return $this->defaultImage;
    }

    // MARK: SETTERS

    private function setHost(): void
    {
        $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        $host = $_SERVER['HTTP_HOST'];

        $this->host = $esquema . $host;
    }

    // MARK: FILE OPERATIONS

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
