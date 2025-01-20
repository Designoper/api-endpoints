<?php

require_once __DIR__ . '/../universal/ApiResponse.php';

final class Usuario extends ApiResponse
{
	// private string $statement;
	// private array $params = [];
	// private string $types = '';
	private string $usuario;
	private string $password;

	public function __construct()
	{
		parent::__construct();
	}

	private function getUsuario(): string
	{
		return $this->usuario;
	}

	private function getPassword(): string
	{
		return $this->password;
	}

	private function setUsuario(): void
	{
		$input = $_POST['usuario'] ?? null;
		$sanitizedInput = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if (empty($sanitizedInput)) {
			$this->setValidationError("El campo 'usuario' no puede estar vacío.");
			return;
		}

		$this->usuario = $sanitizedInput;
	}

	private function setPassword(): void
	{
		$input = $_POST['password'] ?? null;
		$sanitizedInput = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		if (empty($sanitizedInput)) {
			$this->setValidationError("El campo 'password' no puede estar vacío.");
			return;
		}

		$this->password = $sanitizedInput;
	}

	public function login(): void
	{
		$this->setUsuario();
		$this->setPassword();

		$this->checkValidationErrors();

		$statement = "SELECT *
		FROM usuarios
		WHERE nombre = ?
		AND PASSWORD = ?";

		$query = $this->getConnection()->prepare($statement);

		$usuario = $this->getUsuario();
		$password = $this->getPassword();

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

	public function createUsuario(): void
	{
		$usuario = $_POST["usuario"] ?? null;
		$password = $_POST["password"] ?? null;

		$statement =
			"INSERT INTO usuarios (nombre, password)
			VALUES (?, ?)";

		$query = $this->getConnection()->prepare($statement);

		$query->bind_param(
			"ss",
			$usuario,
			$password
		);

		$query->execute();
		$query->close();

		$this->setStatus(201);
		$this->setMessage("Usuario creado con éxito");
		$this->getResponse();
	}
}
