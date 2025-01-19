<?php

require_once __DIR__ . '/../errors/LibroValidationErrors.php';

final class Libro extends LibroValidationErrors
{
	private string $statement = "SELECT
	libros.titulo,
	libros.descripcion,
	libros.paginas,
	DATE_FORMAT(libros.fecha_publicacion, '%d/%m/%Y') AS fecha_publicacion,
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

	private function getparams(): array
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

	public function greatFilter(): void
	{
		$min_paginas = isset($_GET['min_paginas']) ? $_GET['min_paginas'] : null;
		$max_paginas = isset($_GET['max_paginas']) ? $_GET['max_paginas'] : null;
		$min_fecha = isset($_GET['min_fecha']) ? $_GET['min_fecha'] : null;
		$max_fecha = isset($_GET['max_fecha']) ? $_GET['max_fecha'] : null;

		if (isset($min_paginas) && ($min_paginas !== "")) {
			$this->validatePaginas($min_paginas);
			$this->addParam($min_paginas);
			$this->addType('i');
			$this->addStatement("AND libros.paginas >= ?");
		}

		if (isset($max_paginas) && ($max_paginas !== "")) {
			$this->validatePaginas($max_paginas);
			$this->addParam($max_paginas);
			$this->addType('i');
			$this->addStatement("AND libros.paginas <= ?");
		}

		if (isset($min_fecha) && ($min_fecha !== "")) {
			$this->validateFechaPublicacion($min_fecha);
			$this->addParam($min_fecha);
			$this->addType('s');
			$this->addStatement("AND libros.fecha_publicacion >= ?");
		}

		if (isset($max_fecha) && ($max_fecha !== "")) {
			$this->validateFechaPublicacion($max_fecha);
			$this->addParam($max_fecha);
			$this->addType('s');
			$this->addStatement("AND libros.fecha_publicacion <= ?");
		}

		$this->validationErrorsExist();

		$query = $this->getConnection()->prepare($this->getStatement());

		if ($this->getparams()) {
			$query->bind_param($this->getTypes(), ...$this->getparams());
		}

		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$libros
			? 'Libros obtenidos'
			: 'No hay coincidencias';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($libros);
		$this->getResponse();
	}
}
