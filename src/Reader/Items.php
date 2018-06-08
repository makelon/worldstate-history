<?php
namespace WsHistory\Reader;

use WsHistory\Common\Db;
use WsHistory\Common\InputException;
use WsHistory\Common\ServerException;

class Items {
	/**
	* Database connection instance
	*/
	private static $db;

	/**
	* Lookup table for item ids
	*/
	private static $knownItems;

	/**
	* Get database connection instance and load item list
	*/
	public static function init(Db $db) {
		if (!isset(self::$db)) {
			self::$db = $db;
			self::getKnownItems();
		}
	}

	/**
	* Load item list
	*/
	private static function getKnownItems() {
		$results = self::$db->conn->query("SELECT item_id, item_name FROM items");
		if ($results === false) {
			throw new ServerException('Database error', self::$conn->errorInfo()[2]);
		}
		while ($row = $results->fetch(\PDO::FETCH_ASSOC)) {
			self::addKnownItem($row['item_name'], $row['item_id']);
		}
	}

	/**
	* Add new entry to list of known items
	*
	* @string $itemName
	* @int $itemId
	*/
	public static function addKnownItem(string $itemName, int $itemId) {
		self::$knownItems[$itemName] = $itemId;
	}

	/**
	* Find id of a given item or add new item if it didn't exist
	*
	* @string $itemName
	* @string $itemType
	* @return int Item id
	*/
	public static function getItemId(string $itemName, string $itemType) {
		if (isset(self::$knownItems[$itemName])) {
			$itemId = self::$knownItems[$itemName];
		}
		else {
			$statement = self::$db->conn->prepare("
				INSERT INTO items
					(item_name, item_type)
				VALUES
					(?, ?)");
			if ($statement->execute([$itemName, $itemType]) === false) {
				throw new ServerException('Database error', $statement->errorInfo()[2]);
			}
			$itemId = self::$db->conn->lastInsertId('items_item_id_seq');
			self::addKnownItem($itemName, $itemId);
		}
		return $itemId;
	}

}
