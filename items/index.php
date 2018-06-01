<?php
use WsHistory\Common\Config;
use WsHistory\Common\InputException;
use WsHistory\Common\ServerException;
use WsHistory\Items\App;

require(__DIR__ . '/autoload.php');

try {
	$app = new App();
	$items = $app->run();
	$response = [
		'status' => 'success',
		'data' => $items
	];
}
catch (InputException $e) {
	$response = [
		'status' => 'fail',
		'data' => $e->getDetails()
	];
	http_response_code(400);
}
catch (ServerException $e) {
	$response = [
		'status' => 'error',
		'message' => 'A database error occurred'
	];
	if (Config::Debug) {
		$response['data'] = $e->getDetails();
	}
	http_response_code(500);
}
catch (\Exception $e) {
	$response = [
		'status' => 'error',
		'message' => 'An unknown error occurred'
	];
	http_response_code(500);
}

if (!empty(Config::Cors)) {
	header('Access-Control-Allow-Origin: ' . Config::Cors);
}
header('Cache-Control: no-cache, no-store');
header('Content-Type: application/json');
echo json_encode($response);
?>
