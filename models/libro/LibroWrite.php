<?php

declare(strict_types=1);

require_once __DIR__ . '/LibroIntegrityErrors.php';

final class LibroWrite extends LibroIntegrityErrors
{
	private ?int $idLibro {
		get {
			if (!filter_var($this->idLibro, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))) {
				$this->setValidationError("El campo 'id_libro' debe ser un número entero superior o igual a 1 y solo contener números.");
			}
			return $this->idLibro;
		}
		set(string|int|null $input) => $this->idLibro = (int) $input;
	}

	private ?string $titulo {
		get {
			if (empty($this->titulo)) {
				$this->setValidationError("El campo 'titulo' no puede estar vacío.");
			}
			return $this->titulo;
		}
		set(?string $input) => $this->titulo = $input;
	}

	private ?string $descripcion {
		get {
			if (empty($this->descripcion)) {
				$this->setValidationError("El campo 'descripcion' no puede estar vacío.");
			}
			return $this->descripcion;
		}
		set(?string $input) => $this->descripcion = $input;
	}

	private ?int $paginas {
		get {
			if (!filter_var($this->paginas, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))) {
				$this->setValidationError("El campo 'paginas' debe ser un número entero superior o igual a 1 y solo contener números.");
			}
			return $this->paginas;
		}
		set(string|int|null $input) => $this->paginas = (int) $input;
	}

	private ?string $fechaPublicacion {
		get {
			$dateTime = DateTime::createFromFormat('Y-m-d', $this->fechaPublicacion);
			if (!$dateTime || $dateTime->format('Y-m-d') !== $this->fechaPublicacion) {
				$this->setValidationError("El campo 'fecha_publicacion' debe tener el formato yyyy-mm-dd.");
			}
			return $this->fechaPublicacion;
		}
		set(?string $input) => $this->fechaPublicacion = $input;
	}

	private ?int $idCategoria {
		get {
			if (!filter_var($this->idCategoria, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))) {
				$this->setValidationError("El campo 'id_categoria' debe ser un número entero superior o igual a 1 y solo contener números.");
			}
			return $this->idCategoria;
		}
		set(string|int|null $input) => $this->idCategoria = (int) $input;
	}

	public function __construct()
	{
		parent::__construct();

		$this->idLibro = $_POST['id_libro'] ?? null;
		$this->titulo = $_POST['titulo'] ?? null;
		$this->descripcion = $_POST['descripcion'] ?? null;
		$this->paginas = $_POST['paginas'] ?? null;
		$this->fechaPublicacion = $_POST['fecha_publicacion'] ?? null;
		$this->idCategoria = $_POST['id_categoria'] ?? null;
	}

	private function setPortada(): void
	{
		$filesUploaded = $this->flattenFilesArray("portada");

		if (empty($filesUploaded)) {
			$this->setFile(null);
			return;
		}

		if (count($filesUploaded) > 1) {
			$this->setValidationError('Solo se puede subir una imagen.');
			return;
		}

		$portada = $filesUploaded[0];

		$fileType = exif_imagetype($portada['tmp_name']);

		$allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
		if (!in_array($fileType, $allowedTypes)) {
			$this->setValidationError("Solo se permiten imágenes JPEG y PNG.");
		}

		if ($portada['size'] > 1048576) {
			$this->setValidationError('La imagen no puede superar 1MB.');
		}

		$this->setFile($portada);
	}

	private function setCheckbox(): void
	{
		if (!isset($_POST['eliminar_portada'])) {
			$this->setDeleteCheckbox(false);
			return;
		}

		if ($_POST['eliminar_portada'] !== "") {
			$this->setValidationError("El único valor válido para eliminar_portada es campo vacío");
			return;
		}

		$this->setDeleteCheckbox(true);
	}

	// MARK: CREATE

	public function createLibro(): void
	{
		$titulo = $this->titulo;
		$descripcion = $this->descripcion;
		$paginas = $this->paginas;
		$fechaPublicacion = $this->fechaPublicacion;
		$idCategoria = $this->idCategoria;

		$this->setPortada();

		$this->checkValidationErrors();

		$this->tituloExists($titulo);
		$this->idCategoriaExists($idCategoria);

		$this->checkIntegrityErrors();

		$portada = $this->uploadFile();

		$statement =
			"INSERT INTO libros (
				titulo,
				descripcion,
				portada,
				paginas,
				fecha_publicacion,
				id_categoria
			)
			VALUES (
				?,
				?,
				?,
				?,
				?,
				?
			)";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"sssisi",
			$titulo,
			$descripcion,
			$portada,
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
		$idLibro = $this->idLibro;
		$titulo = $this->titulo;
		$descripcion = $this->descripcion;
		$paginas = $this->paginas;
		$fechaPublicacion = $this->fechaPublicacion;
		$idCategoria = $this->idCategoria;
		$this->setPortada();
		$this->setCheckbox();

		$this->checkValidationErrors();

		$this->idLibroExists($idLibro);
		$this->tituloUpdateExists($this->titulo, $this->idLibro);
		$this->idCategoriaExists($this->idCategoria);

		$this->checkIntegrityErrors();

		$portada = $this->updateFile($this->idLibro);

		$statement =
			"UPDATE libros
				SET titulo = ?,
				descripcion = ?,
				portada = ?,
				paginas = ?,
				fecha_publicacion = ?,
				id_categoria = ?
			WHERE id_libro = ?";

		$query = $this->connection->prepare($statement);

		$query->bind_param(
			"sssisii",
			$titulo,
			$descripcion,
			$portada,
			$paginas,
			$fechaPublicacion,
			$idCategoria,
			$idLibro
		);

		$query->execute();
		$numFilas = $query->affected_rows;
		$query->close();

		if ($numFilas === 1) {
			$this->setStatus(200);
			$this->setMessage('¡Libro modificado!');
		} else {
			$this->setStatus(204);
		}
		$this->getResponse();
	}

	// MARK: DELETE

	public function deleteLibro(): void
	{
		$idLibro = $this->idLibro;

		$this->checkValidationErrors();

		$this->idLibroExists($idLibro);

		$this->checkIntegrityErrors();

		$this->deleteFile($idLibro);

		$statement =
			"DELETE FROM libros
			WHERE id_libro = ?";

		$query = $this->connection->prepare($statement);

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

		$this->deleteAllFiles();

		$statement =
			"TRUNCATE TABLE libros";

		$query = $this->connection->prepare($statement);
		$query->execute();

		$query->close();

		$this->setStatus(204);
		$this->getResponse();
	}
}
