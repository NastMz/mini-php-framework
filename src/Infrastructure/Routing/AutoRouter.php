<?php
declare(strict_types=1);

namespace App\Infrastructure\Routing;

use App\Infrastructure\Routing\Attributes\Route as RouteAttribute;
use App\Infrastructure\Routing\Attributes\Controller as ControllerAttribute;
use ReflectionClass;
use ReflectionMethod;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * AutoRouter
 *
 * Automatically discovers and registers routes from controller classes using PHP 8 attributes.
 */
class AutoRouter
{
    private array $routes = [];
    private array $debug = [];

    public function __construct(
        private string $baseNamespace,
        private string $controllersPath
    ) {}

    /**
     * Scan controllers and auto-register routes
     */
    public function discover(): array
    {
        $this->debug = [];
        $this->routes = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->controllersPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->processControllerFile($file);
            }
        }

        return [
            'routes' => $this->routes,
            'debug' => $this->debug
        ];
    }

    /**
     * Process a single controller file
     */
    private function processControllerFile(\SplFileInfo $file): void
    {
        $relativePath = str_replace($this->controllersPath, '', $file->getPathname());
        $relativePath = str_replace(['/', '\\'], '\\', $relativePath);
        $relativePath = trim($relativePath, '\\');
        $className = $this->baseNamespace . '\\Presentation\\Controller\\' . str_replace('.php', '', $relativePath);

        if (!class_exists($className)) {
            return;
        }

        $reflection = new ReflectionClass($className);
        
        // Skip abstract classes
        if ($reflection->isAbstract()) {
            return;
        }

        // Get controller-level attributes
        $controllerAttributes = $reflection->getAttributes(ControllerAttribute::class);
        $controllerPrefix = '';
        $controllerMiddleware = [];

        if (!empty($controllerAttributes)) {
            $controllerAttr = $controllerAttributes[0]->newInstance();
            $controllerPrefix = $controllerAttr->prefix;
            $controllerMiddleware = $controllerAttr->middleware;
        }

        // Process each method
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $this->processControllerMethod($method, $className, $controllerPrefix, $controllerMiddleware);
        }
    }

    /**
     * Process a controller method for route attributes
     */
    private function processControllerMethod(
        ReflectionMethod $method,
        string $className,
        string $controllerPrefix,
        array $controllerMiddleware
    ): void {
        $routeAttributes = $method->getAttributes(RouteAttribute::class);
        
        if (empty($routeAttributes)) {
            return;
        }

        foreach ($routeAttributes as $routeAttribute) {
            $route = $routeAttribute->newInstance();
            
            // Build full path
            $fullPath = $controllerPrefix . $route->path;
            $fullPath = '/' . trim($fullPath, '/');
            
            // Merge middleware
            $middleware = array_merge($controllerMiddleware, $route->middleware);
            
            // Create route data
            $routeData = [
                'method' => $route->method,
                'path' => $fullPath,
                'handler' => $className . '::' . $method->getName(),
                'name' => $route->name,
                'middleware' => $middleware,
                'where' => $route->where
            ];

            $this->routes[] = $routeData;
            $this->debug[] = "✅ Auto-route: {$route->method->value} {$fullPath} → {$className}::{$method->getName()}";
        }
    }

    /**
     * Register discovered routes with the router
     */
    public function registerRoutes(Router $router): void
    {
        foreach ($this->routes as $routeData) {
            $route = new Route(
                $routeData['method'],
                $routeData['path'],
                $routeData['handler']
            );
            
            $router->add($route);
        }
    }

    /**
     * Get debug information
     */
    public function getDebugInfo(): array
    {
        return $this->debug;
    }
}
