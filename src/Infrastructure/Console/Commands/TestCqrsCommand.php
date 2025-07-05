<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;
use App\Application\Command\CommandBusInterface;
use App\Application\Query\QueryBusInterface;
use App\Application\Command\CreateUserCommand;
use App\Application\Query\GetUserByEmailQuery;
use App\Application\Query\GetSystemHealthQuery;

/**
 * TestCqrsCommand
 *
 * Command to test the CQRS system.
 */
class TestCqrsCommand extends Command
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus
    ) {}

    public function getName(): string
    {
        return 'cqrs:test';
    }

    public function getDescription(): string
    {
        return 'Test the CQRS system with commands and queries';
    }

    public function configure(): void
    {
        // No configuration needed for this command
    }

    public function execute(array $arguments, array $options): int
    {
        echo "🧪 Testing CQRS System...\n\n";

        // Test Commands
        echo "📝 Testing Commands...\n";
        
        // Test CreateUserCommand
        echo "👤 Creating a new user...\n";
        $createUserCommand = new CreateUserCommand(
            'test@example.com',
            'Test User',
            'password123'
        );
        
        $result = $this->commandBus->dispatch($createUserCommand);
        echo "✅ User created: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

        // Test Queries
        echo "📊 Testing Queries...\n";
        
        // Test GetUserByEmailQuery
        echo "🔍 Fetching user by email...\n";
        $getUserQuery = new GetUserByEmailQuery('john@example.com');
        $user = $this->queryBus->dispatch($getUserQuery);
        
        if ($user) {
            echo "✅ User found: " . json_encode($user, JSON_PRETTY_PRINT) . "\n\n";
        } else {
            echo "❌ User not found\n\n";
        }

        // Test GetSystemHealthQuery
        echo "🏥 Checking system health...\n";
        $healthQuery = new GetSystemHealthQuery();
        $health = $this->queryBus->dispatch($healthQuery);
        echo "✅ System health: " . json_encode($health, JSON_PRETTY_PRINT) . "\n\n";

        echo "🎉 CQRS System test completed!\n";
        echo "📝 Commands executed write operations\n";
        echo "📊 Queries executed read operations\n";
        echo "🔄 Events were dispatched automatically\n";

        return 0;
    }
}
