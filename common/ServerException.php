<?php
namespace WsHistory\Common;

class ServerException extends \Exception {
	private $details;

	public function __construct($message, $details = '') {
		$this->details = $details;
		parent::__construct($message);
	}

	public function getDetails() {
		return $this->details;
	}
}
?>
