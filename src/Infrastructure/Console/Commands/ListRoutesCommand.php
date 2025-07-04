<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

class ListRoutesCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('route:list');
        $this->setDescription('List all registered routes');
    }

    protected function execute(array $arguments, array $options): int
    {
        $routesFile = __DIR__ . '/../../../../bootstrap/routes.php';
        
        if (!file_exists($routesFile)) {
            $this->error('Routes file not found');
            return 1;
        }

        $this->info('Registered Routes:');
        $this->info(str_repeat('-', 80));
        
        // This is a simple implementation - in a real app you'd want to 
        // integrate with your actual router to get registered routes
        $routes = $this->parseRoutesFile($routesFile);
        
        if (empty($routes)) {
            $this->warning('No routes found');
            return 0;
        }

        foreach ($routes as $route) {
            echo sprintf(
                "%-10s %-30s %s\n",
                $route['method'],
                $route['path'],
                $route['handler']
            );
        }

        return 0;
    }

    private function parseRoutesFile(string $file): array
    {
        $content = file_get_contents($file);
        $routes = [];
        
        // Simple regex to extract route definitions
        // This is a basic implementation - you'd want to improve this
        $patterns = [
            '/\$router->get\([\'"]([^\'"]+)[\'"],\s*[\'"]([^\'"]+)[\'"]/',
            '/\$router->post\([\'"]([^\'"]+)[\'"],\s*[\'"]([^\'"]+)[\'"]/',
            '/\$router->put\([\'"]([^\'"]+)[\'"],\s*[\'"]([^\'"]+)[\'"]/',
            '/\$router->delete\([\'"]([^\'"]+)[\'"],\s*[\'"]([^\'"]+)[\'"]/',
        ];
        
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        
        foreach ($patterns as $index => $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $routes[] = [
                        'method' => $methods[$index],
                        'path' => $match[1],
                        'handler' => $match[2]
                    ];
                }
            }
        }

        return $routes;
    }
}
