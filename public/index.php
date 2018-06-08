<?php
use WsHistory\Common\Config;
use WsHistory\Common\InputException;
use WsHistory\Common\ServerException;
use WsHistory\App;

require(__DIR__ . '/../vendor/autoload.php');

if (!empty(Config::Cors)) {
	header('Access-Control-Allow-Origin: ' . Config::Cors);
}
header('Content-Type: application/json');

$clientCache = 0;
try {
	$app = new App();
	$data = $app->run();
	$clientCache = $app->getClientCache();
	$response = $data;
}
catch (InputException $e) {
	$response = [
		'error' => $e->getDetails()
	];
	http_response_code($e->getCode());
}
catch (ServerException $e) {
	$response = [
		'error' => $e->getMessage()
	];
	if (Config::Debug) {
		$response['details'] = $e->getDetails();
	}
	http_response_code($e->getCode());
}
catch (\Exception $e) {
	$response = [
		'error' => 'An unknown error occurred'
	];
	if (Config::Debug) {
		$response['details'] = $e->getMessage();
	}
	http_response_code(500);
}
header('Cache-Control: ' . ($clientCache ? "max-age=$clientCache" : 'no-cache, no-store'));

echo json_encode($response);
