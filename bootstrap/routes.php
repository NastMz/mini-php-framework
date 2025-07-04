<?php
declare(strict_types=1);

// bootstrap/routes.php

use App\Infrastructure\Routing\Router;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = $container ?? throw new RuntimeException('Container not provided to routes.php');
$router = $container->get(Router::class);

// Example registration (uncomment and adjust):
use App\Infrastructure\Routing\HttpMethod;
use App\Infrastructure\Routing\Route;
use App\Presentation\Controller\HomeController;
use App\Presentation\Controller\Api\ApiController;

// Web routes
$router->add(new Route(
    HttpMethod::GET,
    '/',
    HomeController::class . '::index'
));

$router->add(new Route(
    HttpMethod::GET,
    '/test',
    HomeController::class . '::index'
));

// API routes
$router->add(new Route(
    HttpMethod::GET,
    '/api/status',
    ApiController::class . '::status'
));

$router->add(new Route(
    HttpMethod::GET,
    '/api/info',
    ApiController::class . '::info'
));

return $router;
