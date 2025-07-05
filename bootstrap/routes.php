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
use App\Presentation\Controller\FileUploadController;

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

// File Upload routes
$router->add(new Route(
    HttpMethod::GET,
    '/upload',
    FileUploadController::class . '::showForm'
));

$router->add(new Route(
    HttpMethod::POST,
    '/api/upload',
    FileUploadController::class . '::upload'
));

$router->add(new Route(
    HttpMethod::DELETE,
    '/api/upload/{path}',
    FileUploadController::class . '::delete'
));

return $router;
