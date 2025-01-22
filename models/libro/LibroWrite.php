<?php

require_once __DIR__ . '/LibroIntegrityErrors.php';

final class LibroWrite extends LibroIntegrityErrors
{
	private readonly int $idLibro;
	private readonly string $titulo;
	private readonly string $descripcion;
	private readonly int $paginas;
	private readonly string $fechaPublicacion;
	private readonly int $idCategoria;

	private ?array $portada;
	private string $portadaRuta;
	private ?string $portadaRutaRelativa;

	private string $relativeFolder = 'libros/';

	public function __construct()
	{
		parent::__construct();
	}

	// MARK: GETTERS

	private function getIdLibro(): int
	{
		return $this->idLibro;
	}

	private function getTitulo(): string
	{
		return $this->titulo;
	}

	private function getDescripcion(): string
	{
		return $this->descripcion;
	}

	private function getPaginas(): int
	{
		return $this->paginas;
	}

	private function getFechaPublicacion(): string
	{
		return $this->fechaPublicacion;
	}

	private function getIdCategoria(): int
	{
		return $this->idCategoria;
	}

	// MARK: IMAGE GETTERS

	private function getPortada(): ?array
	{
		return $this->portada;
	}

	private function getRelativeFolder(): string
	{
		return $this->relativeFolder;
	}

	// MARK: SETTERS

	private function setIdLibro(): void
	{
		$input = $_POST['id_libro'] ?? null;
		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'id_libro' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->idLibro = intval($sanitizedInput);
	}

	private function setIdLibroPUT($input): void
	{
		// $input = $data['id_libro'] ?? null;
		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'id_libro' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->idLibro = intval($sanitizedInput);
	}

	private function setTitulo(): void
	{
		$input = $_POST['titulo'] ?? null;
		$sanitizedInput = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if ($sanitizedInput === "" || $sanitizedInput === null) {
			$this->setValidationError("El campo 'titulo' no puede estar vacío.");
			return;
		}

		$this->titulo = $sanitizedInput;
	}

	private function setDescripcion(): void
	{
		$input = $_POST['descripcion'] ?? null;
		$sanitizedInput = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if ($sanitizedInput === "" || $sanitizedInput === null) {
			$this->setValidationError("El campo 'descripcion' no puede estar vacío.");
			return;
		}

		$this->descripcion = $sanitizedInput;
	}

	private function setPaginas(): void
	{
		$input = $_POST['paginas'] ?? null;
		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'paginas' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->paginas = intval($sanitizedInput);
	}

	private function setFechaPublicacion(): void
	{
		$input = $_POST['fecha_publicacion'] ?? null;
		$sanitizedInput = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if ($sanitizedInput === "" || $sanitizedInput === null) {
			$this->setValidationError("El campo 'fecha_publicacion' no puede estar vacío.");
			return;
		}

		$dateTime = DateTime::createFromFormat('Y-m-d', $input);

		if (!$dateTime || $dateTime->format('Y-m-d') !== $sanitizedInput) {
			$this->setValidationError("El campo 'fecha_publicacion' debe tener el formato yyyy-mm-dd.");
			return;
		}

		$this->fechaPublicacion = $sanitizedInput;
	}

	private function setIdCategoria(): void
	{
		$input = $_POST['id_categoria'] ?? null;
		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'id_categoria' debe ser un número entero superior o igual a 1 y solo contener números.");
			return;
		}

		$this->idCategoria = intval($sanitizedInput);
	}

	// MARK: IMAGE SETTERS

	private function setPortada(?array $portada): void
	{
		$this->portada = $portada;
	}

	private function setPortadaRuta(string $portadaRuta): void
	{
		$this->portadaRuta = $portadaRuta;
	}

	private function setPortadaRutaRelativa(?string $portadaRutaRelativa): void
	{
		$this->portadaRutaRelativa = $portadaRutaRelativa;
	}

	// MARK: CREATE

	public function createLibro(): void
	{
		$this->setTitulo();
		$this->setDescripcion();
		$this->setPaginas();
		$this->setFechaPublicacion();
		$this->setIdCategoria();

		$this->checkValidationErrors();

		$this->tituloExists($this->getTitulo());
		$this->idCategoriaExists($this->getIdCategoria());

		$this->checkIntegrityErrors();

		// $portadaRutaRelativa = $this->setFileRelativePath($this->getPortada(), $this->getRelativeFolder());

		// $this->setPortadaRutaRelativa($portadaRutaRelativa);

		// $portadaRuta = $this->moveFile($this->getPortada(), $this->getRelativeFolder());

		// $this->setPortadaRuta($portadaRuta);

		$statement =
			"INSERT INTO libros (titulo, descripcion, paginas, fecha_publicacion, id_categoria)
		VALUES (?, ?, ?, ?, ?)";

		$query = $this->getConnection()->prepare($statement);

		$titulo = $this->getTitulo();
		$descripcion = $this->getDescripcion();
		$paginas = $this->getPaginas();
		$fechaPublicacion = $this->getFechaPublicacion();
		$idCategoria = $this->getIdCategoria();

		$query->bind_param(
			"ssisi",
			$titulo,
			$descripcion,
			$paginas,
			$fechaPublicacion,
			$idCategoria
		);

		$query->execute();
		$query->close();

		$this->setStatus(201);
		$this->setMessage("Libro creado");
		$this->getResponse();
	}

	// MARK: UPDATE

	public function updateLibro(): void
	{
		$this->setIdLibro();
		$this->setTitulo();
		$this->setDescripcion();
		$this->setPaginas();
		$this->setFechaPublicacion();
		$this->setIdCategoria();

		$this->checkValidationErrors();

		$this->idLibroExists($this->getIdLibro());
		$this->tituloUpdateExists($this->getTitulo(), $this->getIdLibro());
		$this->idCategoriaExists($this->getIdCategoria());

		$this->checkIntegrityErrors();

		$statement =
			"UPDATE libros
		SET titulo = ?, descripcion = ?, paginas = ?, fecha_publicacion = ?, id_categoria = ?
		WHERE id_libro = ?";

		$query = $this->getConnection()->prepare($statement);

		$idLibro = $this->getIdLibro();
		$titulo = $this->getTitulo();
		$descripcion = $this->getDescripcion();
		$paginas = $this->getPaginas();
		$fechaPublicacion = $this->getFechaPublicacion();
		$idCategoria = $this->getIdCategoria();

		$query->bind_param(
			"ssisii",
			$titulo,
			$descripcion,
			$paginas,
			$fechaPublicacion,
			$idCategoria,
			$idLibro
		);

		$query->execute();
		$numFilas = $query->affected_rows;
		$query->close();

		if ($numFilas === 1) {
			// $libroModificado = [
			// 	"titulo" => $this->getTitulo(),
			// 	"descripcion" => $this->getDescripcion(),
			// 	"paginas" => $this->getPaginas(),
			// 	"fecha_de_publicacion" => $this->getFechaPublicacion(),
			// 	"Id categoria" => $this->getIdCategoria()
			// ];

			$this->setStatus(200);
			$this->setMessage('¡Libro modificado!');
			// $this->setUpdatedContent($libroModificado);
		} else {
			$this->setStatus(204);
		}
		$this->getResponse();
	}

	// MARK: DELETE

	public function deleteLibro(): void
	{
		$input = file_get_contents('php://input');
		$data = json_decode($input, true) ?? [];

		$this->setIdLibroPUT($data["id_libro"] ?? null);

		$this->checkValidationErrors();

		$this->idLibroExists($this->getIdLibro());

		$this->checkIntegrityErrors();

		// $this->removeFile($this->getIdLibro());

		$statement =
			"DELETE FROM libros
		WHERE id_libro = ?";

		$query = $this->getConnection()->prepare($statement);

		$idLibro = $this->getIdLibro();

		$query->bind_param(
			"i",
			$idLibro
		);

		$query->execute();
		$query->close();

		$this->setStatus(204);
		$this->getResponse();
	}

	// MARK: DELETE ALL

	public function deleteAllLibros(): void
	{
		$this->librosExists();

		$this->checkIntegrityErrors();

		$statement =
			"DELETE FROM libros";

		$query = $this->getConnection()->prepare($statement);
		$query->execute();

		$query->close();

		$this->setStatus(204);
		$this->getResponse();
	}
}
