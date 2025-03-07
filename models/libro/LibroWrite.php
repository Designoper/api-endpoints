<?php

declare(strict_types=1);

require_once __DIR__ . '/LibroIntegrityErrors.php';

final class LibroWrite extends LibroIntegrityErrors
{
	private readonly int $idLibro;
	private readonly string $titulo;
	private readonly string $descripcion;
	private readonly int $paginas;
	private readonly string $fechaPublicacion;
	private readonly int $idCategoria;

	private const string FOLDER = 'libros/';

	public function __construct()
	{
		parent::__construct();

		$this->extraDirectories = self::FOLDER;
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

	// MARK: SETTERS

	private function setIdLibro(): void
	{
		$name = 'id_libro';
		$value = $_POST[$name] ?? "";
		$errorMessage = "El campo '$name' debe ser un número entero superior o igual a 1 y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
			? $this->idLibro = (int) $value
			: $this->setValidationError($errorMessage);
	}

	private function setTitulo(): void
	{
		$name = 'titulo';
		$value = $_POST[$name] ?? "";
		$errorMessage = "El campo '$name' no puede estar vacío.";

		$value === ""
			? $this->setValidationError($errorMessage)
			: $this->titulo = $value;
	}

	private function setDescripcion(): void
	{
		$name = 'descripcion';
		$value = $_POST[$name] ?? "";
		$errorMessage = "El campo '$name' no puede estar vacío.";

		$value === ""
			? $this->setValidationError($errorMessage)
			: $this->descripcion = $value;
	}

	private function setPaginas(): void
	{
		$name = 'paginas';
		$value = $_POST[$name] ?? "";
		$errorMessage = "El campo '$name' debe ser un número entero superior o igual a 1 y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
			? $this->paginas = (int) $value
			: $this->setValidationError($errorMessage);
	}

	private function setFechaPublicacion(): void
	{
		$name = 'fecha_publicacion';
		$value = $_POST[$name] ?? "";
		$dateFormat = 'Y-m-d';
		$errorMessage = "El campo '$name' debe tener el formato '$dateFormat'.";

		$dateTime = DateTime::createFromFormat($dateFormat, $value);

		$dateTime === false || $dateTime->format($dateFormat) !== $value
			? $this->setValidationError($errorMessage)
			: $this->fechaPublicacion = $value;
	}

	private function setIdCategoria(): void
	{
		$name = 'id_categoria';
		$value = $_POST[$name] ?? "";
		$errorMessage = "El campo '$name' debe ser un número entero superior o igual a 1 y solo contener números.";

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
			? $this->idCategoria = (int) $value
			: $this->setValidationError($errorMessage);
	}

	private function setPortada(): void
	{
		$name = 'portada';

		$filesUploaded = $this->flattenFilesArray($name);

		if (count($filesUploaded) === 0) {
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
		$name = 'eliminar_portada';
		$value = $_POST[$name] ?? false;
		$errorMessage = "El campo '$name' solo es válido si esta vacío.";

		if ($value === false) {
			$this->deleteCheckbox = false;
			return;
		}

		$value !== ""
			? $this->setValidationError($errorMessage)
			: $this->deleteCheckbox = true;
	}

	// MARK: CREATE

	public function createLibro(): void
	{
		$this->setTitulo();
		$this->setDescripcion();
		$this->setPaginas();
		$this->setFechaPublicacion();
		$this->setIdCategoria();
		$this->setPortada();

		$this->checkValidationErrors();

		$titulo = $this->getTitulo();
		$descripcion = $this->getDescripcion();
		$paginas = $this->getPaginas();
		$fechaPublicacion = $this->getFechaPublicacion();
		$idCategoria = $this->getIdCategoria();
		$portada = $this->uploadFileName();

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

		$query->bind_param(
			"sssisi",
			$titulo,
			$descripcion,
			$portada,
			$paginas,
			$fechaPublicacion,
			$idCategoria
		);

		try {
			$query->execute();
		} catch (Exception $error) {
			$query->close();

			//error 1062 clave única duplicada
			if ($error->getCode() == 1062) {
				preg_match_all("/for key '(.+?)'/", $error->getMessage(), $matches);
				$constraintNames = $matches[1] ?? [];

				foreach ($constraintNames as $constraint) {
					$errorMessage = match ($constraint) {
						'titulo_UNIQUE' => '¡El título del libro ya esta asignado a otro libro!',
						default => "Valor duplicado en {$constraint}"
					};
					$this->setIntegrityError($errorMessage);
				}

				$this->setStatus(409);
				$this->checkIntegrityErrors();
			}

			//error 1452 clave foránea no válida
			if ($error->getCode() == 1452) {
				preg_match("/FOREIGN KEY \(`(.+?)`\)/", $error->getMessage(), $matches);
				$constraintFields = $matches[1] ?? [];

				foreach ($constraintFields as $constraint) {
					$errorMessage = match ($constraint) {
						'id_categoria' => '¡La categoria seleccionada no existe!',
						default => "El registro referenciado no existe en {$constraint}"
					};
					$this->setIntegrityError($errorMessage);
				}

				$this->setStatus(404);
				$this->checkIntegrityErrors();
			}
		}

		$query->close();
		$this->setStatus(201);
		$this->setMessage("Libro creado");
		$this->uploadFile();
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
		$this->setPortada();
		$this->setCheckbox();

		$this->checkValidationErrors();

		$idLibro = $this->getIdLibro();
		$titulo = $this->getTitulo();
		$descripcion = $this->getDescripcion();
		$paginas = $this->getPaginas();
		$fechaPublicacion = $this->getFechaPublicacion();
		$idCategoria = $this->getIdCategoria();

		$this->idLibroExists($idLibro);

		$portada = $this->updateFileName($idLibro);
		$libroPath = $this->getFileUrl($idLibro);

		$statement =
			"UPDATE libros
				SET titulo = ?,
				descripcion = ?,
				portada = ?,
				paginas = ?,
				fecha_publicacion = ?,
				id_categoria = ?
			WHERE id_libro = ?";

		$query = $this->getConnection()->prepare($statement);

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

		try {
			$query->execute();
		} catch (Exception $error) {
			$query->close();

			//error 1062 clave única duplicada
			if ($error->getCode() == 1062) {
				$this->setStatus(409);
				$this->setIntegrityError('¡El título del libro ya esta asignado a otro libro!');
				$this->checkIntegrityErrors();
			}

			//error 1452 clave foránea no válida
			if ($error->getCode() == 1452) {
				$this->setStatus(404);
				$this->setIntegrityError('¡La categoria seleccionada no existe!');
				$this->checkIntegrityErrors();
			}
		}

		$numFilas = $query->affected_rows;
		$query->close();

		if ($numFilas === 1) {
			$this->setStatus(200);
			$this->setMessage('¡Libro modificado!');
		} else {
			$this->setStatus(204);
		}

		$this->updateFile($libroPath);
		$this->getResponse();
	}

	// MARK: DELETE

	public function deleteLibro(): void
	{
		$this->setIdLibro();

		$this->checkValidationErrors();

		$idLibro = $this->getIdLibro();
		$libroPath = $this->getFileUrl($idLibro);

		$statement =
			"DELETE FROM libros
			WHERE id_libro = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"i",
			$idLibro
		);

		$query->execute();
		$numFilas = $query->affected_rows;
		$query->close();

		if ($numFilas === 1) {
			$this->setStatus(204);
			$this->deleteFile($libroPath);
		} else {
			$this->setStatus(404);
			$this->setMessage('¡El libro solicitado no existe!');
		}

		$this->getResponse();
	}

	// MARK: DELETE ALL

	public function deleteAllLibros(): void
	{
		$statement =
			"DELETE FROM libros";

		$query = $this->getConnection()->prepare($statement);

		$query->execute();
		$numFilas = $query->affected_rows;
		$query->close();

		if ($numFilas > 0) {
			$this->deleteAllFiles();
		}

		$this->setStatus(204);
		$this->getResponse();
	}
}
