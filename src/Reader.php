<?php
namespace WsHistory;

use WsHistory\Common\Config;
use WsHistory\Common\Db;
use WsHistory\Common\ServerException;
use WsHistory\Reader\Items;
use WsHistory\Reader\Records\Alerts;
use WsHistory\Reader\Records\Invasions;
use WsHistory\Reader\Records\InvasionsTmp;
use WsHistory\Reader\Records\Voidtraders;

class Reader {
	private $platform;
	private $recordsDir;

	private $db;
	private $filePositions;
	private $readers;

	public function __construct() {
		$this->db = new Db();
		$results = $this->db->conn->query("SELECT path, last_pos FROM file_positions");
		if ($results == false) {
			throw new ServerException('Database error', $this->db->conn->errorInfo()[2]);
		}
		while ($row = $results->fetch(\PDO::FETCH_ASSOC)) {
			$this->filePositions[$row['path']] = $row['last_pos'];
		}
		Items::init($this->db);
	}

	/**
	* Read files for the chosen platform.
	*
	* @string $platform
	*/
	public function readRecords($platform) {
		if (empty(Config::SourceDirs[$platform])) {
			throw new ServerException("Unknown platform \"$platform\"");
		}
		$this->recordsDir = Config::SourceDirs[$platform];
		$this->platform = $platform;
		$this->readers = [
			'alerts.db' => new Alerts($this->db, $platform),
			'invasions.db' => new Invasions($this->db, $platform),
			'invasions.db.tmp' => new InvasionsTmp($this->db, $platform),
			'voidtraders.db' => new Voidtraders($this->db, $platform)
		];
		foreach ($this->getFiles() as $filename) {
			$this->readFile($filename);
		}
	}

	/**
	* Return file names containing records for the chosen platform.
	*/
	private function getFiles() {
		if (!$dirHandle = opendir($this->recordsDir)) {
			throw new ServerException('Cannot open data directory', $this->recordsDir);
		}
		while ($filename = readdir($dirHandle)) {
			if ($filename[0] != '.') {
				yield "$filename";
			}
		}
	}

	/**
	* Get the file content and send each line to the appropriate reader
	*
	* @string $filename
	*/
	private function readFile(string $filename) {
		if (!isset($this->readers[$filename])) {
			return;
		}
		$isTmp = substr_compare($filename, '.tmp', -4) === 0;
		$recordTypeEnd = strpos($filename, '.');
		if ($recordTypeEnd === false) {
			throw new ServerException('Cannot determine record type', "$this->recordsDir/$filename");
		}
		$recordType = substr($filename, 0, $recordTypeEnd);
		if (!$fhRecords = fopen("$this->recordsDir/$filename", 'rb')) {
			throw new ServerException('Cannot open file', "$this->recordsDir/$filename");
		}
		if (!$isTmp) {
			// Find out where we left off last run
			$filePositionIdx = "$this->platform/$recordType";
			$filePosition = $this->filePositions[$filePositionIdx] ?? 0;
			$fileSize = fstat($fhRecords)['size'];
			if ($filePosition == $fileSize) {
				// Nothing new
				return;
			}
			elseif ($filePosition > $fileSize) {
				// File has gotten smaller; restart from the beginning
				$filePosition = 0;
			}
			elseif ($filePosition > 0) {
				// Seek to first new byte
				fseek($fhRecords, $filePosition);
			}
		}
		$recordReader = $this->readers[$filename];
		$rewind = 0;
		$numRecords = 0;
		for ($n = 1; $line = fgets($fhRecords); ++$n) {
			$rewind = strlen($line);
			if (!$idEnd = strpos($line, "\t")) {
				// Zero or false means no id
				echo "No id on line $n\n";
				continue;
			}
			$id = substr($line, 0, $idEnd);
			if (!preg_match('/^[0-9a-fA-F]+$/', $id)) {
				echo "Invalid id on line $n: '$id'\n";
				continue;
			}
			$record = json_decode(substr($line, $idEnd + 1), true);
			$record['id'] = $id;
			try {
				$wasAdded = $recordReader->readRecord($record);
			}
			catch (ServerException $e) {
				printf("%s: %s\n", $e->getMessage(), $e->getDetails());
				break;
			}
			$rewind = 0;
			if ($wasAdded) {
				++$numRecords;
			}
			if (!($n % 50000)) {
				printf("Read %d %s %s\r", $numRecords, $this->platform, $recordType);
				usleep(300000);
			}
		}
		if ($numRecords) {
			printf("Read %d %s %s\n", $numRecords, $this->platform, $recordType);
		}
		if (!$isTmp) {
			// Update last read position for non-temporary files
			$filePosition = ftell($fhRecords) - $rewind;
			$statement = $this->db->conn->prepare("
				INSERT INTO file_positions
					(path, last_pos)
				VALUES
					(?, ?)
				ON CONFLICT (path) DO UPDATE SET last_pos = excluded.last_pos");
			if (!$statement->execute([$filePositionIdx, $filePosition])) {
				throw new ServerException('Database error', $statement->errorInfo()[2]);
			}
		}
	}
}
