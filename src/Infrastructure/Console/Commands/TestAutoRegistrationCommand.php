<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;
use App\Infrastructure\DI\AutoRegistration;
use App\Infrastructure\Service\NotificationService;
use App\Application\Command\CommandBusInterface;
use App\Application\Query\QueryBusInterface;
use App\Application\Command\SendNotificationCommand;
use App\Application\Query\GetNotificationStatsQuery;

/**
 * TestAutoRegistrationCommand
 *
 * Command to test the auto-registered services.
 */
class TestAutoRegistrationCommand extends Command
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private NotificationService $notificationService
    ) {
    }

    public function getName(): string
    {
        return 'di:show';
    }

    public function getDescription(): string
    {
        return 'Show auto-registered services and test functionality';
    }

    public function configure(): void
    {
        // No configuration needed for this command
    }

    public function execute(array $arguments, array $options): int
    {
        echo "🧪 Testing Auto-Registered Services & CQRS\n\n";

        // First, show what was auto-registered
        $autoRegistration = new AutoRegistration('App', dirname(__DIR__, 4));
        $result = $autoRegistration->scan();
        
        echo "📦 Currently Auto-Registered Services:\n";
        foreach ($result['services'] as $className => $factory) {
            $shortName = $this->getShortClassName($className);
            echo "   ✅ {$shortName}\n";
        }

        echo "\n🚌 CQRS Mappings:\n";
        foreach ($result['cqrs']['commands'] as $command => $handler) {
            echo "   📝 {$this->getShortClassName($command)} → {$this->getShortClassName($handler)}\n";
        }
        foreach ($result['cqrs']['queries'] as $query => $handler) {
            echo "   📊 {$this->getShortClassName($query)} → {$this->getShortClassName($handler)}\n";
        }

        echo "\n🔧 Testing Auto-Registered NotificationService:\n";
        try {
            $notification = $this->notificationService->sendNotification(
                'test@example.com',
                'Hello from auto-registered service!'
            );
            echo "   ✅ Notification sent: {$notification['id']}\n";
        } catch (\Exception $e) {
            echo "   ❌ Error: {$e->getMessage()}\n";
        }

        echo "\n📝 Testing Auto-Registered SendNotificationCommand:\n";
        try {
            $command = new SendNotificationCommand(
                'admin@example.com',
                'Testing CQRS auto-registration',
                'email'
            );
            $result = $this->commandBus->dispatch($command);
            echo "   ✅ Command handled: {$result['id']}\n";
        } catch (\Exception $e) {
            echo "   ❌ Error: {$e->getMessage()}\n";
        }

        echo "\n📊 Testing Auto-Registered GetNotificationStatsQuery:\n";
        try {
            $query = new GetNotificationStatsQuery(
                date('Y-m-01'),
                date('Y-m-d')
            );
            $stats = $this->queryBus->dispatch($query);
            echo "   ✅ Query handled: {$stats['total_sent']} total notifications\n";
            echo "   📈 Delivery rate: {$stats['delivery_rate']}%\n";
        } catch (\Exception $e) {
            echo "   ❌ Error: {$e->getMessage()}\n";
        }

        echo "\n🎉 Auto-Registration Success!\n";
        echo "✨ No manual configuration needed!\n";
        echo "🚀 Just create your classes and they're automatically available!\n";
        
        return 0;
    }

    private function getShortClassName(string $className): string
    {
        return substr($className, strrpos($className, '\\') + 1);
    }
}
