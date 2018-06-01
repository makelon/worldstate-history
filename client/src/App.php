<?php
namespace WsHistory\Client;

use WsHistory\Common\Db;
use WsHistory\Common\InputException;
use WsHistory\Common\ServerException;

class App {
	const NumRecords = 50;
	const RTAlerts = 'alerts';
	const RTInvasions = 'invasions';
	const RTVoidtraders = 'voidtraders';
	const ValidPlatforms = ['pc', 'ps4', 'xb1'];
	const ValidRecordTypes = [self::RTAlerts, self::RTInvasions, self::RTVoidtraders];

	private $db;
	private $platform;
	private $recordType;

	function __construct(string $params) {
		$paramsSplit = explode('/', trim($params, '/'));
		$numParams = count($paramsSplit);
		if ($numParams != 2) {
			throw new InputException([
				'platform' => 'Platform required',
				'type' => 'Record type required'
			]);
		}
		list($platform, $recordType) = $paramsSplit;
		if (!in_array($platform, self::ValidPlatforms)) {
			throw new InputException(['platform' => "Unknown platform \"$platform\""]);
		}
		if (!in_array($recordType, self::ValidRecordTypes)) {
			throw new InputException(['type' => "Unknown record type \"$recordType\""]);
		}
		$this->platform = $platform;
		$this->recordType = $recordType;
	}

	/**
	* Generate and execute the database query to fetch matching items.
	*
	* @return array Array of maps with fields
	*     @string name,
	*     @string type,
	*     @int start,
	*     @int end,
	*     @string info
	*/
	public function run() {
		$db = new Db();
		$searchQuery = $this->getSearchQuery();
		$offset = $_GET['o'] ?? 0;
		$dbColumns = "item_name AS name, item_type AS type, item_count AS count, time_start AS start, time_end AS end";
		switch ($this->recordType) {
			case self::RTAlerts:
				$dbColumns .= ", mission_type AS info";
				$dbColumnRecordId = 'alert_id';
				$dbTableRecords = "{$this->platform}_alerts";
				$dbTableRecordItems = "{$this->platform}_alert_items";
				break;
			case self::RTInvasions:
				$dbColumns .= ", location AS info";
				$dbColumnRecordId = 'invasion_id';
				$dbTableRecords = "{$this->platform}_invasions";
				$dbTableRecordItems = "{$this->platform}_invasion_items";
				break;
			case self::RTVoidtraders:
				$dbColumns .= ", location AS info";
				$dbColumnRecordId = 'voidtrader_id';
				$dbTableRecords = "{$this->platform}_voidtraders";
				$dbTableRecordItems = "{$this->platform}_voidtrader_items";
				break;
		}
		$conditions = [
			"i.item_id = r_i.item_id",
			"r_i.$dbColumnRecordId = r.$dbColumnRecordId"
		];
		$params = [
			self::NumRecords,
			$offset
		];
		if ($searchQuery !== false) {
			$conditions[] = "to_tsvector('simple', i.item_name || ' ' || i.item_type) @@ to_tsquery('simple', ?)";
			array_unshift($params, $searchQuery);
		}
		$conditionsStr = implode(" AND ", $conditions);
		$statement = $db->conn->prepare("
			SELECT
				$dbColumns
			FROM
				items i, $dbTableRecordItems r_i, $dbTableRecords r
			WHERE
				$conditionsStr
			ORDER BY time_end DESC
			LIMIT ?
			OFFSET ?");
		if (!$statement->execute($params)) {
			throw new ServerException('Database error', $statement->errorInfo()[2]);
		}
		return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	* Remove any non-word characters from query and create a ts_query-compatible search string
	*/
	private function getSearchQuery() {
		$query = $_GET['q'] ?? '';
		$query = trim(preg_replace('/[\x21-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]+/', ' ', $query));
		if ($query === '') {
			return false;
		}
		$words = [];
		$word = strtok($query, ' ');
		while ($word !== false) {
			$words[] = $word;
			$word = strtok(' ');
		}
		return implode(' & ', $words) . ':*';
	}
}
?>
