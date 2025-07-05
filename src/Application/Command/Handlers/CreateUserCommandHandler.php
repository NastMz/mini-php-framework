<?php
declare(strict_types=1);

namespace App\Application\Command\Handlers;

use App\Application\Command\CommandHandlerInterface;
use App\Application\Command\CommandInterface;
use App\Application\Command\CreateUserCommand;
use App\Infrastructure\Event\DomainEventDispatcher;
use App\Domain\Event\UserRegisteredEvent;
use App\Infrastructure\Logging\LoggerInterface;

/**
 * CreateUserCommandHandler
 *
 * Handles the creation of new users in the system.
 */
class CreateUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private DomainEventDispatcher $eventDispatcher
    ) {}

    public function handle(CommandInterface $command): mixed
    {
        if (!$command instanceof CreateUserCommand) {
            throw new \InvalidArgumentException('Expected CreateUserCommand');
        }

        // Simulate user creation logic
        $userId = uniqid('user_', true);
        
        // Log the creation
        $this->logger->info('Creating new user', [
            'user_id' => $userId,
            'email' => $command->email,
            'name' => $command->name
        ]);

        // Simulate saving to database
        // In real implementation, you would use a repository here
        
        // Dispatch domain event
        $event = new UserRegisteredEvent($userId, $command->email, $command->name);
        $this->eventDispatcher->dispatch($event);

        return [
            'user_id' => $userId,
            'email' => $command->email,
            'name' => $command->name,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}
