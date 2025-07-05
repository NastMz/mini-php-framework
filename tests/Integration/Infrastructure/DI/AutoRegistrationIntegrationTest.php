<?php
declare(strict_types=1);

namespace Tests\Integration\Infrastructure\DI;

use Tests\TestCase;
use App\Infrastructure\DI\AutoRegistration;

class AutoRegistrationIntegrationTest extends TestCase
{
    private string $testBasePath;
    private AutoRegistration $autoRegistration;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->testBasePath = dirname(__DIR__, 4);
        $this->autoRegistration = new AutoRegistration('App', $this->testBasePath);
    }

    public function testAutoRegistrationScansAndFindsServices(): void
    {
        $result = $this->autoRegistration->scan();

        // Verify structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('services', $result);
        $this->assertArrayHasKey('cqrs', $result);
        $this->assertArrayHasKey('debug', $result);

        // Should find the FileUploadService
        $services = array_keys($result['services']);
        $fileUploadServiceFound = false;
        foreach ($services as $service) {
            if (str_contains($service, 'FileUploadService')) {
                $fileUploadServiceFound = true;
                break;
            }
        }
        $this->assertTrue($fileUploadServiceFound, 'FileUploadService should be auto-registered');

        // Should have some CQRS mappings
        $this->assertIsArray($result['cqrs']['commands']);
        $this->assertIsArray($result['cqrs']['queries']);

        // Should provide debug information
        $this->assertNotEmpty($result['debug']);
    }

    public function testAutoRegistrationPerformance(): void
    {
        $startTime = microtime(true);
        $this->autoRegistration->scan();
        $endTime = microtime(true);
        
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Should complete in reasonable time (less than 100ms)
        $this->assertLessThan(100, $executionTime, 'Auto-registration should be fast');
    }
}
