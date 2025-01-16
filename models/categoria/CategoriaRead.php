<?php

require_once __DIR__ . '/../universal/Response.php';

final class CategoriaRead extends Response
{
	private ?int $idCategoria;
	private ?string $categoria;

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: GETTERS

	private function getIdCategoria(): ?int
	{
		return $this->idCategoria;
	}

	private function getCategotia(): ?string
	{
		return $this->categoria;
	}

	// MARK: SETTERS

	private function setId(string $idCategoria): void
	{
		$this->idCategoria = intval($idCategoria);
	}

	private function setCategoria(string $categoria): void
	{
		$this->categoria = $categoria;
	}

	public function readCategorias(): void
	{
		$statement =
			"SELECT * FROM categorias
		ORDER BY categoria";

		$query = $this->getConnection()->prepare($statement);
		$query->execute();

		$categorias = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if ($categorias) {
			$this->setStatusCode(200);
			$this->setMessage('Categorias obtenidas!');
			$this->setContent($categorias);
		} else {
			$this->setStatusCode(404);
			$this->setMessage('No hay categorias!');
		}

		$this->getResponse();
	}
}
