<?php

require_once __DIR__ . '/LibroIntegrityErrors.php';

final class LibroWrite extends LibroIntegrityErrors
{
	private readonly ?int $idLibro;
	private readonly ?string $titulo;
	private readonly ?string $descripcion;
	private readonly int $paginas;
	private readonly ?string $fechaPublicacion;
	private readonly ?int $idCategoria;

	private ?array $portada;
	private string $portadaRuta;
	private ?string $portadaRutaRelativa;

	private string $relativeFolder = 'libros/';

	public function __construct()
	{
		parent::__construct();

		// $this->setTitulo();
		// $this->setDescripcion();
		// $this->setPaginas();
		// $this->setFechaPublicacion();
		// $this->setIdCategoria();
	}

	// MARK: GETTERS

	private function getIdLibro(): ?int
	{
		return $this->idLibro;
	}

	private function getTitulo(): ?string
	{
		return $this->titulo;
	}

	private function getDescripcion(): ?string
	{
		return $this->descripcion;
	}

	private function getPaginas()
	{
		return $this->paginas;
	}

	private function getFechaPublicacion(): ?string
	{
		return $this->fechaPublicacion;
	}

	private function getIdCategoria(): ?int
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

	private function setIdLibro(?string $idLibro): void
	{
		$this->idLibro = intval($idLibro);
	}

	private function setTitulo(): void
	{
		if (isset($_POST["titulo"])) {
			$titulo = filter_var($_POST["titulo"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			if ($titulo !== "") {
				$this->titulo = $titulo;
			} else $this->titulo = null;
		} else $this->titulo = null;


	}

	private function setDescripcion(): void
	{
		if (isset($_POST["descripcion"])) {
			$descripcion = filter_var($_POST["descripcion"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			if ($descripcion !== "") {
				$this->descripcion = $descripcion;
			} else $this->descripcion = null;
		} else $this->descripcion = null;
	}

	private function setPaginas(): void
	{
		// if (isset($_POST["paginas"])) {
		// 	$paginas = filter_var($_POST["paginas"], FILTER_SANITIZE_NUMBER_INT);
		// 	if ($paginas !== "") {
		// 		$this->paginas = intval($paginas);
		// 	} else $this->paginas = null;
		// } else $this->paginas = null;

		$input = $_POST['paginas'] ?? null;
		// Sanitize to remove any non-numeric characters
		$sanitizedInput = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

		// Validate both the sanitized and original input
		if (!filter_var($sanitizedInput, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1))) || !preg_match('/^[0-9]+$/', $input)) {
			$this->setValidationError("El campo 'paginas' debe ser un número entero superior o igual a 1 y solo contener números.");
		}

		else $this->paginas = intval($sanitizedInput);
	}

	private function setFechaPublicacion(): void
	{
		if (isset($_POST["fecha_publicacion"])) {
			$fechaPublicacion = filter_var($_POST["fecha_publicacion"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			if ($fechaPublicacion !== "") {
				$this->fechaPublicacion = $fechaPublicacion;
			} else $this->fechaPublicacion = null;
		} else $this->fechaPublicacion = null;
	}

	private function setIdCategoria(): void
	{
		if (isset($_POST["id_categoria"])) {
			$idCategoria = filter_var($_POST["id_categoria"], FILTER_SANITIZE_NUMBER_INT);
			if ($idCategoria !== "") {
				$this->idCategoria = intval($idCategoria);
			} else $this->idCategoria = null;
		} else $this->idCategoria = null;
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

		// $this->validateTitulo($this->getTitulo());
		// $this->validateDescripcion($this->getDescripcion());
		// $this->validatePaginas();
		// $this->validateFechaPublicacion($this->getFechaPublicacion());
		// $this->validateIdCategoria($this->getIdCategoria());

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
		$this->validateIdLibro($this->getIdLibro());
		$this->validateTitulo($this->getTitulo());
		$this->validateDescripcion($this->getDescripcion());
		$this->validatePaginas($this->getPaginas());
		$this->validateFechaPublicacion($this->getFechaPublicacion());
		$this->validateIdCategoria($this->getIdCategoria());

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
		$query->bind_param(
			"ssisii",
			$this->titulo,
			$this->descripcion,
			$this->paginas,
			$this->fechaPublicacion,
			$this->idCategoria,
			$this->idLibro
		);
		$query->execute();
		$numFilas = $query->affected_rows;
		$query->close();

		if ($numFilas === 1) {
			$libroModificado = [
				"titulo" => $this->getTitulo(),
				"descripcion" => $this->getDescripcion(),
				"paginas" => $this->getPaginas(),
				"fecha_de_publicacion" => $this->getFechaPublicacion(),
				"Id categoria" => $this->getIdCategoria()
			];

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
		$this->validateIdLibro($this->getIdLibro());

		$this->checkValidationErrors();

		$this->idLibroExists($this->getIdLibro());

		$this->checkIntegrityErrors();

		// $this->removeFile($this->getIdLibro());

		$statement =
			"DELETE FROM libros
		WHERE id_libro = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"i",
			$this->idLibro
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
