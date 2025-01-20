<?php

require_once __DIR__ . '/LibroIntegrityErrors.php';

final class LibroWrite extends LibroIntegrityErrors
{
	private ?int $idLibro;
	private ?string $titulo;
	private ?string $descripcion;
	private ?int $paginas;
	private ?string $fechaPublicacion;
	private ?int $idCategoria;

	private ?array $portada;
	private string $portadaRuta;
	private ?string $portadaRutaRelativa;

	private string $relativeFolder = 'libros/';

	public function __construct(
	) {
		parent::__construct();

		// $this->setPortada($portada);
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

	private function getPaginas(): ?int
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

	private function setTitulo(?string $titulo): void
	{
		$this->titulo = $titulo;
	}

	private function setDescripcion(?string $descripcion): void
	{
		$this->descripcion = $descripcion;
	}

	private function setPaginas(?string $paginas): void
	{
		$this->paginas = intval($paginas);
	}

	private function setFechaPublicacion(?string $fechaPublicacion): void
	{
		$this->fechaPublicacion = $fechaPublicacion;
	}

	private function setIdCategoria(?string $idCategoria): void
	{
		$this->idCategoria = intval($idCategoria);
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
		$this->setTitulo($_POST["titulo"] ?? "");
		$this->setDescripcion($_POST["descripcion"] ?? "");
		$this->setPaginas($_POST["paginas"] ?? "");
		$this->setFechaPublicacion($_POST["fecha_publicacion"] ?? "");
		$this->setIdCategoria($_POST["id_categoria"] ?? "");

		$this->validateTitulo($this->getTitulo());
		$this->validateDescripcion($this->getDescripcion());
		$this->validatePaginas($this->getPaginas());
		$this->validateFechaPublicacion($this->getFechaPublicacion());
		$this->validateIdCategoria($this->getIdCategoria());

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

		$query->bind_param(
			"ssisi",
			$this->titulo,
			$this->descripcion,
			$this->paginas,
			$this->fechaPublicacion,
			$this->idCategoria
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
