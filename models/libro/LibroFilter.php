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

	private readonly ?int $minimoPaginas;
	private readonly ?int $maximoPaginas;
	private readonly ?string $minimoFechaPublicacion;
	private readonly ?string $maximoFechaPublicacion;
	private readonly ?string $titulo;
	private readonly ?int $idCategoria;
	private readonly ?string $criterioOrden;

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






	private function getTitulo(): ?string
	{
		return $this->titulo;
	}

	private function getMinimoPaginas(): ?int
	{
		return $this->minimoPaginas;
	}

	private function getMaximoPaginas(): ?int
	{
		return $this->maximoPaginas;
	}

	private function getMinimoFechaPublicacion(): ?string
	{
		return $this->minimoFechaPublicacion;
	}

	private function getMaximoFechaPublicacion(): ?string
	{
		return $this->maximoFechaPublicacion;
	}

	private function getIdCategoria(): ?int
	{
		return $this->idCategoria;
	}

	private function getCriterioOrden(): ?string
	{
		return $this->criterioOrden;
	}

	//MARK: SETTERS

	private function setMinimoPaginas(): void
	{
		$input = $_GET['min_paginas'] ?? "";

		if ($input === "") {
			$this->minimoPaginas = null;
			return;
		}

		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'min_paginas' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->minimoPaginas = (int) $sanitizedInput;
	}

	private function setMaximoPaginas(): void
	{
		$input = $_GET['max_paginas'] ?? "";

		if ($input === "") {
			$this->maximoPaginas = null;
			return;
		}

		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'max_paginas' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->maximoPaginas = (int) $sanitizedInput;
	}

	private function setMinimoFechaPublicacion(): void
	{
		$input = $_GET['min_fecha'] ?? "";

		if ($input === "") {
			$this->minimoFechaPublicacion = null;
			return;
		}

		$dateTime = DateTime::createFromFormat('Y-m-d', $input);

		if (!$dateTime || $dateTime->format('Y-m-d') !== $input) {
			$this->setValidationError("El campo 'min_fecha' debe tener el formato yyyy-mm-dd");
			return;
		}

		$this->minimoFechaPublicacion = $input;
	}

	private function setMaximoFechaPublicacion(): void
	{
		$input = $_GET['max_fecha'] ?? "";

		if ($input === "") {
			$this->maximoFechaPublicacion = null;
			return;
		}

		$dateTime = DateTime::createFromFormat('Y-m-d', $input);

		if (!$dateTime || $dateTime->format('Y-m-d') !== $input) {
			$this->setValidationError("El campo 'max_fecha' debe tener el formato yyyy-mm-dd");
			return;
		}

		$this->maximoFechaPublicacion = $input;
	}

	private function setTitulo(): void
	{
		$input = $_GET['titulo'] ?? "";

		if ($input === "") {
			$this->titulo = null;
			return;
		}

		$this->titulo = $input;
	}

	private function setIdCategoria(): void
	{
		$input = $_GET['id_categoria'] ?? "";

		if ($input === "") {
			$this->idCategoria = null;
			return;
		}

		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'categoria' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->idCategoria = (int) $sanitizedInput;
	}

	private function setCriterioOrden(): void
	{
		$input = $_GET['criterio_orden'] ?? "";

		if ($input === "") {
			$this->criterioOrden = null;
			return;
		}

		$permitedValues = [
			'tituloAsc',
			'tituloDesc',
			'paginasAsc',
			'paginasDesc',
			'fechaAsc',
			'fechaDesc'
		];

		if (!in_array($input, $permitedValues, true)) {
			$this->setValidationError("El campo 'criterio_orden' solo acepta los siguientes valores: 'tituloAsc','tituloDesc','paginasAsc','paginasDesc','fechaAsc','fechaDesc'.");
			return;
		}

		$this->criterioOrden = $input;
	}

	//MARK: FUNCTION

	public function filterLibros(): void
	{
		$this->setMinimoPaginas();
		$this->setMaximoPaginas();
		$this->setMinimoFechaPublicacion();
		$this->setMaximoFechaPublicacion();
		$this->setTitulo();
		$this->setIdCategoria();
		$this->setCriterioOrden();

		$this->checkValidationErrors();

		if ($this->getMinimoPaginas()) {
			$this->addParam($this->getMinimoPaginas());
			$this->addType('i');
			$this->addStatement("AND libros.paginas >= ?");
		}

		if ($this->getMaximoPaginas()) {
			$this->addParam($this->getMaximoPaginas());
			$this->addType('i');
			$this->addStatement("AND libros.paginas <= ?");
		}

		if ($this->getMinimoFechaPublicacion()) {
			$this->addParam($this->getMinimoFechaPublicacion());
			$this->addType('s');
			$this->addStatement("AND libros.fecha_publicacion >= ?");
		}

		if ($this->getMaximoFechaPublicacion()) {
			$this->addParam($this->getMaximoFechaPublicacion());
			$this->addType('s');
			$this->addStatement("AND libros.fecha_publicacion <= ?");
		}

		if ($this->getTitulo()) {
			$this->addParam("%" . $this->getTitulo() . "%");
			$this->addType('s');
			$this->addStatement("AND libros.titulo LIKE ?");
		}

		if ($this->getIdCategoria()) {
			$this->addParam($this->getIdCategoria());
			$this->addType('i');
			$this->addStatement("AND libros.id_categoria = ?");
		}

		if ($this->getCriterioOrden()) {
			switch ($this->getCriterioOrden()) {
				case 'tituloAsc':
					$param = "libros.titulo ASC";
					break;
				case 'tituloDesc':
					$param = "libros.titulo DESC";
					break;
				case 'paginasAsc':
					$param = "libros.paginas ASC";
					break;
				case 'paginasDesc':
					$param = "libros.paginas DESC";
					break;
				case 'fechaAsc':
					$param = "libros.fecha_publicacion ASC";
					break;
				case 'fechaDesc':
					$param = "libros.fecha_publicacion DESC";
					break;
			}

			$this->addStatement("ORDER BY " . $param);
		}

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
