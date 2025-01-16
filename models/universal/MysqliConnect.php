<?php

require_once __DIR__ . '/../../env.php';

abstract class MysqliConnect
{
	private string $hostname = HOSTNAME;
	private string $username = USERNAME;
	private string $password = PASSWORD;
	private string $database = DATABASE;
	private mysqli $connection;

	protected function __construct()
	{
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

	private function setConnection(): void
	{
		$this->connection = new mysqli($this->getHostname(), $this->getUsername(), $this->getPassword(), $this->getDatabase());
		$this->connection->set_charset('utf8');
	}
}
