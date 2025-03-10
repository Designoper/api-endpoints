<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/ApiResponse.php';
require_once __DIR__ . '/../universal/FileManager.php';

final class LibroId extends ApiResponse
{
	private readonly int $idLibro;

	public function __construct()
	{
		parent::__construct();
	}

	private function getIdLibro(): int
	{
		return $this->idLibro;
	}

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

	public function readLibro(): void
	{
		$this->setIdLibro();
		$this->checkValidationErrors();

		$idLibro = $this->getIdLibro();

		$host = $this->getHost();
		$defaultImage = FileManager::DEFAULT_IMAGE;

		$statement =
			"SELECT
				libros.id_libro,
				libros.titulo,
				CASE
					WHEN libros.portada IS NULL THEN CONCAT('$host', '$defaultImage')
					ELSE CONCAT('$host', libros.portada)
				END AS portada,
				libros.descripcion,
				libros.paginas,
				libros.fecha_publicacion,
				DATE_FORMAT(libros.fecha_publicacion, '%d-%m-%Y') AS fecha_publicacion_dd_mm_yyyy,
				categorias.categoria
			FROM libros
			NATURAL JOIN categorias
			WHERE libros.id_libro = ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("i", $idLibro);
		$query->execute();

		$libro = $query->get_result()->fetch_assoc();

		$query->close();

		if ($libro) {
			$this->setStatus(200);
			$this->setMessage('Libro obtenido.');
			$this->setContent($libro);
			$this->getResponse();
		} else {
			$this->setStatus(404);
			$this->setIntegrityError('¡El libro solicitado no existe!');
			$this->checkIntegrityErrors();
		}
	}
}
