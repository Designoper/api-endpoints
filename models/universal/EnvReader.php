<?php

abstract class EnvReader
{
	protected function __construct()
	{
		$this->getEnvironmentVariables();
	}

	private function getEnvironmentVariables(): void
	{
		$env_file = fopen('.env', 'r');

		if ($env_file) {
			while (($line = fgets($env_file))) {
				$line = trim($line);
				if (empty($line) || $line[0] === '#') continue;

				list($key, $value) = explode('=', $line, 2);
				if (!empty($key) && !empty($value)) {
					putenv(sprintf('%s=%s', $key, $value));
				}
			}

			fclose($env_file);
		}
	}
}
