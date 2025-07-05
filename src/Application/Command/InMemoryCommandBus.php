<?php
declare(strict_types=1);

namespace App\Application\Command;

use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * InMemoryCommandBus
 *
 * A simple in-memory command bus implementation that uses a container to resolve command handlers.
 * It maps command classes to their respective handler classes and dispatches commands accordingly.
 */
class InMemoryCommandBus implements CommandBusInterface
{
    /**
     * @param ContainerInterface            $container
     * @param array<class-string, class-string> $map
     *        Mapping Command FQCN → Handler FQCN
     */
    public function __construct(
        private ContainerInterface $container,
        private array              $map
    ) {}

    /**
     * Dispatches a command to its handler.
     *
     * @param CommandInterface $command The command to dispatch
     * @return mixed The result of the command handler
     * @throws RuntimeException If no handler is registered for the command
     */
    public function dispatch(CommandInterface $command): mixed
    {
        $cmdClass = get_class($command);
        if (! isset($this->map[$cmdClass])) {
            throw new RuntimeException("No handler registered for command {$cmdClass}");
        }

        $handlerClass = $this->map[$cmdClass];
        $handler = $this->container->get($handlerClass);
        if (! $handler instanceof CommandHandlerInterface) {
            throw new RuntimeException("Handler {$handlerClass} must implement CommandHandlerInterface");
        }

        return $handler->handle($command);
    }
}
