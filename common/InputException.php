<?php
namespace WsHistory\Common;

class InputException extends \Exception {
	private $details;

	public function __construct($details = []) {
		$this->details = $details;
		parent::__construct('Input error');
	}

	public function getDetails() {
		return $this->details;
	}
}
?>
