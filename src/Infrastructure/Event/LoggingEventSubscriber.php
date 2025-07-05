<?php
declare(strict_types=1);

namespace App\Infrastructure\Event;

use App\Domain\Event\DomainEventInterface;
use App\Domain\Event\DomainEventSubscriberInterface;
use App\Domain\Event\UserRegisteredEvent;
use App\Domain\Event\FileUploadedEvent;
use App\Infrastructure\Logging\LoggerInterface;

/**
 * LoggingEventSubscriber
 *
 * Subscriber that logs domain events for audit purposes.
 */
class LoggingEventSubscriber implements DomainEventSubscriberInterface
{
    private const TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        private LoggerInterface $logger
    ) {}

    public static function subscribedTo(): array
    {
        return [
            UserRegisteredEvent::class,
            FileUploadedEvent::class,
        ];
    }

    public function handle(DomainEventInterface $event): void
    {
        $eventClass = get_class($event);
        $timestamp = $event->occurredOn()->format(self::TIMESTAMP_FORMAT);
        
        match ($eventClass) {
            UserRegisteredEvent::class => $this->handleUserRegistered($event),
            FileUploadedEvent::class => $this->handleFileUploaded($event),
            default => $this->logger->info("Unknown event: {$eventClass}", [
                'occurred_on' => $timestamp,
                'event_data' => $event
            ])
        };
    }

    private function handleUserRegistered(UserRegisteredEvent $event): void
    {
        $this->logger->info('User registered', [
            'user_id' => $event->userId,
            'email' => $event->email,
            'name' => $event->name,
            'occurred_on' => $event->occurredOn()->format(self::TIMESTAMP_FORMAT)
        ]);
    }

    private function handleFileUploaded(FileUploadedEvent $event): void
    {
        $this->logger->info('File uploaded', [
            'filename' => $event->filename,
            'path' => $event->path,
            'size' => $event->size,
            'mime_type' => $event->mimeType,
            'occurred_on' => $event->occurredOn()->format(self::TIMESTAMP_FORMAT)
        ]);
    }
}
