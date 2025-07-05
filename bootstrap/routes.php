<?php
declare(strict_types=1);

// bootstrap/routes.php

use App\Infrastructure\Routing\Router;
use App\Infrastructure\Routing\AutoRouter;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = $container ?? throw new RuntimeException('Container not provided to routes.php');
$router = $container->get(Router::class);

// Auto-discover routes from controller attributes
$autoRouter = new AutoRouter(
    'App',
    __DIR__ . '/../src/Presentation/Controller'
);

$autoResult = $autoRouter->discover();
$autoRouter->registerRoutes($router);

// Debug information (only in development)
if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    error_log('Auto-discovered routes: ' . json_encode($autoResult['debug']));
}

return $router;
