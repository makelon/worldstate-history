<?php
namespace WsHistory;

use WsHistory\Common\Db;
use WsHistory\Common\InputException;
use WsHistory\Common\ServerException;

class History extends AbstractHandler {
	const RTAlerts = 'alerts';
	const RTInvasions = 'invasions';
	const RTVoidtraders = 'voidtraders';

	const ValidPlatforms = ['pc', 'ps4', 'xb1'];
	const ValidComponents = [self::RTAlerts, self::RTInvasions, self::RTVoidtraders];

	/**
	* Maximum amount of records to fetch in a database query
	*/
	const NumRecords = 50;

	/**
	* Requested platform
	*/
	private $platform;

	/**
	* Requested component
	*/
	private $component;

	/**
	* Enable browser cache for fast comparison between different platforms
	*
	* @return int Browser cache lifetime
	*/
	public function getClientCache(): int {
		return 300;
	}

	/**
	* Validate and store request parameters
	*/
	public function setReqParams(array $reqParams): void {
		$inputErrors = [];
		if (!isset($reqParams['platform'])) {
			$inputErrors['platform'] = 'Missing platform parameter';
		}
		if (!isset($reqParams['component'])) {
			$inputErrors['component'] = 'Missing component parameter';
		}
		if (count($inputErrors) === 0) {
			$platform = $reqParams['platform'];
			if (!in_array($platform, self::ValidPlatforms)) {
				$inputErrors['platform'] = "Unknown platform '$platform'";
			}
			$component = $reqParams['component'];
			if (!in_array($component, self::ValidComponents)) {
				$inputErrors['component'] = "Unknown component '$component'";
			}
		}

		if (count($inputErrors) > 0) {
			throw new InputException($inputErrors);
		}

		$this->platform = $platform;
		$this->component = $component;
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
		switch ($this->component) {
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
