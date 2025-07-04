<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;
use App\Infrastructure\Routing\Router;

/**
 * Command to list all registered routes in the application.
 */
class ListRoutesCommand extends Command
{
    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('route:list');
        $this->setDescription('List all registered routes');
    }

    /**
     * Execute the command to list all registered routes.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
    protected function execute(array $arguments, array $options): int
    {
        try {
            // Load the complete bootstrap container and routes
            require_once __DIR__ . '/../../../../bootstrap/dependencies.php';
            $router = require_once __DIR__ . '/../../../../bootstrap/routes.php';
            
            $this->info('Registered Routes:');
            $this->info(str_repeat('-', 80));
            
            // Get routes from the router
            $routes = $router->getRoutes();
            
            if (empty($routes)) {
                $this->warning('No routes found');
                return 0;
            }

            $this->info(sprintf("%-10s %-30s %s", 'METHOD', 'PATH', 'HANDLER'));
            $this->info(str_repeat('-', 80));

            foreach ($routes as $route) {
                $this->info(sprintf(
                    "%-10s %-30s %s",
                    $route->method->value,
                    $route->pathPattern,
                    is_string($route->handler) ? $route->handler : 'Closure'
                ));
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to list routes: ' . $e->getMessage());
            return 1;
        }
    }
}
