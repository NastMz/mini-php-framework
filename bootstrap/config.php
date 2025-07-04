<?php
declare(strict_types=1);

use App\Infrastructure\Config\EnvLoader;

// Load environment variables
EnvLoader::load();

return require __DIR__ . '/../config/settings.php';
