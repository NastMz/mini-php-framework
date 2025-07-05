<?php
declare(strict_types=1);

namespace App\Application\Query;

/**
 * GetNotificationStatsQuery
 *
 * Query to get notification statistics.
 * This query will be automatically mapped to its handler.
 */
class GetNotificationStatsQuery implements QueryInterface
{
    public function __construct(
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null
    ) {
    }
}
