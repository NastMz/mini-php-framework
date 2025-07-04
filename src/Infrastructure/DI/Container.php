<?php
declare(strict_types=1);

namespace App\Infrastructure\DI;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionParameter;

/**
 * Simple DI container with auto-wiring and service definitions.
 */
class Container implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $settings;

    /** @var array<string, callable(Container): mixed> */
    private array $definitions;

    /** @var array<string, mixed> */
    private array $instances = [];

    /**
     * @param array<string,mixed>                         $settings
     * @param array<string, callable(Container): mixed>   $definitions
     */
    private function __construct(array $settings, array $definitions = [])
    {
        $this->settings    = $settings;
        $this->definitions = $definitions;

        // make settings injectable via 'settings' key
        $this->definitions['settings'] = fn(Container $c): array => $this->settings;
    }

    /**
     * Build the container.
     *
     * @param array<string,mixed>                       $settings
     * @param array<string, callable(Container): mixed> $definitions
     */
    public static function build(array $settings, array $definitions = []): ContainerInterface
    {
        $container = new self($settings, $definitions);
        
        // Register the container itself as ContainerInterface
        $container->definitions[ContainerInterface::class] = fn(Container $c): ContainerInterface => $c;
        
        return $container;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $id): mixed
    {
        // Return shared instance if available
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        // Create via definition
        if (isset($this->definitions[$id])) {
            $object = ($this->definitions[$id])($this);
            return $this->instances[$id] = $object;
        }

        // Autowire classes
        if (class_exists($id)) {
            $object = $this->autowire($id);
            return $this->instances[$id] = $object;
        }

        throw new NotFoundException("No entry or class found for '{$id}' in container");
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        if (isset($this->definitions[$id]) || array_key_exists($id, $this->instances)) {
            return true;
        }
        if ($id === 'settings') {
            return true;
        }
        return class_exists($id);
    }

    /**
     * Auto-wire a class by resolving constructor parameters.
     *
     * @throws ContainerException on resolution failure
     */
    private function autowire(string $class): object
    {
        $refClass = new ReflectionClass($class);

        if (! $refClass->isInstantiable()) {
            throw new ContainerException("Class '{$class}' is not instantiable");
        }

        $constructor = $refClass->getConstructor();
        if ($constructor === null) {
            return new $class;
        }

        $args = [];
        /** @var ReflectionParameter $param */
        foreach ($constructor->getParameters() as $param) {
            $args[] = $this->resolveParameter($param);
        }

        return $refClass->newInstanceArgs($args);
    }

    /**
     * Resolve a single constructor parameter.
     *
     * @throws ContainerException on failure
     */
    private function resolveParameter(ReflectionParameter $param): mixed
    {
        $type = $param->getType();
        // Class-typed dependency
        if ($type !== null && ! $type->isBuiltin()) {
            /** @var string $depClass */
            $depClass = $type->getName();
            return $this->get($depClass);
        }

        // Named "settings" → inject settings array
        if ($param->getName() === 'settings') {
            return $this->settings;
        }

        // Default value fallback
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        throw new ContainerException(
            "Cannot resolve parameter '\${$param->getName()}' in '{$param->getDeclaringClass()->getName()}'"
        );
    }
}

/**
 * Marker exception for container errors.
 */
class ContainerException extends \Exception implements ContainerExceptionInterface {}

/**
 * Thrown when a dependency is not found in the container.
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface {}
