<?php
namespace WsHistory\Reader\Records;

use WsHistory\Common\Db;
use WsHistory\Common\ServerException;
use WsHistory\Reader\Items;

abstract class AbstractRecords {
	/**
	* Database connection instance
	*/
	protected $db;

	/**
	* Database table containing the component's records
	*/
	protected $dbTableRecords;

	/**
	* Database table containing the component's item history
	*/
	protected $dbTableRecordItems;

	/**
	* Name of the ID column in the component's database tables
	*/
	protected $dbColumnRecordId;

	/**
	* The constructor sets the db info property values
	*/
	abstract public function __construct(Db $db, string $platform);

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
				INSERT INTO {$this->dbTableRecordItems}
					({$this->dbColumnRecordId}, item_id, item_count)
				VALUES
					(?, ?, ?)");
			if ($statement->execute([$recordId, $itemId, $item['count']]) === false) {
				throw new ServerException('Database error', $statement->errorInfo()[2]);
			}
		}
	}
}
