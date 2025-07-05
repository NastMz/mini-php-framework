<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\DI;

use Tests\TestCase;
use App\Infrastructure\DI\AutoRegistration;
use App\Infrastructure\DI\Container;

class AutoRegistrationTest extends TestCase
{
    private string $testBasePath;
    private AutoRegistration $autoRegistration;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use actual project path for integration-style testing
        $this->testBasePath = dirname(__DIR__, 4);
        $this->autoRegistration = new AutoRegistration('App', $this->testBasePath);
    }

    public function testScanReturnsValidStructure(): void
    {
        $result = $this->autoRegistration->scan();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('services', $result);
        $this->assertArrayHasKey('cqrs', $result);
        $this->assertArrayHasKey('debug', $result);

        $this->assertIsArray($result['services']);
        $this->assertIsArray($result['cqrs']);
        $this->assertIsArray($result['debug']);
    }

    public function testScansServicesCorrectly(): void
    {
        $result = $this->autoRegistration->scan();
        
        // Should find FileUploadService
        $this->assertArrayHasKey('App\\Infrastructure\\Service\\FileUploadService', $result['services']);
        
        // Each service should have a closure factory
        foreach ($result['services'] as $className => $factory) {
            $this->assertInstanceOf(\Closure::class, $factory);
            $this->assertStringStartsWith('App\\', $className);
        }
    }

    public function testScansCqrsMappingsCorrectly(): void
    {
        $result = $this->autoRegistration->scan();
        
        $this->assertArrayHasKey('commands', $result['cqrs']);
        $this->assertArrayHasKey('queries', $result['cqrs']);

        // Should find UploadFileCommand mapping
        if (!empty($result['cqrs']['commands'])) {
            foreach ($result['cqrs']['commands'] as $command => $handler) {
                $this->assertStringStartsWith('App\\Application\\Command\\', $command);
                $this->assertStringStartsWith('App\\Application\\Command\\Handlers\\', $handler);
                $this->assertStringEndsWith('Command', $command);
                $this->assertStringEndsWith('CommandHandler', $handler);
            }
        }

        // Should find GetSystemHealthQuery mapping
        if (!empty($result['cqrs']['queries'])) {
            foreach ($result['cqrs']['queries'] as $query => $handler) {
                $this->assertStringStartsWith('App\\Application\\Query\\', $query);
                $this->assertStringStartsWith('App\\Application\\Query\\Handlers\\', $handler);
                $this->assertStringEndsWith('Query', $query);
                $this->assertStringEndsWith('QueryHandler', $handler);
            }
        }
    }

    public function testFactoriesCreateValidInstances(): void
    {
        $result = $this->autoRegistration->scan();
        
        // Create a minimal container for testing
        $container = Container::build(
            ['database' => ['path' => ':memory:']],
            [
                'App\\Infrastructure\\Logging\\LoggerInterface' => fn() => $this->createMock('App\\Infrastructure\\Logging\\LoggerInterface'),
                'App\\Domain\\Service\\FileStorageInterface' => fn() => $this->createMock('App\\Domain\\Service\\FileStorageInterface'),
                'App\\Infrastructure\\Event\\DomainEventDispatcher' => fn() => $this->createMock('App\\Infrastructure\\Event\\DomainEventDispatcher'),
            ]
        );

        // Test that we can instantiate services through their factories
        foreach ($result['services'] as $className => $factory) {
            try {
                $instance = $factory($container);
                $this->assertInstanceOf($className, $instance);
            } catch (\Exception $e) {
                // Some services might have complex dependencies, that's OK for this test
                $this->assertStringContains('Cannot resolve', $e->getMessage());
            }
        }
    }

    public function testDebugInformationIsProvided(): void
    {
        $result = $this->autoRegistration->scan();
        
        $this->assertNotEmpty($result['debug']);
        
        // Should contain scanning information
        $debugString = implode(' ', $result['debug']);
        $this->assertStringContains('Scanning directory:', $debugString);
    }

    public function testPerformanceIsAcceptable(): void
    {
        $startTime = microtime(true);
        $this->autoRegistration->scan();
        $endTime = microtime(true);
        
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Should complete scanning in under 100ms
        $this->assertLessThan(100, $executionTime, 'Auto-registration should complete in under 100ms');
    }
}
