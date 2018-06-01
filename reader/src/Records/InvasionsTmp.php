<?php
namespace WsHistory\Reader\Records;

use WsHistory\Common\Db;
use WsHistory\Common\ServerException;
use WsHistory\Reader\Records\Records;

class InvasionsTmp extends Records {

	/**
	* Keep track of invasions to figure out which ones are done.
	*/
	private $invasions = [];

	function __construct(Db $db, string $platform) {
		$this->db = $db;
		$this->dbInfo = [
			'tableRecords' => "{$platform}_invasions",
			'tableRecordItems' => "{$platform}_invasion_items",
			'columnRecordId' => 'invasion_id'
		];
	}

	/* Documented in Records.php */
	public function readRecord(array $record): bool {
		if (isset($record['start'])) {
			$this->invasions[$record['id']] = $record;
		}
		$invasion = $this->invasions[$record['id']] ?? null;
		if ($invasion === null || abs($record['score']) < $invasion['endScore']) {
			// Skip ongoing invasions
			return false;
		}
		$items = [];
		foreach (['rewardsAttacker', 'rewardsDefender'] as $key) {
			if (isset($invasion[$key]['items'])) {
				foreach ($invasion[$key]['items'] as $item) {
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
				$invasion['end'] = abs(current($record['scoreHistory'])[0]);
			}
			else {
				$invasion['end'] = $invasion['start'];
			}
			$statement = $this->db->conn->prepare("
				INSERT INTO {$this->dbInfo['tableRecords']}
					({$this->dbInfo['columnRecordId']}, time_start, time_end, location)
				VALUES
					(?, ?, ?, ?)
				ON CONFLICT DO NOTHING");
			if (!$statement->execute([$record['id'], $invasion['start'], $invasion['end'], $invasion['location']])) {
				throw new ServerException('Database error', $statement->errorInfo()[2]);
			}
			if ($statement->rowCount() > 0) {
				$this->addItems($invasion['id'], $items);
				return true;
			}
		}
		return false;
	}
}
?>
