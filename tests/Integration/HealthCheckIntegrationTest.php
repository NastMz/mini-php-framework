<?php
declare(strict_types=1);

namespace Tests\Integration;

use Tests\TestCase;
use App\Infrastructure\Health\HealthCheckService;
use App\Application\Query\GetSystemHealthQuery;
use App\Application\Query\Handlers\GetSystemHealthQueryHandler;

class HealthCheckIntegrationTest extends TestCase
{
    public function testHealthCheckServiceWorks(): void
    {
        // Create in-memory SQLite database for testing
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $healthService = new HealthCheckService($pdo);
        $result = $healthService->check();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('checks', $result);
        $this->assertArrayHasKey('timestamp', $result);
        
        $this->assertEquals('healthy', $result['status']);
        $this->assertArrayHasKey('database', $result['checks']);
        $this->assertTrue($result['checks']['database']);
    }

    public function testHealthCheckQueryHandler(): void
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $healthService = new HealthCheckService($pdo);
        $handler = new GetSystemHealthQueryHandler($healthService);
        $query = new GetSystemHealthQuery();

        $result = $handler->handle($query);

        $this->assertIsArray($result);
        $this->assertEquals('healthy', $result['status']);
        $this->assertArrayHasKey('checks', $result);
        $this->assertArrayHasKey('database', $result['checks']);
        $this->assertTrue($result['checks']['database']);
    }

    public function testHealthCheckWithDatabaseError(): void
    {
        // Create a PDO that will fail
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('query')
            ->willThrowException(new \PDOException('Connection failed'));

        $healthService = new HealthCheckService($pdo);
        $result = $healthService->check();

        $this->assertEquals('unhealthy', $result['status']);
        $this->assertFalse($result['checks']['database']);
    }
}
