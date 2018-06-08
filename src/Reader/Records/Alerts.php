<?php
namespace WsHistory\Reader\Records;

use WsHistory\Common\Db;
use WsHistory\Common\ServerException;

class Alerts extends AbstractRecords {
	public function __construct(Db $db, string $platform) {
		$this->db = $db;
		$this->dbTableRecords = "{$platform}_alerts";
		$this->dbTableRecordItems = "{$platform}_alert_items";
		$this->dbColumnRecordId = 'alert_id';
	}

	/* Documented in AbstractRecords.php */
	public function readRecord(array $record): bool {
		if (!isset($record['start']) || !isset($record['end']) || !isset($record['rewards']['items'])) {
			return false;
		}
		$items = $record['rewards']['items'];
		if (count($items) > 0) {
			$statement = $this->db->conn->prepare("
				INSERT INTO {$this->dbTableRecords}
					({$this->dbColumnRecordId}, time_start, time_end, mission_type)
				VALUES
					(?, ?, ?, ?)
				ON CONFLICT DO NOTHING");
			if (!$statement->execute([$record['id'], $record['start'], $record['end'], $record['missionType']])) {
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
