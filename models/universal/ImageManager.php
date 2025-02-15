<?php

require_once __DIR__ . '/ApiResponse.php';

abstract class ImageManager extends ApiResponse
{
	private string $root = 'http://localhost/api-endpoints/';
	private string $genericPathImage = 'assets/img/';
	private string $extraDirectories = '';
	private string $relativePath = '../../';
	private string $defaultImage = 'default.jpg';

	protected function __construct()
	{
		parent::__construct();
	}

	private function getRoot(): string
	{
		return $this->root;
	}

	private function getGenericPathImage(): string
	{
		return $this->genericPathImage;
	}

	private function getExtraDirectories(): string
	{
		return $this->extraDirectories;
	}

	private function getRelativePath(): string
	{
		return $this->relativePath;
	}

	private function getDefaultImage(): string
	{
		return $this->defaultImage;
	}

	private function setExtraDirectories(string $extraDirectories): void
	{
		$this->extraDirectories = $extraDirectories;
	}





	protected function setFileRelativePath(?array $file = null, string $extra = ""): ?string
	{
		if ($file === null) {
			return null;
		}

		return $this->getGenericPathImage() . $extra . $file["name"];
	}


	protected function moveFile(?array $file = null, string $extraDirectories = ""): string
	{
		if ($file === null) {
			return $this->getRoot() . $this->getGenericPathImage() . $this->getDefaultImage();
		}

		$this->setExtraDirectories($extraDirectories);

		$targetDirectory = __DIR__ . '/../../assets/img/' . $this->getExtraDirectories();

		move_uploaded_file(
			$file["tmp_name"],
			$targetDirectory . $file["name"]
		);

		return $this->getRoot() . $this->getGenericPathImage() . $this->getExtraDirectories() . $file["name"];
	}



	protected function removeFile(int $idLibro): void
	{
		$path = $this->retrieveRelativePath($idLibro);

		if ($path === null) {
			return;
		}

		unlink($this->getRelativePath() . $path);
	}



	private function retrieveRelativePath(int $idLibro): ?string
	{
		$statement =
			"SELECT portada_ruta_relativa
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
