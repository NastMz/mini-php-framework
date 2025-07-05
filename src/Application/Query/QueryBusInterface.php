<?php
declare(strict_types=1);

namespace App\Application\Query;

/**
 * QueryBusInterface
 *
 * Interface for a query bus that dispatches read-only queries to their respective handlers.
 * Implementations should handle the routing of queries to the appropriate QueryHandler.
 */
interface QueryBusInterface
{
    /**
     * @param QueryInterface $query
     * @return mixed
     */
    public function dispatch(QueryInterface $query): mixed;
}
