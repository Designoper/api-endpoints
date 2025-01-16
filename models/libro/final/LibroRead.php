<?php

require_once __DIR__ . '/../errors/LibroValidationErrors.php';

final class LibroRead extends LibroValidationErrors
{
	private string $filterTitulo;
	private int $minimoPaginas;
	private int $maximoPaginas = PHP_INT_MAX;
	private string $filterCategoria = "%%";

	public function __construct(?array $data = null)
	{
		parent::__construct();

		$this->setFilterTitulo($data['filterTitulo'] ?? "");
		$this->setMinimoPaginas($data['minimoPaginas'] ?? 1);

		if (isset($data['maximoPaginas']) && !empty($data['maximoPaginas'])) {
			$this->setMaximoPaginas($data['maximoPaginas']);
		}

		if (isset($data['filterCategoria']) && ($data['filterCategoria'] !== "")) {
			$this->setFilterCategoria($data['filterCategoria']);
		}
	}

	// MARK: SETTERS

	private function setFilterTitulo(string $filterTitulo): void
	{
		$this->filterTitulo = "%" . $filterTitulo . "%";
	}

	private function setMinimoPaginas(string $minimoPaginas): void
	{
		$this->minimoPaginas = intval($minimoPaginas);
	}

	private function setMaximoPaginas(string $maximoPaginas): void
	{
		$this->maximoPaginas = intval($maximoPaginas);
	}

	private function setFilterCategoria(string $filterCategoria): void
	{
		$this->filterCategoria = $filterCategoria;
	}

	// MARK: READ ALL

	public function readLibros(): void
	{
		$statement =
			"SELECT *, DATE_FORMAT(libros.fecha_publicacion, '%d/%m/%Y') AS fecha_publicacion_dd_mm_yyyy
		FROM libros
		NATURAL JOIN categorias
		ORDER BY titulo";

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

	// MARK: FILTER

	public function filterLibros(): void
	{
		$statement =
			"SELECT *, DATE_FORMAT(libros.fecha_publicacion, '%d/%m/%Y') AS fecha_publicacion_dd_mm_yyyy
		FROM libros
		NATURAL JOIN categorias
		WHERE titulo LIKE ?
		AND libros.paginas BETWEEN ? AND ?
		AND libros.id_categoria LIKE ?
		ORDER BY titulo";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param(
			"siis",
			$this->filterTitulo,
			$this->minimoPaginas,
			$this->maximoPaginas,
			$this->filterCategoria
		);
		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if ($libros) {
			$this->setMessage('¡Libros filtrados obtenidos!');
		} else {
			$this->setMessage('¡Ningun libro coincide con el criterio especificado!');
		}

		$this->setStatusCode(200);
		$this->setContent($libros);
		$this->getResponse();
	}
}
