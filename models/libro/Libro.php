<?php

require_once __DIR__ . '/LibroValidationErrors.php';

final class Libro extends LibroValidationErrors
{
	private string $statement =
	"SELECT
		libros.id_libro,
		libros.titulo,
		libros.descripcion,
		libros.paginas,
		libros.fecha_publicacion,
		DATE_FORMAT(libros.fecha_publicacion, '%d/%m/%Y')
		AS fecha_publicacion_dd_mm_yyyy,
		categorias.categoria
	FROM libros
	NATURAL JOIN categorias
	WHERE 1=1";
	private array $params = [];
	private string $types = '';

	public function __construct()
	{
		parent::__construct();
	}

	private function getStatement(): string
	{
		return $this->statement;
	}

	private function getParams(): array
	{
		return $this->params;
	}

	private function getTypes(): string
	{
		return $this->types;
	}

	private function addStatement(string $statement): void
	{
		$this->statement .= ' ' . $statement;
	}

	private function addParam(string|int $param): void
	{
		$this->params[] = $param;
	}

	private function addType(string $type): void
	{
		$this->types .= $type;
	}

	public function readLibros(): void
	{
		$statement =
			"SELECT
				libros.id_libro,
				libros.titulo,
				libros.descripcion,
				libros.paginas,
				libros.fecha_publicacion,
				DATE_FORMAT(libros.fecha_publicacion, '%d-%m-%Y')
				AS fecha_publicacion_dd_mm_yyyy,
				categorias.categoria
			FROM libros
			NATURAL JOIN categorias
			ORDER BY libros.titulo";
		$query = $this->getConnection()->prepare($statement);

		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$libros
			? 'Libros obtenidos'
			: 'No hay ningún libro';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($libros);
		$this->getResponse();
	}

	public function filterLibros(): void
	{
		$min_paginas = $_GET["min_paginas"] ?? "";
		$max_paginas = $_GET["max_paginas"] ?? "";
		$min_fecha = $_GET["min_fecha"] ?? "";
		$max_fecha = $_GET["max_fecha"] ?? "";
		$titulo = $_GET["titulo"] ?? "";
		$categoria = $_GET["categoria"] ?? "";

		if ($min_paginas !== "") {
			$this->validatePaginas($min_paginas);
			$this->addParam($min_paginas);
			$this->addType('i');
			$this->addStatement("AND libros.paginas >= ?");
		}

		if ($max_paginas !== "") {
			$this->validatePaginas($max_paginas);
			$this->addParam($max_paginas);
			$this->addType('i');
			$this->addStatement("AND libros.paginas <= ?");
		}

		if ($min_fecha !== "") {
			$this->validateFechaPublicacion($min_fecha);
			$this->addParam($min_fecha);
			$this->addType('s');
			$this->addStatement("AND libros.fecha_publicacion >= ?");
		}

		if ($max_fecha !== "") {
			$this->validateFechaPublicacion($max_fecha);
			$this->addParam($max_fecha);
			$this->addType('s');
			$this->addStatement("AND libros.fecha_publicacion <= ?");
		}

		if ($titulo !== "") {
			$this->addParam("%" . $titulo . "%");
			$this->addType('s');
			$this->addStatement("AND libros.titulo LIKE ?");
		}

		if ($categoria !== "") {
			$this->addParam($categoria);
			$this->addType('i');
			$this->addStatement("AND libros.id_categoria = ?");
		}

		$this->checkValidationErrors();

		$query = $this->getConnection()->prepare($this->getStatement());

		if ($this->getParams()) {
			$query->bind_param($this->getTypes(), ...$this->getParams());
		}

		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$libros
			? 'Libros obtenidos'
			: 'Ningún libro coincide con el criterio seleccionado';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($libros);
		$this->getResponse();
	}
}
