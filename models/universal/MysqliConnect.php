<?php

$env_file = fopen('.env', 'r');
if ($env_file) {
	while (($line = fgets($env_file)) !== false) {
		$line = trim($line);
		if (empty($line) || $line[0] == '#') continue;

		list($key, $value) = explode('=', $line, 2);
		if (!empty($key) && !empty($value)) {
			putenv(sprintf('%s=%s', $key, $value));
		}
	}
	fclose($env_file);
}

abstract class MysqliConnect
{
	private readonly string $hostname;
	private readonly string $username;
	private readonly string $password;
	private readonly string $database;
	private readonly mysqli $connection;

	protected function __construct()
	{
		$this->setHostname();
		$this->setUsername();
		$this->setPassword();
		$this->setDatabase();
		$this->setConnection();
	}

	// MARK: GETTERS

	private function getHostname(): string
	{
		return $this->hostname;
	}

	private function getUsername(): string
	{
		return $this->username;
	}

	private function getPassword(): string
	{
		return $this->password;
	}

	private function getDatabase(): string
	{
		return $this->database;
	}

	protected function getConnection(): mysqli
	{
		return $this->connection;
	}

	// MARK: SETTERS

	private function setHostname(): void
	{
		$this->hostname = getenv('HOSTNAME');
	}

	private function setUsername(): void
	{
		$this->username = getenv('USERNAME');
	}

	private function setPassword(): void
	{
		$this->password = getenv('PASSWORD');
	}

	private function setDatabase(): void
	{
		$this->database = getenv('DATABASE');
	}

	private function setConnection(): void
	{
		$this->connection = new mysqli($this->getHostname(), $this->getUsername(), $this->getPassword(), $this->getDatabase());
		$this->connection->set_charset('utf8');
	}
}
