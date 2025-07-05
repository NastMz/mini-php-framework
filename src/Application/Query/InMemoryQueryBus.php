<?php
// src/Application/Query/InMemoryQueryBus.php
declare(strict_types=1);

namespace App\Application\Query;

use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * InMemoryQueryBus
 *
 * A simple in-memory implementation of a QueryBus that uses a container to resolve query handlers.
 * It maps query classes to their respective handler classes and dispatches queries to the appropriate handler.
 */
class InMemoryQueryBus implements QueryBusInterface
{
    /**
     * @param ContainerInterface            $c
     * @param array<class-string, class-string> $map
     *        Mapping Query FQCN → Handler FQCN
     */
    public function __construct(
        private ContainerInterface $c,
        private array              $map
    ) {}

    /**
     * Dispatches a query to its handler.
     *
     * @param QueryInterface $query The query to dispatch
     * @return mixed The result of the query handler
     * @throws RuntimeException If no handler is registered for the query
     */
    public function dispatch(QueryInterface $query): mixed
    {
        $qClass = get_class($query);
        if (!isset($this->map[$qClass])) {
            throw new RuntimeException("No handler registered for query {$qClass}");
        }

        $handler = $this->c->get($this->map[$qClass]);
        if (!$handler instanceof QueryHandlerInterface) {
            throw new RuntimeException("Handler must implement QueryHandlerInterface");
        }

        return $handler->handle($query);
    }
}
