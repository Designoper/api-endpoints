<?php

require_once __DIR__ . '/ApiResponse.php';

abstract class ImageManager extends ApiResponse
{
    private string $baseUrl = 'http://localhost/api-endpoints/';
    private string $imagePath = 'assets/img/';
    private string $additionalDirs = '';
    private string $projectRoot = __DIR__ . '/../../';
    private string $defaultImage = 'default.jpg';

    protected function __construct()
    {
        parent::__construct();
    }

    // MARK: GETTERS

    private function getBaseUrl(): string
    {
        return $this->baseUrl;
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

    private function setAdditionalDirs(string $additionalDirs): void
    {
        $this->additionalDirs = $additionalDirs;
    }

    protected function setFileRelativePath(?array $file = null, string $additional = ""): ?string
    {
        return $file
            ? $this->getImagePath() . $additional . $file["name"]
            : null;
    }

    // MARK: FILE OPERATIONS

    protected function uploadFile(?array $file = null, string $additionalDirs = ""): string
    {
        if ($file === null) {
            return $this->getBaseUrl() . $this->getImagePath() . $this->getDefaultImage();
        }

        $this->setAdditionalDirs($additionalDirs);

        $destination = $this->getProjectRoot() . $this->getImagePath() . $this->getAdditionalDirs() . $file["name"];

        move_uploaded_file(
            $file["tmp_name"],
            $destination
        );

        return $this->getBaseUrl() . $this->getImagePath() . $this->getAdditionalDirs() . $file["name"];
    }

    protected function deleteFile(int $bookId): void
    {
        $path = $this->getFileRelativePathById($bookId);

        if ($path) {
            unlink($this->getProjectRoot() . $path);
        }
    }

    private function getFileRelativePathById(int $bookId): ?string
    {
        $statement =
            "SELECT portada_ruta_relativa
            FROM libros
            WHERE id_libro = ?";

        $query = $this->getConnection()->prepare($statement);

        $query->bind_param(
            "i",
            $bookId
        );

        $query->execute();
        $data = $query->get_result()->fetch_column();
        $query->close();

        return $data;
    }
}
