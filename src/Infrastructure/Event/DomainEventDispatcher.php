<?php
declare(strict_types=1);

namespace App\Infrastructure\Event;

use Psr\Container\ContainerInterface;
use App\Domain\Event\DomainEventInterface;
use App\Domain\Event\DomainEventSubscriberInterface;

/**
 * DomainEventDispatcher
 *
 * This class is responsible for dispatching domain events to all registered subscribers.
 * It uses a container to resolve subscriber instances and calls their handle method
 * when an event is dispatched.
 */
class DomainEventDispatcher
{
    /** @var class-string<DomainEventSubscriberInterface>[] */
    private array $subscriberClasses;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The service container to resolve subscribers.
     * @param class-string<DomainEventSubscriberInterface>[] $subscriberClasses List of subscriber classes to dispatch events to.
     */
    public function __construct(
        private ContainerInterface $container,
        array $subscriberClasses
    ) {
        $this->subscriberClasses = $subscriberClasses;
    }

    /**
     * Dispatch an event to all interested subscribers.
     */
    public function dispatch(DomainEventInterface $event): void
    {
        $eventClass = get_class($event);

        foreach ($this->subscriberClasses as $subscriberClass) {
            if (! in_array($eventClass, $subscriberClass::subscribedTo(), true)) {
                continue;
            }
            /** @var DomainEventSubscriberInterface $subscriber */
            $subscriber = $this->container->get($subscriberClass);
            $subscriber->handle($event);
        }
    }
}
