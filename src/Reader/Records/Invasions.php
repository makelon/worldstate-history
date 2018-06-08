<?php
namespace WsHistory\Reader\Records;

use WsHistory\Common\Db;
use WsHistory\Common\ServerException;

class Invasions extends AbstractRecords {
	public function __construct(Db $db, string $platform) {
		$this->db = $db;
		$this->dbTableRecords = "{$platform}_invasions";
		$this->dbTableRecordItems = "{$platform}_invasion_items";
		$this->dbColumnRecordId = 'invasion_id';
	}

	/* Documented in AbstractRecords.php */
	public function readRecord(array $record): bool {
		if (!isset($record['start'])) {
			return false;
		}
		$items = [];
		foreach (['rewardsAttacker', 'rewardsDefender'] as $key) {
			if (isset($record[$key]['items'])) {
				foreach ($record[$key]['items'] as $item) {
					$items[] = [
						'name' => $item['name'],
						'count' => $item['count'] ?? 1
					];
				}
			}
		}
		if (count($items) > 0) {
			if (count($record['scoreHistory'])) {
				end($record['scoreHistory']);
				$record['end'] = abs(current($record['scoreHistory'])[0]);
			}
			else {
				$record['end'] = $record['start'];
			}
			$statement = $this->db->conn->prepare("
				INSERT INTO {$this->dbTableRecords}
					({$this->dbColumnRecordId}, time_start, time_end, location)
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
