<?php

require_once __DIR__ . '/LibroValidationErrors.php';

final class LibroFilter extends LibroValidationErrors
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

	private readonly int|string $minimoPaginas;
	private readonly int|string $maximoPaginas;
	private readonly string $minimoFechaPublicacion;
	private readonly string $maximoFechaPublicacion;
	private readonly string $titulo;
	private readonly string $categoria;

	public function __construct()
	{
		parent::__construct();
	}

	//MARK: GETTERS

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






	private function getTitulo(): string
	{
		return $this->titulo;
	}

	private function getMinimoPaginas(): string|int
	{
		return $this->minimoPaginas;
	}

	private function getMaximoPaginas(): string|int
	{
		return $this->maximoPaginas;
	}

	private function getMinimoFechaPublicacion(): string
	{
		return $this->minimoFechaPublicacion;
	}

	private function getMaximoFechaPublicacion(): string
	{
		return $this->maximoFechaPublicacion;
	}

	private function getCategoria(): string
	{
		return $this->categoria;
	}


	//MARK: SETTERS

	private function setMinimoPaginas(): void
	{
		$input = $_GET['min_paginas'] ?? "";

		if ($input === "") {
			$this->minimoPaginas = $input;
			return;
		}

		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'min_paginas' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->minimoPaginas = intval($sanitizedInput);
	}

	private function setMaximoPaginas(): void
	{
		$input = $_GET['max_paginas'] ?? "";

		if ($input === "") {
			$this->maximoPaginas = $input;
			return;
		}

		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'max_paginas' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->maximoPaginas = intval($sanitizedInput);
	}

	private function setMinimoFechaPublicacion(): void
	{
		$input = $_GET['min_fecha'] ?? "";

		$sanitizedInput = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$this->minimoFechaPublicacion = $sanitizedInput;
	}

	private function setMaximoFechaPublicacion(): void
	{
		$input = $_GET['max_fecha'] ?? "";

		$sanitizedInput = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$this->maximoFechaPublicacion = $sanitizedInput;
	}

	private function setTitulo(): void
	{
		$input = $_GET['titulo'] ?? "";

		$sanitizedInput = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		$this->titulo = $sanitizedInput;
	}

	private function setCategoria(): void
	{
		$input = $_GET['id_categoria'] ?? "";

		if ($input === "") {
			$this->categoria = $input;
			return;
		}

		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'categoria' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->categoria = intval($sanitizedInput);
	}

	//MARK: FUNCTION

	public function filterLibros(): void
	{
		$this->setMinimoPaginas();
		$this->setMaximoPaginas();
		$this->setMinimoFechaPublicacion();
		$this->setMaximoFechaPublicacion();
		$this->setTitulo();
		$this->setCategoria();

		if ($this->getMinimoPaginas() !== "") {
			$this->addParam($this->getMinimoPaginas());
			$this->addType('i');
			$this->addStatement("AND libros.paginas >= ?");
		}

		if ($this->getMaximoPaginas() !== "") {
			$this->addParam($this->getMaximoPaginas());
			$this->addType('i');
			$this->addStatement("AND libros.paginas <= ?");
		}

		if ($this->getMinimoFechaPublicacion() !== "") {
			$this->addParam($this->getMinimoFechaPublicacion());
			$this->addType('s');
			$this->addStatement("AND libros.fecha_publicacion >= ?");
		}

		if ($this->getMaximoFechaPublicacion() !== "") {
			$this->addParam($this->getMaximoFechaPublicacion());
			$this->addType('s');
			$this->addStatement("AND libros.fecha_publicacion <= ?");
		}

		if ($this->getTitulo() !== "") {
			$this->addParam("%" . $this->getTitulo() . "%");
			$this->addType('s');
			$this->addStatement("AND libros.titulo LIKE ?");
		}

		if ($this->getCategoria() !== "") {
			$this->addParam($this->getCategoria());
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
