<?php
declare(strict_types=1);

namespace App\Domain\Event;

/**
 * DomainEventInterface
 *
 * This interface defines the structure for domain events in the application.
 * Each event should implement this interface to ensure it provides the necessary
 * information about when the event occurred.
 */
interface DomainEventInterface
{
    /**
     * @return \DateTimeImmutable When the event occurred
     */
    public function occurredOn(): \DateTimeImmutable;
}
