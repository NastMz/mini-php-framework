<?php
declare(strict_types=1);

namespace App\Domain\Event;

/**
 * UserRegisteredEvent
 *
 * Domain event that is dispatched when a user is registered.
 */
class UserRegisteredEvent implements DomainEventInterface
{
    private \DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly string $userId,
        public readonly string $email,
        public readonly string $name
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
