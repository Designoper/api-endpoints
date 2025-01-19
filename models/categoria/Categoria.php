<?php

require_once __DIR__ . '/../universal/ApiResponse.php';

final class Categoria extends ApiResponse
{
	private string $statement =
	"SELECT *
	FROM categorias
	WHERE 1=1";

	public function __construct()
	{
		parent::__construct();
	}

	private function getStatement(): string
	{
		return $this->statement;
	}

	public function readCategorias(): void
	{
		$query = $this->getConnection()->prepare($this->getStatement());

		$query->execute();

		$categorias = $query->get_result()->fetch_all(MYSQLI_ASSOC);
		$message =
			$categorias
			? 'Categorias obtenidas'
			: 'No hay coincidencias';

		$query->close();

		$this->setStatus(200);
		$this->setMessage($message);
		$this->setContent($categorias);
		$this->getResponse();
	}
}
