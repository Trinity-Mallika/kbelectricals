<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

class Database
{
	private PDO $con;

	public function __construct()
	{

		$isLocal = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === 'trinity' ||  $_SERVER['SERVER_NAME'] === '192.168.1.8');

		if ($isLocal) {
			$dbhost = "localhost";
			$dbuser = "root";
			$dbpass = "";
			$dbname = "kbelectricals";
		} else {
			$dbhost = "localhost";
			$dbuser = "u975817652_kbelectricals";
			$dbpass = "nbkzf;Uyn.V2WXN";
			$dbname = "u975817652_kbelectricals";
		}

		$dsn = "mysql:host={$dbhost};dbname={$dbname};charset=utf8mb4";

		try {
			$this->con = new PDO($dsn, $dbuser, $dbpass, [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false,
				PDO::ATTR_PERSISTENT         => false,
			]);
		} catch (PDOException $e) {
			die("DB Connection Failed: " . $e->getMessage());
		}
	}

	public function getConnection(): PDO
	{
		return $this->con;
	}
}
