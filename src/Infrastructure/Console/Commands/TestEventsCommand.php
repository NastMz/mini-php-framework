<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;
use App\Infrastructure\Event\DomainEventDispatcher;
use App\Domain\Event\UserRegisteredEvent;
use App\Domain\Event\FileUploadedEvent;

/**
 * TestEventsCommand
 *
 * Command to test the domain event system.
 */
class TestEventsCommand extends Command
{
    public function __construct(
        private DomainEventDispatcher $eventDispatcher
    ) {}

    public function getName(): string
    {
        return 'events:test';
    }

    public function getDescription(): string
    {
        return 'Test the domain event system';
    }

    public function configure(): void
    {
        // No configuration needed for this command
    }

    public function execute(array $arguments, array $options): int
    {
        echo "🧪 Testing Domain Event System...\n\n";

        // Test UserRegisteredEvent
        echo "📧 Dispatching UserRegisteredEvent...\n";
        $userEvent = new UserRegisteredEvent('user-123', 'john@example.com', 'John Doe');
        $this->eventDispatcher->dispatch($userEvent);
        echo "✅ UserRegisteredEvent dispatched successfully\n\n";

        // Test FileUploadedEvent
        echo "📁 Dispatching FileUploadedEvent...\n";
        $fileEvent = new FileUploadedEvent('test.jpg', 'uploads/test.jpg', 1024, 'image/jpeg');
        $this->eventDispatcher->dispatch($fileEvent);
        echo "✅ FileUploadedEvent dispatched successfully\n\n";

        echo "🎉 Domain Event System test completed!\n";
        echo "📝 Check the log files to see the events being logged.\n";

        return 0;
    }
}
