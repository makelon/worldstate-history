<?php
namespace WsHistory\Reader\Records;

use WsHistory\Common\Db;
use WsHistory\Common\ServerException;
use WsHistory\Reader\Records\Records;

class Alerts extends Records {
	function __construct(Db $db, string $platform) {
		$this->db = $db;
		$this->dbInfo = [
			'tableRecords' => "{$platform}_alerts",
			'tableRecordItems' => "{$platform}_alert_items",
			'columnRecordId' => 'alert_id'
		];
	}

	/* Documented in Records.php */
	public function readRecord(array $record): bool {
		if (!isset($record['start']) || !isset($record['end']) || !isset($record['rewards']['items'])) {
			return false;
		}
		$items = $record['rewards']['items'];
		if (count($items) > 0) {
			$statement = $this->db->conn->prepare("
				INSERT INTO {$this->dbInfo['tableRecords']}
					({$this->dbInfo['columnRecordId']}, time_start, time_end, mission_type)
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
?>
