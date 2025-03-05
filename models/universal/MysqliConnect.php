<?php

declare(strict_types=1);

require_once __DIR__ . '/EnvReader.php';

abstract class MysqliConnect extends EnvReader
{
	private readonly string $hostname;
	private readonly string $username;
	private readonly string $password;
	private readonly string $database;
	protected readonly mysqli $connection;

	protected function __construct()
	{
		parent::__construct();

		$this->hostname = getenv('HOSTNAME');
		$this->username = getenv('USERNAME');
		$this->password = getenv('PASSWORD');
		$this->database = getenv('DATABASE');

		$this->connection = new mysqli(
			$this->hostname,
			$this->username,
			$this->password,
			$this->database
		);

		$this->connection->set_charset('utf8');
	}
}
