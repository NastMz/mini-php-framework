<?php
declare(strict_types=1);

namespace App\Domain\Event;

/**
 * DomainEventSubscriberInterface
 *
 * This interface defines the contract for domain event subscribers.
 * Subscribers must implement the `subscribedTo` method to specify which events they listen to,
 * and the `handle` method to process those events.
 */
interface DomainEventSubscriberInterface
{
    /**
     * Return list of DomainEventInterface FQCNs this subscriber listens to.
     *
     * @return class-string<DomainEventInterface>[]
     */
    public static function subscribedTo(): array;

    /**
     * Handle the dispatched event.
     *
     * @param DomainEventInterface $event
     */
    public function handle(DomainEventInterface $event): void;
}
