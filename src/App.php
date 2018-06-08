<?php
namespace WsHistory;

use FastRoute;
use WsHistory\Common\Db;
use WsHistory\Common\InputException;

class App {
	/**
	* Request handler
	*/
	private $requestHandler;

	/**
	* Set up routing and call the request handler
	*
	* @return response data
	*/
	public function run() {
		$requestMethod = $_SERVER['REQUEST_METHOD'];
		$path = $_SERVER['PATH_INFO'] ?? '';

		$dispatcher = FastRoute\simpleDispatcher(function($router) {
			$router->addRoute('GET', '', ['WsHistory\ItemList', 'run']);
			$router->addRoute('GET', '/history/{platform}/{component}', ['WsHistory\History', 'run']);
		});
		$routeInfo = $dispatcher->dispatch($requestMethod, $path);
		if ($routeInfo[0] !== FastRoute\Dispatcher::FOUND) {
			throw new InputException("No handler for method '$requestMethod:$path'", 404);
		}

		[$handlerClass, $handlerMethod] = $routeInfo[1];
		$this->requestHandler = new $handlerClass();
		$this->requestHandler->setReqParams($routeInfo[2]);
		return $this->requestHandler->$handlerMethod();
	}

	public function getClientCache(): int {
		return $this->requestHandler->getClientCache();
	}
}
