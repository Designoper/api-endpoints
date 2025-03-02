<?php

declare(strict_types=1);

require_once __DIR__ . '/EnvReader.php';

abstract class MysqliConnect extends EnvReader
{
    private const string HOSTNAME_KEY = 'HOSTNAME';
    private const string USERNAME_KEY = 'USERNAME';
    private const string PASSWORD_KEY = 'PASSWORD';
    private const string DATABASE_KEY = 'DATABASE';

    private readonly mysqli $connection;

    protected function __construct()
    {
        parent::__construct();

        $hostname = getenv(self::HOSTNAME_KEY);
        $username = getenv(self::USERNAME_KEY);
        $password = getenv(self::PASSWORD_KEY);
        $database = getenv(self::DATABASE_KEY);

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->connection = new mysqli($hostname, $username, $password, $database);
        $this->connection->set_charset('utf8');
    }

    protected function getConnection(): mysqli
    {
        return $this->connection;
    }
}