<?php

require_once __DIR__ . '/../errors/LibroValidationErrors.php';

final class Libro extends LibroValidationErrors
{
	public function __construct()
	{
		parent::__construct();
	}

	// MARK: SETTERS

	// private function setFilterTitulo(string $filterTitulo): void
	// {
	// 	$this->filterTitulo = "%" . $filterTitulo . "%";
	// }

	// private function setMinimoPaginas(string $minimoPaginas): void
	// {
	// 	$this->minimoPaginas = intval($minimoPaginas);
	// }

	// private function setMaximoPaginas(string $maximoPaginas): void
	// {
	// 	$this->maximoPaginas = intval($maximoPaginas);
	// }

	// private function setFilterCategoria(string $filterCategoria): void
	// {
	// 	$this->filterCategoria = $filterCategoria;
	// }

	// MARK: MIN PAGINAS

	public function minPaginas(int $minimoPaginas): void
	{
		$this->validatePaginas($minimoPaginas);
		$this->checkValidationErrors();

		$statement =
			"SELECT *
		FROM libros
		NATURAL JOIN categorias
		WHERE libros.paginas >= ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param(
			"i",
			$minimoPaginas,
		);
		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if ($libros) {
			$this->setMessage('¡Libros obtenidos!');
		} else {
			$this->setMessage('¡No hay libros!');
		}

		$this->setStatusCode(200);
		$this->setContent($libros);
		$this->getResponse();
	}

	// MARK: MAX PAGINAS

	public function maxPaginas(int $maximoPaginas): void
	{
		$this->validatePaginas($maximoPaginas);
		$this->checkValidationErrors();

		$statement =
			"SELECT *
		FROM libros
		NATURAL JOIN categorias
		WHERE libros.paginas <= ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param(
			"i",
			$maximoPaginas,
		);
		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if ($libros) {
			$this->setMessage('¡Libros obtenidos!');
		} else {
			$this->setMessage('¡No hay libros!');
		}

		$this->setStatusCode(200);
		$this->setContent($libros);
		$this->getResponse();
	}

	// MARK: PAGINAS ASC

	public function OrdenarPaginasAsc(): void
	{
		$statement =
			"SELECT *
			FROM libros
			NATURAL JOIN categorias
			ORDER BY libros.paginas ASC";

		$query = $this->getConnection()->prepare($statement);
		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if ($libros) {
			$this->setMessage('¡Libros obtenidos!');
		} else {
			$this->setMessage('¡No hay libros!');
		}

		$this->setStatusCode(200);
		$this->setContent($libros);
		$this->getResponse();
	}

	// MARK: PAGINAS DESC

	public function OrdenarPaginasDesc(): void
	{
		$statement =
			"SELECT *
			FROM libros
			NATURAL JOIN categorias
			ORDER BY libros.paginas DESC";

		$query = $this->getConnection()->prepare($statement);
		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if ($libros) {
			$this->setMessage('¡Libros obtenidos!');
		} else {
			$this->setMessage('¡No hay libros!');
		}

		$this->setStatusCode(200);
		$this->setContent($libros);
		$this->getResponse();
	}
}
