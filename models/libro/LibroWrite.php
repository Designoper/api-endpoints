<?php

declare(strict_types=1);

require_once __DIR__ . '/LibroIntegrityErrors.php';

final class LibroWrite extends LibroIntegrityErrors
{
	private int $idLibro {
		get => $this->idLibro;
		set(string|int $input) {

			filter_var($input, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
				? $this->idLibro = (int) $input
				: $this->setValidationError("El campo 'id_libro' debe ser un número entero superior o igual a 1 y solo contener números.");
		}
	}
	private string $titulo {
		get => $this->titulo;
		set(string $input) {

			$input === ""
				? $this->setValidationError("El campo 'titulo' no puede estar vacío.")
				: $this->titulo = $input;
		}
	}
	private string $descripcion {
		get => $this->descripcion;
		set(string $input) {

			$input === ""
				? $this->setValidationError("El campo 'descripcion' no puede estar vacío.")
				: $this->descripcion = $input;
		}
	}
	private int $paginas {
		get => $this->paginas;
		set(string|int $input) {

			filter_var($input, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
				? $this->paginas = (int) $input
				: $this->setValidationError("El campo 'paginas' debe ser un número entero superior o igual a 1 y solo contener números.");
		}
	}
	private string $fechaPublicacion {
		get => $this->fechaPublicacion;
		set(string $input) {

			$dateTime = DateTime::createFromFormat('Y-m-d', $input);

			!$dateTime || $dateTime->format('Y-m-d') !== $input
				? $this->setValidationError("El campo 'fecha_publicacion' debe tener el formato yyyy-mm-dd.")
				: $this->fechaPublicacion = $input;
		}
	}
	private int $idCategoria {
		get => $this->idCategoria;
		set(string|int $input) {

			filter_var($input, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
				? $this->idCategoria = (int) $input
				: $this->setValidationError("El campo 'id_categoria' debe ser un número entero superior o igual a 1 y solo contener números.");
		}
	}

	public function __construct()
	{
		parent::__construct();
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
		$this->titulo = $_POST['titulo'] ?? "";
		$this->descripcion = $_POST['descripcion'] ?? "";
		$this->paginas = $_POST['paginas'] ?? "";
		$this->fechaPublicacion = $_POST['fecha_publicacion'] ?? "";
		$this->idCategoria = $_POST['id_categoria'] ?? "";

		$this->setPortada();

		$this->checkValidationErrors();

		$this->tituloExists($this->titulo);
		$this->idCategoriaExists($this->idCategoria);

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

		$query = $this->getConnection()->prepare($statement);

		$titulo = $this->titulo;
		$descripcion = $this->descripcion;
		$paginas = $this->paginas;
		$fechaPublicacion = $this->fechaPublicacion;
		$idCategoria = $this->idCategoria;

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

	// public function updateLibro(): void
	// {
	// 	$this->setIdLibro();
	// 	$this->setTitulo();
	// 	$this->setDescripcion();
	// 	$this->setPaginas();
	// 	$this->setFechaPublicacion();
	// 	$this->setIdCategoria();

	// 	$this->setPortada();
	// 	$this->setCheckbox();

	// 	$this->checkValidationErrors();

	// 	$this->idLibroExists($this->getIdLibro());
	// 	$this->tituloUpdateExists($this->titulo, $this->getIdLibro());
	// 	$this->idCategoriaExists($this->getIdCategoria());

	// 	$this->checkIntegrityErrors();

	// 	$portada = $this->updateFile($this->getIdLibro());

	// 	$statement =
	// 		"UPDATE libros
	// 			SET titulo = ?,
	// 			descripcion = ?,
	// 			portada = ?,
	// 			paginas = ?,
	// 			fecha_publicacion = ?,
	// 			id_categoria = ?
	// 		WHERE id_libro = ?";

	// 	$query = $this->getConnection()->prepare($statement);

	// 	$idLibro = $this->getIdLibro();
	// 	$titulo = $this->titulo;
	// 	$descripcion = $this->getDescripcion();
	// 	$paginas = $this->getPaginas();
	// 	$fechaPublicacion = $this->getFechaPublicacion();
	// 	$idCategoria = $this->getIdCategoria();

	// 	$query->bind_param(
	// 		"sssisii",
	// 		$titulo,
	// 		$descripcion,
	// 		$portada,
	// 		$paginas,
	// 		$fechaPublicacion,
	// 		$idCategoria,
	// 		$idLibro
	// 	);

	// 	$query->execute();
	// 	$numFilas = $query->affected_rows;
	// 	$query->close();

	// 	if ($numFilas === 1) {
	// 		$this->setStatus(200);
	// 		$this->setMessage('¡Libro modificado!');
	// 	} else {
	// 		$this->setStatus(204);
	// 	}
	// 	$this->getResponse();
	// }

	// // MARK: DELETE

	// public function deleteLibro(): void
	// {
	// 	$this->setIdLibro();

	// 	$this->checkValidationErrors();

	// 	$this->idLibroExists($this->getIdLibro());

	// 	$this->checkIntegrityErrors();

	// 	$this->deleteFile($this->getIdLibro());

	// 	$statement =
	// 		"DELETE FROM libros
	// 		WHERE id_libro = ?";

	// 	$query = $this->getConnection()->prepare($statement);

	// 	$idLibro = $this->getIdLibro();

	// 	$query->bind_param(
	// 		"i",
	// 		$idLibro
	// 	);

	// 	$query->execute();
	// 	$query->close();

	// 	$this->setStatus(204);
	// 	$this->getResponse();
	// }

	// MARK: DELETE ALL

	public function deleteAllLibros(): void
	{
		$this->librosExists();

		$this->checkIntegrityErrors();

		$this->deleteAllFiles();

		$statement =
			"TRUNCATE TABLE libros";

		$query = $this->getConnection()->prepare($statement);
		$query->execute();

		$query->close();

		$this->setStatus(204);
		$this->getResponse();
	}
}
