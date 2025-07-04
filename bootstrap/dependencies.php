<?php
declare(strict_types=1);

use App\Infrastructure\DI\Container;
use Psr\Container\ContainerInterface;

/** @var array<string,mixed> $settings */
$settings = require __DIR__ . '/config.php';

/** @var ContainerInterface $container */
$container = Container::build($settings, [
    // Example custom definitions:
    // PDO::class => fn(Container $c) => new PDO(
    //     $c->get('settings')['db']['dsn'],
    //     $c->get('settings')['db']['user'],
    //     $c->get('settings')['db']['pass'],
    // ),
]);

return $container;
