<?php

declare(strict_types=1);

require_once __DIR__ . '/../universal/FileManager.php';

final class Libro extends FileManager
{
	public function __construct()
	{
		parent::__construct();
	}

	public function readLibros(): void
	{
		$host = $this->getHost();
		$default = $this->getDefaultImage();
		$statement =
			"SELECT
			libros.id_libro,
			libros.titulo,
			CASE
				WHEN libros.portada IS NULL THEN '$default'
				ELSE CONCAT('$host', libros.portada)
			END AS portada,
			libros.descripcion,
			libros.paginas,
			libros.fecha_publicacion,
			DATE_FORMAT(libros.fecha_publicacion, '%d-%m-%Y') AS fecha_publicacion_dd_mm_yyyy,
			categorias.categoria
		FROM libros
		NATURAL JOIN categorias
		ORDER BY libros.titulo";


		$query = $this->getConnection()->prepare($statement);

		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$libros
			? 'Libros obtenidos'
			: 'No hay ningún libro';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($libros);
		// header('Cache-Control: public, max-age=31536000, must-revalidate');
		$this->getResponse();
	}
}
