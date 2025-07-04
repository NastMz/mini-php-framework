<?php
declare(strict_types=1);

namespace App\Infrastructure\Routing;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;
use Psr\Container\ContainerInterface;

/**
 * Class Router
 *
 * Manages routing of HTTP requests to appropriate handlers based on method and path.
 * Supports dynamic path parameters and dependency injection for handlers.
 */
class Router
{
    private ContainerInterface $container;
    /** @var Route[] */
    private array $routes = [];

    /**
     * Router constructor.
     *
     * @param ContainerInterface $container Dependency injection container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Adds a new route to the router.
     *
     * @param Route $route The route to add
     * @throws \InvalidArgumentException If the route already exists
     */
    public function add(Route $route): void
    {
        $key = $route->method->value . ' ' . $route->pathPattern;
        if (isset($this->routes[$key])) {
            throw new \InvalidArgumentException("Duplicate route: {$key}");
        }
        $this->routes[$key] = $route;
    }

    /**
     * Gets all registered routes.
     *
     * @return Route[] Array of all registered routes
     */
    public function getRoutes(): array
    {
        return array_values($this->routes);
    }

    /**
     * Dispatches the request to the first matching route.
     *
     * @return ResponseInterface
     */
    public function dispatch(
        string $rawMethod,
        string $rawPath,
        RequestInterface  $req,
        ResponseInterface $res
    ): ResponseInterface {
        $method = HttpMethod::tryFrom($rawMethod)
            ?? throw new \RuntimeException("Unsupported HTTP method: {$rawMethod}");

        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }

            // Build regex from pathPattern (e.g. /user/{id})
            $regex = '#^' .
                preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $route->pathPattern) .
                '$#';

            if (!preg_match($regex, $rawPath, $matches)) {
                continue;
            }

            // Extract only named subpatterns
            $params = array_filter(
                $matches,
                fn($key) => is_string($key),
                ARRAY_FILTER_USE_KEY
            );

            // Resolve handler to a callable
            $handler = $route->handler;
            if (is_string($handler) && str_contains($handler, '::')) {
                [$class, $methodName] = explode('::', $handler, 2);
                $instance = $this->container->get($class);
                $handler = [$instance, $methodName];
            }

            // Call handler(Request, Response, ...$params)
            $result = call_user_func_array(
                $handler,
                array_merge([$req, $res], array_values($params))
            );

            return $result instanceof ResponseInterface
                ? $result
                : $res;
        }

        throw new \RuntimeException("No route found for {$rawMethod} {$rawPath}");
    }
}
