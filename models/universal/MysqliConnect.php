<?php

declare(strict_types=1);

require_once __DIR__ . '/EnvReader.php';

abstract class MysqliConnect extends EnvReader
{
	private string $hostname {
		get => getenv('HOSTNAME');
	}

	private string $username {
		get => getenv('USERNAME');
	}

	private string $password {
		get => getenv('PASSWORD');
	}

	private string $database {
		get => getenv('DATABASE');
	}

	protected mysqli $connection {
		final get {
			$this->connection = new mysqli($this->hostname, $this->username, $this->password, $this->database);
			$this->connection->set_charset('utf8');
			return $this->connection;
		}
	}

	protected function __construct()
	{
		parent::__construct();
	}
}
