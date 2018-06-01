<?php
namespace WsHistory\Common;

use WsHistory\Common\Config;

class Db {
	public $conn;

	function __construct() {
		$dsn = sprintf('pgsql:host=%s;dbname=%s', Config::DbHost, Config::DbName);
		try {
			$this->conn = new \PDO($dsn, Config::DbUser, Config::DbPassword, Config::DbOptions);
		}
		catch (\PDOException $e) {
			throw new \Exception('DB error: ' . $e->getMessage());
		}
	}
}
?>
