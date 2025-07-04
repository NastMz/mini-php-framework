<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Http\Request;
use App\Infrastructure\Middleware\MiddlewareDispatcher;
use App\Infrastructure\Routing\RouterHandler;

// 1) Build container
$container = require __DIR__ . '/../bootstrap/dependencies.php';

// 2) Create Request
$request = Request::fromGlobals();

// 3) Load middleware and router
$middlewares    = require __DIR__ . '/../bootstrap/middleware.php';
$router         = (function() use ($container) {
    return require __DIR__ . '/../bootstrap/routes.php';
})();
$routerHandler  = new RouterHandler($router);

// 4) Build dispatcher
$dispatcher = new MiddlewareDispatcher($middlewares, $routerHandler);

// 5) Dispatch and send
$response = $dispatcher->handle($request);
$response->send();
