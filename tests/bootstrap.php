<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Config\EnvLoader;

// Load test environment variables
EnvLoader::load(__DIR__ . '/../.env.test');

// Set test environment
putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

// Override settings for testing
$_ENV['DATABASE_PATH'] = ':memory:';
$_ENV['LOG_LEVEL'] = 'debug';
