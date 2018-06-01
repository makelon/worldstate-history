<?php
namespace WsHistory\Common;

class Config {
	/**
	* Debug flag that if enabled adds more detail to error messages
	*/
	const Debug = false;

	/**
	* Add Access-Control-Allow-Origin HTTP header to specify
	* permitted domains for Cross-Origin Resource Sharing.
	*/
	const Cors = '*';

	/**
	* Credentials
	*/
	const DbHost = '127.0.0.1';
	const DbName = '';
	const DbUser = '';
	const DbPassword = '';

	/**
	* Whether to use persistent connections (recommended)
	*/
	const DbOptions = [
		\PDO::ATTR_PERSISTENT => true
	];

	/**
	* Paths to worlstate-server output
	*/
	const SourceDirs = [
		'pc' => '',
		'ps4' => '',
		'xb1' => '',
	];
}
?>
