<?php
namespace WsHistory\Reader\Records;

use WsHistory\Common\Db;
use WsHistory\Common\ServerException;
use WsHistory\Reader\Items;

abstract class Records {
	protected $db;
	protected $dbInfo;

	abstract function __construct(Db $db, string $platform);

	/**
	* Read and add record info and any items to the database.
	*
	* @array $record
	* @return bool Whether anything was added to the database
	*/
	abstract public function readRecord(array $record): bool;

	/**
	* Add items found in the record to the database.
	*
	* @string $recordId
	* @array $items Array of maps with fields
	*     @string name,
	*     @string type,
	*     @int count
	*/
	protected function addItems(string $recordId, array $items) {
		foreach ($items as $item) {
			if (preg_match('/^(\d+) Endo$/', $item['name'], $m)) {
				$item['name'] = 'Endo';
				$item['count'] *= $m[1];
			}
			$itemId = Items::getItemId($item['name'], $item['type'] ?? 'Misc');
			$statement = $this->db->conn->prepare("
				INSERT INTO {$this->dbInfo['tableRecordItems']}
					({$this->dbInfo['columnRecordId']}, item_id, item_count)
				VALUES
					(?, ?, ?)");
			if ($statement->execute([$recordId, $itemId, $item['count']]) === false) {
				throw new ServerException('Database error', $statement->errorInfo()[2]);
			}
		}
	}
}
?>
