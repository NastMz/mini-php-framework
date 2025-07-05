<?php
declare(strict_types=1);

namespace App\Infrastructure\DI;

use ReflectionClass;
use ReflectionException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use App\Infrastructure\DI\Exception\DependencyResolutionException;

/**
 * AutoRegistration
 *
 * Enhanced auto-registration system with better reflection handling and debugging.
 */
class AutoRegistration
{
    private array $registrations = [];
    private array $cqrsMappings = ['commands' => [], 'queries' => []];
    private array $debug = [];

    public function __construct(
        private string $baseNamespace,
        private string $basePath
    ) {
    }

    /**
     * Scan and register all services automatically.
     */
    public function scan(): array
    {
        $this->debug = [];
        
        // Register services
        $this->scanServices();
        
        // Register controllers
        $this->scanControllers();
        
        // Register command handlers
        $this->scanCommandHandlers();
        
        // Register query handlers
        $this->scanQueryHandlers();
        
        // Register event subscribers
        $this->scanEventSubscribers();
        
        // Build CQRS mappings
        $this->buildCqrsMappings();
        
        return [
            'services' => $this->registrations,
            'cqrs' => $this->cqrsMappings,
            'debug' => $this->debug
        ];
    }

    /**
     * Scan services in Infrastructure/Service directory.
     */
    private function scanServices(): void
    {
        $this->scanDirectory(
            'src/Infrastructure/Service',
            '*Service.php',
            'Service',
            null
        );
    }

    /**
     * Scan controllers in Presentation/Controller directory.
     */
    private function scanControllers(): void
    {
        $this->scanDirectory(
            'src/Presentation/Controller',
            '*Controller.php',
            'Controller',
            null
        );
    }

    /**
     * Scan command handlers.
     */
    private function scanCommandHandlers(): void
    {
        $this->scanDirectory(
            'src/Application/Command/Handlers',
            '*CommandHandler.php',
            'CommandHandler',
            'App\Application\Command\CommandHandlerInterface'
        );
    }

    /**
     * Scan query handlers.
     */
    private function scanQueryHandlers(): void
    {
        $this->scanDirectory(
            'src/Application/Query/Handlers',
            '*QueryHandler.php',
            'QueryHandler',
            'App\Application\Query\QueryHandlerInterface'
        );
    }

    /**
     * Scan event subscribers.
     */
    private function scanEventSubscribers(): void
    {
        $this->scanDirectory(
            'src/Infrastructure/Event',
            '*EventSubscriber.php',
            'EventSubscriber',
            'App\Domain\Event\DomainEventSubscriberInterface'
        );
    }

    /**
     * Generic directory scanner.
     */
    private function scanDirectory(string $directory, string $pattern, string $type, ?string $interface): void
    {
        $fullPath = $this->basePath . '/' . $directory;
        $this->debug[] = "Scanning directory: $fullPath";
        
        if (!is_dir($fullPath)) {
            $this->debug[] = "Directory not found: $fullPath";
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $fileName = $file->getBasename('.php');
                
                if ($this->matchesPattern($fileName, $pattern)) {
                    $className = $this->buildClassName($directory, $fileName);
                    
                    if ($this->isValidClass($className, $interface)) {
                        $this->registrations[$className] = $this->createFactory($className);
                        $this->debug[] = "✅ Registered $type: $className";
                    } else {
                        $this->debug[] = "❌ Invalid class: $className";
                    }
                }
            }
        }
    }

    /**
     * Check if filename matches pattern.
     */
    private function matchesPattern(string $fileName, string $pattern): bool
    {
        $regex = '/^' . str_replace('*', '.*', str_replace('.php', '', $pattern)) . '$/';
        return preg_match($regex, $fileName) === 1;
    }

    /**
     * Build class name from directory and filename.
     */
    private function buildClassName(string $directory, string $fileName): string
    {
        // Convert directory path to namespace
        $namespacePart = str_replace('/', '\\', str_replace('src/', '', $directory));
        return $this->baseNamespace . '\\' . $namespacePart . '\\' . $fileName;
    }

    /**
     * Validate if class exists and implements required interface.
     */
    private function isValidClass(string $className, ?string $interface): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($className);
            
            // Skip abstract classes and interfaces
            if ($reflection->isAbstract() || $reflection->isInterface()) {
                return false;
            }

            // Check interface requirement
            if ($interface && !$reflection->implementsInterface($interface)) {
                return false;
            }

            return true;
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Create factory for automatic dependency injection.
     */
    private function createFactory(string $className): \Closure
    {
        return function (Container $container) use ($className) {
            return $this->instantiateClass($className, $container);
        };
    }

    /**
     * Instantiate class with automatic dependency injection.
     */
    private function instantiateClass(string $className, Container $container): object
    {
        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $className();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[] = $this->resolveDependency($parameter, $container, $className);
        }

        return new $className(...$dependencies);
    }

    /**
     * Resolve single dependency.
     */
    private function resolveDependency(\ReflectionParameter $parameter, Container $container, string $className): mixed
    {
        $type = $parameter->getType();
        
        if ($type && !$type->isBuiltin()) {
            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : null;
            
            if ($typeName && $container->has($typeName)) {
                return $container->get($typeName);
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw DependencyResolutionException::cannotResolveType(
            $type instanceof \ReflectionNamedType ? $type->getName() : 'unknown',
            $className
        );
    }

    /**
     * Build CQRS mappings automatically.
     */
    private function buildCqrsMappings(): void
    {
        $this->buildCommandMappings();
        $this->buildQueryMappings();
    }

    /**
     * Build command mappings.
     */
    private function buildCommandMappings(): void
    {
        $commandsDir = $this->basePath . '/src/Application/Command';
        $handlersDir = $this->basePath . '/src/Application/Command/Handlers';
        
        $this->cqrsMappings['commands'] = $this->buildCqrsMapping($commandsDir, $handlersDir, 'Command');
    }

    /**
     * Build query mappings.
     */
    private function buildQueryMappings(): void
    {
        $queriesDir = $this->basePath . '/src/Application/Query';
        $handlersDir = $this->basePath . '/src/Application/Query/Handlers';
        
        $this->cqrsMappings['queries'] = $this->buildCqrsMapping($queriesDir, $handlersDir, 'Query');
    }

    /**
     * Build CQRS mapping for commands or queries.
     */
    private function buildCqrsMapping(string $requestDir, string $handlerDir, string $type): array
    {
        $mappings = [];
        
        if (!is_dir($requestDir) || !is_dir($handlerDir)) {
            return $mappings;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($requestDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $fileName = $file->getBasename('.php');
                
                if (str_ends_with($fileName, $type) && !str_contains($fileName, 'Handler')) {
                    $requestClass = $this->baseNamespace . '\\Application\\' . $type . '\\' . $fileName;
                    $handlerClass = $this->baseNamespace . '\\Application\\' . $type . '\\Handlers\\' . $fileName . 'Handler';
                    
                    if (class_exists($requestClass) && class_exists($handlerClass)) {
                        $mappings[$requestClass] = $handlerClass;
                        $this->debug[] = "✅ CQRS mapping: $requestClass → $handlerClass";
                    }
                }
            }
        }

        return $mappings;
    }

    /**
     * Get debug information.
     */
    public function getDebugInfo(): array
    {
        return $this->debug;
    }
}
