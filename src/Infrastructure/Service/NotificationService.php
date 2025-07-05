<?php
declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Infrastructure\Logging\LoggerInterface;

/**
 * NotificationService
 *
 * Example service to demonstrate auto-registration capabilities.
 * This service will be automatically registered without manual configuration.
 */
class NotificationService
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * Send a notification.
     */
    public function sendNotification(string $recipient, string $message): array
    {
        $this->logger->info('Sending notification', [
            'recipient' => $recipient,
            'message' => $message
        ]);

        // Simulate sending notification
        return [
            'id' => uniqid('notification_'),
            'recipient' => $recipient,
            'message' => $message,
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Send a batch of notifications.
     */
    public function sendBatch(array $notifications): array
    {
        $results = [];
        foreach ($notifications as $notification) {
            $results[] = $this->sendNotification(
                $notification['recipient'],
                $notification['message']
            );
        }

        $this->logger->info('Batch notification sent', [
            'count' => count($results)
        ]);

        return $results;
    }
}
