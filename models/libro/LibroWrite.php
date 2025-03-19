<?php

declare(strict_types=1);

require_once __DIR__ . '/LibroIntegrityErrors.php';
require_once __DIR__ . '/../universal/FileManager.php';

final class LibroWrite extends LibroIntegrityErrors
{
	private readonly array $info;
	private readonly int $idLibro;
	private readonly string $titulo;
	private readonly string $descripcion;
	private readonly int $paginas;
	private readonly string $fechaPublicacion;
	private readonly int $idCategoria;

	private readonly ?array $portada;
	private readonly bool $eliminarPortada;

	private const string FOLDER = 'libros/';

	private const string SQL_COLUMN = 'portada';
	private const string SQL_TABLE = 'libros';
	private const string SQL_PRIMARY_KEY = 'id_libro';

	public function __construct()
	{
		parent::__construct();

		$info = $this->parsePutMultipart();
		$this->info = $info;
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

	private function getPortada(): ?array
	{
		return $this->portada;
	}

	private function getEliminarPortada(): bool
	{
		return $this->eliminarPortada;
	}

	// MARK: SETTERS

	private function setIdLibro(): void
	{
		$errorMessage = "El id del recurso debe ser un número entero superior o igual a 1 y solo contener números.";

		$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$segments = explode('/', trim($path, '/'));
		$value = end($segments);

		filter_var($value, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))
			? $this->idLibro = (int) $value
			: $this->setValidationError($errorMessage);
	}

	private function setTitulo(): void
	{
		$name = 'titulo';
		$value = $this->info[$name] ?? "";
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

		$filesUploaded = FileManager::flattenFilesArray($name);

		if (count($filesUploaded) === 0) {
			$this->portada = null;
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

		$this->portada = $portada;
	}

	private function setCheckbox(): void
	{
		$name = 'eliminar_portada';
		$value = $_POST[$name] ?? false;
		$errorMessage = "El campo '$name' solo es válido si está vacío.";

		if ($value === false) {
			$this->eliminarPortada = false;
			return;
		}

		$value !== ""
			? $this->setValidationError($errorMessage)
			: $this->eliminarPortada = true;
	}

	private function parsePutMultipart(): array
	{
		$rawData = file_get_contents("php://input");
		$contentType = $_SERVER["CONTENT_TYPE"] ?? '';

		// Make sure this is a multipart request
		if (stripos($contentType, "multipart/form-data") === false) {
			return [];
		}

		// Extract boundary from header
		if (!preg_match('/boundary=(.*)$/', $contentType, $matches)) {
			return [];
		}
		$boundary = $matches[1];

		$results = [];
		// Split content by boundary
		$parts = preg_split("/-+$boundary/", $rawData);
		// Remove the last element which is after the final boundary
		array_pop($parts);

		foreach ($parts as $part) {
			// Skip empty parts
			if (empty(trim($part))) {
				continue;
			}
			// Separate headers from body
			$part = ltrim($part, "\r\n");
			$splitPosition = strpos($part, "\r\n\r\n");
			if ($splitPosition === false) {
				continue;
			}
			$rawHeaders = substr($part, 0, $splitPosition);
			$body = substr($part, $splitPosition + 4);
			$body = rtrim($body, "\r\n");

			// Parse headers into an associative array
			$headers = [];
			foreach (explode("\r\n", $rawHeaders) as $headerLine) {
				if (strpos($headerLine, ':') !== false) {
					list($name, $value) = explode(":", $headerLine, 2);
					$headers[strtolower(trim($name))] = trim($value);
				}
			}

			if (!isset($headers['content-disposition'])) {
				continue;
			}
			// Extract the field name and, if available, the filename
			preg_match('/name="([^"]+)"/', $headers['content-disposition'], $nameMatch);
			$fieldName = $nameMatch[1] ?? '';
			preg_match('/filename="([^"]+)"/', $headers['content-disposition'], $fileMatch);

			if ($fileMatch) {
				// File field
				$fileName = $fileMatch[1];
				$fileType = $headers['content-type'] ?? 'application/octet-stream';
				$tmpName = tempnam(sys_get_temp_dir(), 'PUT');
				file_put_contents($tmpName, $body);

				// Mimic PHP's _FILES structure for this field:
				$results[$fieldName] = [
					'name'     => $fileName,
					'type'     => $fileType,
					'tmp_name' => $tmpName,
					'error'    => 0,
					'size'     => filesize($tmpName)
				];
			} else {
				// Regular form field
				$results[$fieldName = $body];
			}
		}
		return $results;
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

		$createLibro = new FileManager();

		$portada = $this->getPortada();
		$createLibro->setFile($portada);
		$createLibro->setExtraDirectories(self::FOLDER);

		$titulo = $this->getTitulo();
		$descripcion = $this->getDescripcion();
		$paginas = $this->getPaginas();
		$fechaPublicacion = $this->getFechaPublicacion();
		$idCategoria = $this->getIdCategoria();
		$portadaName = $createLibro->uploadFileName();

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
			$portadaName,
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
		$createLibro->uploadFile();
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

		$portada = $this->getPortada();
		$eliminarPortada = $this->getEliminarPortada();

		$updateLibro = new FileManager();

		$updateLibro->setFile($portada);
		$updateLibro->setDeleteCheckbox($eliminarPortada);
		$updateLibro->setExtraDirectories(self::FOLDER);

		$portadaName = $updateLibro->updateFileName(self::SQL_COLUMN, self::SQL_TABLE, self::SQL_PRIMARY_KEY, $idLibro);
		$libroPath = $updateLibro->getFileUrl(self::SQL_COLUMN, self::SQL_TABLE, self::SQL_PRIMARY_KEY, $idLibro);

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
			$portadaName,
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

		$updateLibro->updateFile($libroPath);
		$this->getResponse();
	}

	// MARK: DELETE

	public function deleteLibro(): void
	{
		$this->setIdLibro();

		$this->checkValidationErrors();

		$deleteLibro = new FileManager();

		$idLibro = $this->getIdLibro();
		$libroPath = $deleteLibro->getFileUrl(self::SQL_COLUMN, self::SQL_TABLE, self::SQL_PRIMARY_KEY, $idLibro);

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
			$deleteLibro->deleteFile($libroPath);
		} else {
			$this->setStatus(404);
			$this->setMessage('¡El libro solicitado no existe!');
		}

		$this->getResponse();
	}

	// MARK: DELETE ALL

	public function deleteAllLibros(): void
	{
		$deleteAllLibros = new FileManager();
		$deleteAllLibros->setExtraDirectories(self::FOLDER);

		$statement =
			"DELETE FROM libros";

		$query = $this->getConnection()->prepare($statement);

		$query->execute();
		$numFilas = $query->affected_rows;
		$query->close();

		if ($numFilas > 0) {
			$deleteAllLibros->deleteAllFiles();
			$this->setStatus(204);
			$this->getResponse();
		} else {
			$this->setStatus(404);
			$this->setIntegrityError('¡No hay ningún libro para eliminar!');
			$this->checkIntegrityErrors();
		}
	}
}
