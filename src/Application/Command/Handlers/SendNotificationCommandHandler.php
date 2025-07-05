<?php
declare(strict_types=1);

namespace App\Application\Command\Handlers;

use App\Application\Command\CommandHandlerInterface;
use App\Application\Command\CommandInterface;
use App\Application\Command\SendNotificationCommand;
use App\Infrastructure\Service\NotificationService;
use App\Infrastructure\Logging\LoggerInterface;

/**
 * SendNotificationCommandHandler
 *
 * Handler for SendNotificationCommand.
 * This handler will be automatically registered and mapped.
 */
class SendNotificationCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private NotificationService $notificationService,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Handle the send notification command.
     */
    public function handle(CommandInterface $command): mixed
    {
        if (!$command instanceof SendNotificationCommand) {
            throw new \InvalidArgumentException('Expected SendNotificationCommand');
        }

        $this->logger->info('Handling send notification command', [
            'recipient' => $command->recipient,
            'type' => $command->type
        ]);

        $result = $this->notificationService->sendNotification(
            $command->recipient,
            $command->message
        );

        $this->logger->info('Notification sent successfully', [
            'notification_id' => $result['id']
        ]);

        return $result;
    }
}
