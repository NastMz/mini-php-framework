<?php
declare(strict_types=1);

namespace Tests\Unit\Application\Query;

use Tests\TestCase;
use App\Application\Query\InMemoryQueryBus;
use App\Application\Query\QueryInterface;
use App\Application\Query\QueryHandlerInterface;
use App\Infrastructure\DI\Container;

class InMemoryQueryBusTest extends TestCase
{
    private InMemoryQueryBus $queryBus;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->container = Container::build([], []);
    }

    public function testDispatchCallsCorrectHandler(): void
    {
        $query = $this->createMock(QueryInterface::class);
        $handler = $this->createMock(QueryHandlerInterface::class);
        
        $expectedResult = ['data' => 'test result'];
        
        $handler->expects($this->once())
            ->method('handle')
            ->with($query)
            ->willReturn($expectedResult);

        $this->container->set('TestQueryHandler', $handler);

        $mappings = [get_class($query) => 'TestQueryHandler'];
        $this->queryBus = new InMemoryQueryBus($this->container, $mappings);

        $result = $this->queryBus->dispatch($query);

        $this->assertEquals($expectedResult, $result);
    }

    public function testDispatchThrowsExceptionForUnknownQuery(): void
    {
        $query = $this->createMock(QueryInterface::class);
        
        $this->queryBus = new InMemoryQueryBus($this->container, []);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No handler registered for query');

        $this->queryBus->dispatch($query);
    }

    public function testDispatchThrowsExceptionWhenHandlerNotInContainer(): void
    {
        $query = $this->createMock(QueryInterface::class);
        
        $mappings = [get_class($query) => 'NonExistentHandler'];
        $this->queryBus = new InMemoryQueryBus($this->container, $mappings);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Handler not found in container');

        $this->queryBus->dispatch($query);
    }

    public function testDispatchReturnsReadOnlyData(): void
    {
        $query = $this->createMock(QueryInterface::class);
        $handler = $this->createMock(QueryHandlerInterface::class);
        
        // Queries should return data without side effects
        $userData = [
            'id' => 123,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        
        $handler->expects($this->once())
            ->method('handle')
            ->with($query)
            ->willReturn($userData);

        $this->container->set('UserQueryHandler', $handler);

        $mappings = [get_class($query) => 'UserQueryHandler'];
        $this->queryBus = new InMemoryQueryBus($this->container, $mappings);

        $result = $this->queryBus->dispatch($query);

        $this->assertEquals($userData, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
    }
}
