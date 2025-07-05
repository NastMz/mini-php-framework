<?php
declare(strict_types=1);

namespace App\Application\Command;

/**
 * SendNotificationCommand
 *
 * Command to send a notification.
 * This command will be automatically mapped to its handler.
 */
class SendNotificationCommand implements CommandInterface
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $message,
        public readonly string $type = 'email'
    ) {
    }
}
