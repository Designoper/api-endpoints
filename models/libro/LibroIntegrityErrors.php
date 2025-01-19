<?php

require_once __DIR__ . '/LibroValidationErrors.php';

abstract class LibroIntegrityErrors extends LibroValidationErrors
{
	protected function __construct()
	{
		parent::__construct();
	}

	protected function librosExists(): void
	{
		$statement =
			"SELECT * FROM libros";

		$query = $this->getConnection()->prepare($statement);
		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if (!$libros) {
			$this->setStatusCode(404);
			$this->setIntegrityError('¡No hay ningún libro!');
		}
	}

	protected function tituloExists(string $titulo): void
	{
		$statement =
			"SELECT * FROM libros
		WHERE titulo = ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("s", $titulo);
		$query->execute();

		$libro = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if ($libro) {
			$this->setStatusCode(409);
			$this->setIntegrityError('¡El título del libro ya esta asignado a otro libro!');
		}
	}

	protected function tituloUpdateExists(string $titulo, int $idLibro): void
	{
		$statement =
			"SELECT * FROM libros
		WHERE titulo = ?
		AND id_libro != ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("si", $titulo, $idLibro);
		$query->execute();

		$libro = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if ($libro) {
			$this->setStatusCode(409);
			$this->setIntegrityError('¡El título del libro ya esta asignado a otro libro!');
		}
	}

	protected function idLibroExists(int $idLibro): void
	{
		$statement =
			"SELECT * FROM libros
		WHERE id_libro = ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("i", $idLibro);
		$query->execute();

		$libro = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if (!$libro) {
			$this->setStatusCode(404);
			$this->setIntegrityError('¡El libro solicitado no existe!');
		}
	}

	protected function idCategoriaExists(int $idCategoria): void
	{
		$statement =
			"SELECT * FROM categorias
		WHERE id_categoria = ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("i", $idCategoria);
		$query->execute();

		$categoria = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if (!$categoria) {
			$this->setStatusCode(404);
			$this->setIntegrityError('¡La categoria seleccionada no existe!');
		}
	}
}
