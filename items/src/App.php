<?php
namespace WsHistory\Items;

use WsHistory\Common\Db;
use WsHistory\Common\InputException;
use WsHistory\Common\ServerException;

class App {
	private $db;

	/**
	* Get list of all known items
	*
	* @return array of maps with fields
	*     @string name,
	*     @string type
	*/
	public function run() {
		$db = new Db();
		$results = $db->conn->query("
			SELECT item_name AS name, item_type AS type
			FROM items
			WHERE item_id NOT IN (
				SELECT DISTINCT item_id FROM pc_voidtrader_items
			)");
		if ($results === false) {
			throw new ServerException('Database error', $db->conn->errorInfo()[2]);
		}
		return $results->fetchAll(\PDO::FETCH_ASSOC);
	}
}
?>
