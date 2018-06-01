<?php
namespace WsHistory\Reader\Records;

use WsHistory\Common\Db;
use WsHistory\Common\ServerException;
use WsHistory\Reader\Records\Records;

class Voidtraders extends Records {
	function __construct(Db $db, string $platform) {
		$this->db = $db;
		$this->dbInfo = [
			'tableRecords' => "{$platform}_voidtraders",
			'tableRecordItems' => "{$platform}_voidtrader_items",
			'columnRecordId' => 'voidtrader_id'
		];
	}

	public function readRecord(array $record): bool {
		if (!isset($record['start']) || !isset($record['end']) || !isset($record['items']) || count($record['items']) > 50) {
			return false;
		}
		$items = [];
		foreach ($record['items'] as $item) {
			$item['count'] = 1;
			$items[] = $item;
		}
		if (count($items) > 0) {
			$statement = $this->db->conn->prepare("
				INSERT INTO {$this->dbInfo['tableRecords']}
					({$this->dbInfo['columnRecordId']}, time_start, time_end, location)
				VALUES
					(?, ?, ?, ?)
				ON CONFLICT DO NOTHING");
			if (!$statement->execute([$record['id'], $record['start'], $record['end'], $record['location']])) {
				throw new ServerException('Database error', $statement->errorInfo()[2]);
			}
			if ($statement->rowCount() > 0) {
				$this->addItems($record['id'], $items);
				return true;
			}
		}
		return false;
	}
}
?>
