<?php

require_once __DIR__ . '/../universal/ApiResponse.php';

final class Usuario extends ApiResponse
{
	private string $statement;
	private array $params = [];
	private string $types = '';

	public function __construct()
	{
		parent::__construct();
	}

	private function getStatement(): string
	{
		return $this->statement;
	}

	private function getParams(): array
	{
		return $this->params;
	}

	private function getTypes(): string
	{
		return $this->types;
	}

	private function addStatement(string $statement): void
	{
		$this->statement .= ' ' . $statement;
	}

	private function addParam(string|int $param): void
	{
		$this->params[] = $param;
	}

	private function addType(string $type): void
	{
		$this->types .= $type;
	}

	public function login(): void
	{
		$usuario = $_POST["usuario"] ?? null;
		$password = $_POST["password"] ?? null;

		$statement = "SELECT *
		FROM usuarios
		WHERE nombre = ?
		AND PASSWORD = ?";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"ss",
			$usuario,
			$password
		);

		$query->execute();

		$usuario = $query->get_result()->fetch_all(MYSQLI_ASSOC);

		$query->close();

		if (!$usuario) {
			$this->setStatus(401);
			$this->setMessage("Credenciales inválidas");
			$this->getResponse();
			exit();
		}
	}
}
