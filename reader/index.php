#!/usr/bin/env php
<?php
use WsHistory\Common\Config;
use WsHistory\Common\ServerException;

require(__DIR__ . '/autoload.php');

try {
	$reader = new WsHistory\Reader\App();
	$reader->readRecords('pc');
	$reader->readRecords('ps4');
	$reader->readRecords('xb1');
}
catch (ServerException $e) {
	echo $e->getMessage() . $e->getDetails() . "\n";
}
?>
