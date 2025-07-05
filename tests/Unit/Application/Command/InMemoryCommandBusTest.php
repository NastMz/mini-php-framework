<?php
declare(strict_types=1);

namespace Tests\Unit\Application\Command;

use Tests\TestCase;
use App\Application\Command\InMemoryCommandBus;
use App\Application\Command\CommandInterface;
use App\Application\Command\CommandHandlerInterface;
use App\Infrastructure\DI\Container;

class InMemoryCommandBusTest extends TestCase
{
    private InMemoryCommandBus $commandBus;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->container = Container::build([], []);
    }

    public function testDispatchCallsCorrectHandler(): void
    {
        // Create mock command and handler
        $command = $this->createMock(CommandInterface::class);
        $handler = $this->createMock(CommandHandlerInterface::class);
        
        // Expect the handler to be called with the command
        $handler->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willReturn('test result');

        // Add handler to container
        $this->container->set('TestHandler', $handler);

        // Create command bus with mapping
        $mappings = [get_class($command) => 'TestHandler'];
        $this->commandBus = new InMemoryCommandBus($this->container, $mappings);

        $result = $this->commandBus->dispatch($command);

        $this->assertEquals('test result', $result);
    }

    public function testDispatchThrowsExceptionForUnknownCommand(): void
    {
        $command = $this->createMock(CommandInterface::class);
        
        // Create command bus with empty mappings
        $this->commandBus = new InMemoryCommandBus($this->container, []);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No handler registered for command');

        $this->commandBus->dispatch($command);
    }

    public function testDispatchThrowsExceptionWhenHandlerNotInContainer(): void
    {
        $command = $this->createMock(CommandInterface::class);
        
        // Create command bus with mapping to non-existent handler
        $mappings = [get_class($command) => 'NonExistentHandler'];
        $this->commandBus = new InMemoryCommandBus($this->container, $mappings);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Handler not found in container');

        $this->commandBus->dispatch($command);
    }

    public function testDispatchWorksWithMultipleCommands(): void
    {
        $command1 = $this->createMock(CommandInterface::class);
        $command2 = $this->createMock(CommandInterface::class);
        
        $handler1 = $this->createMock(CommandHandlerInterface::class);
        $handler2 = $this->createMock(CommandHandlerInterface::class);

        $handler1->expects($this->once())
            ->method('handle')
            ->with($command1)
            ->willReturn('result1');

        $handler2->expects($this->once())
            ->method('handle')
            ->with($command2)
            ->willReturn('result2');

        $this->container->set('Handler1', $handler1);
        $this->container->set('Handler2', $handler2);

        $mappings = [
            get_class($command1) => 'Handler1',
            get_class($command2) => 'Handler2'
        ];
        
        $this->commandBus = new InMemoryCommandBus($this->container, $mappings);

        $result1 = $this->commandBus->dispatch($command1);
        $result2 = $this->commandBus->dispatch($command2);

        $this->assertEquals('result1', $result1);
        $this->assertEquals('result2', $result2);
    }
}
