<?php
declare(strict_types=1);

namespace App\Application\Query;

/**
 * QueryHandlerInterface
 *
 * Interface for query handlers that process read-only requests.
 * Implementations should handle specific query types and return the corresponding read-model or DTO.
 */
interface QueryHandlerInterface
{
    /**
     * @param QueryInterface $query
     * @return mixed  The read-model or DTO
     */
    public function handle(QueryInterface $query): mixed;
}
