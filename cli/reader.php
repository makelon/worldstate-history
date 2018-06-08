#!/usr/bin/env php
<?php
use WsHistory\Common\ServerException;
use WsHistory\Reader;

require(__DIR__ . '/../vendor/autoload.php');

try {
	$reader = new Reader();
	$reader->readRecords('pc');
	$reader->readRecords('ps4');
	$reader->readRecords('xb1');
}
catch (ServerException $e) {
	printf("%s: %s\n", $e->getMessage(), $e->getDetails());
}
