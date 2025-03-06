<?php

declare(strict_types=1);

require_once __DIR__ . '/LibroIntegrityErrors.php';

final class LibroWrite extends LibroIntegrityErrors
{
	private const string FOLDER = 'libros/';

	private int $idLibro {
		set(mixed $value) {
			filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
				? $this->idLibro = (int) $value
				: $this->setValidationError("El campo 'id_libro' debe ser un número entero superior o igual a 1 y solo contener números.");
		}
	}

	private string $titulo {
		set {
			$value === ""
				? $this->setValidationError("El campo 'titulo' no puede estar vacío.")
				: $this->titulo = $value;
		}
	}

	private string $descripcion {
		set {
			$value === ""
				? $this->setValidationError("El campo 'descripcion' no puede estar vacío.")
				: $this->descripcion = $value;
		}
	}

	private int $paginas {
		set(mixed $value) {
			filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
				? $this->paginas = (int) $value
				: $this->setValidationError("El campo 'paginas' debe ser un número entero superior o igual a 1 y solo contener números.");
		}
	}

	private string $fechaPublicacion {
		set {
			$dateTime = DateTime::createFromFormat('Y-m-d', $value);

			(!$dateTime || $dateTime->format('Y-m-d') !== $value)
				? $this->setValidationError("El campo 'fecha_publicacion' debe tener el formato yyyy-mm-dd.")
				: $this->fechaPublicacion = $value;
		}
	}

	private int $idCategoria {
		set(mixed $value) {
			filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
				? $this->idCategoria = (int) $value
				: $this->setValidationError("El campo 'id_categoria' debe ser un número entero superior o igual a 1 y solo contener números.");
		}
	}

	public function __construct()
	{
		parent::__construct();

		$this->extraDirectories = self::FOLDER;
	}

	private function setPortada(): void
	{
		$filesUploaded = $this->flattenFilesArray("portada");

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
		$value = $_POST['eliminar_portada'] ?? false;

		if ($value === false) {
			$this->deleteCheckbox = false;
			return;
		}

		$value !== ""
			? $this->setValidationError("El único valor válido para eliminar_portada es campo vacío")
			: $this->deleteCheckbox = true;
	}

	// MARK: CREATE

	public function createLibro(): void
	{
		$this->titulo = $_POST['titulo'] ?? "";
		$this->descripcion = $_POST['descripcion'] ?? "";
		$this->paginas = $_POST['paginas'] ?? null;
		$this->fechaPublicacion = $_POST['fecha_publicacion'] ?? "";
		$this->idCategoria = $_POST['id_categoria'] ?? null;
		$this->setPortada();

		$this->checkValidationErrors();

		$titulo = $this->titulo;
		$descripcion = $this->descripcion;
		$paginas = $this->paginas;
		$fechaPublicacion = $this->fechaPublicacion;
		$idCategoria = $this->idCategoria;
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
		$this->idLibro = $_POST['id_libro'] ?? null;
		$this->titulo = $_POST['titulo'] ?? "";
		$this->descripcion = $_POST['descripcion'] ?? "";
		$this->paginas = $_POST['paginas'] ?? null;
		$this->fechaPublicacion = $_POST['fecha_publicacion'] ?? "";
		$this->idCategoria = $_POST['id_categoria'] ?? null;
		$this->setPortada();
		$this->setCheckbox();

		$this->checkValidationErrors();

		$idLibro = $this->idLibro;
		$titulo = $this->titulo;
		$descripcion = $this->descripcion;
		$paginas = $this->paginas;
		$fechaPublicacion = $this->fechaPublicacion;
		$idCategoria = $this->idCategoria;

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
		$this->idLibro = $_POST['id_libro'] ?? null;

		$this->checkValidationErrors();

		$idLibro = $this->idLibro;
		$libroPath = $this->getFileUrl($idLibro);

		$statement =
			"DELETE FROM libros
			WHERE id_libro = ?";

		$query = $this->connection->prepare($statement);

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
			$this->setMessage('¡El ibro solicitado no existe!');
		}

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
