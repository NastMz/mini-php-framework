<?php
declare(strict_types=1);

namespace App\Domain\Event;

/**
 * FileUploadedEvent
 *
 * Domain event that is dispatched when a file is uploaded.
 */
class FileUploadedEvent implements DomainEventInterface
{
    private \DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly string $filename,
        public readonly string $path,
        public readonly int $size,
        public readonly string $mimeType
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
