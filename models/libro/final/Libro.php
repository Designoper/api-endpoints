<?php

require_once __DIR__ . '/../errors/LibroValidationErrors.php';

final class Libro extends LibroValidationErrors
{
	public function __construct()
	{
		parent::__construct();
	}

	// MARK: MIN PAGINAS

	public function minPaginas(mixed $minimoPaginas): void
	{
		$this->validatePaginas($minimoPaginas);

		if ($this->validationErrorsExist()) {
			return;
		}

		$statement =
			"SELECT *
		FROM libros
		NATURAL JOIN categorias
		WHERE libros.paginas >= ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param(
			"i",
			$minimoPaginas,
		);
		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$libros
			? 'Libros con mínimo de ' . $minimoPaginas . ' páginas.'
			: 'Ningún libro tiene ' . $minimoPaginas . ' páginas como mínimo.';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($libros);
		$this->getResponse();
	}

	// MARK: MAX PAGINAS

	public function maxPaginas(mixed $maximoPaginas): void
	{
		$this->validatePaginas($maximoPaginas);

		if ($this->validationErrorsExist()) {
			return;
		}

		$statement = "SELECT *
			FROM libros
			NATURAL JOIN categorias
			WHERE libros.paginas <= ?";

		$query = $this->getConnection()->prepare($statement);
		$query->bind_param("i", $maximoPaginas);
		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$query->close();

		$message =
			$libros
			? '¡Libros obtenidos!'
			: '¡No hay libros!';

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($libros);
		$this->getResponse();
	}

	// MARK: PAGINAS ASC

	public function OrdenarPaginasAsc(): void
	{
		$statement =
			"SELECT *
			FROM libros
			NATURAL JOIN categorias
			ORDER BY libros.paginas ASC";

		$query = $this->getConnection()->prepare($statement);
		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		$message =
			$libros
			? '¡Libros obtenidos!'
			: '¡No hay libros!';

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($libros);
		$this->getResponse();
	}

	// MARK: PAGINAS DESC

	public function OrdenarPaginasDesc(): void
	{
		$statement =
			"SELECT *
			FROM libros
			NATURAL JOIN categorias
			ORDER BY libros.paginas DESC";

		$query = $this->getConnection()->prepare($statement);
		$query->execute();

		$libros = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		$message =
			$libros
			? '¡Libros obtenidos!'
			: '¡No hay libros!';

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($libros);
		$this->getResponse();
	}
}
