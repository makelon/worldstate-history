<?php
namespace WsHistory;

abstract class AbstractHandler {
	/**
	* Request parameters provided by the router
	*/
	protected $reqParams;

	abstract public function run();

	/**
	* Disable browser cache by default
	*/
	public function getClientCache(): int {
		return 0;
	}

	/**
	* Validate and store request parameters
	*/
	public function setReqParams(array $reqParams): void {
		$this->reqParams = $reqParams;
	}
}
