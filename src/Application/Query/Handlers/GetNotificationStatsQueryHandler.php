<?php
declare(strict_types=1);

namespace App\Application\Query\Handlers;

use App\Application\Query\QueryHandlerInterface;
use App\Application\Query\QueryInterface;
use App\Application\Query\GetNotificationStatsQuery;
use App\Infrastructure\Logging\LoggerInterface;

/**
 * GetNotificationStatsQueryHandler
 *
 * Handler for GetNotificationStatsQuery.
 * This handler will be automatically registered and mapped.
 */
class GetNotificationStatsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * Handle the get notification stats query.
     */
    public function handle(QueryInterface $query): mixed
    {
        if (!$query instanceof GetNotificationStatsQuery) {
            throw new \InvalidArgumentException('Expected GetNotificationStatsQuery');
        }

        $this->logger->info('Handling get notification stats query', [
            'date_from' => $query->dateFrom,
            'date_to' => $query->dateTo
        ]);

        // Simulate getting stats from database
        $stats = [
            'total_sent' => 1250,
            'total_delivered' => 1180,
            'total_failed' => 70,
            'delivery_rate' => 94.4,
            'period' => [
                'from' => $query->dateFrom ?? date('Y-m-01'),
                'to' => $query->dateTo ?? date('Y-m-d')
            ],
            'by_type' => [
                'email' => 800,
                'sms' => 350,
                'push' => 100
            ]
        ];

        $this->logger->info('Notification stats retrieved', [
            'total_sent' => $stats['total_sent'],
            'delivery_rate' => $stats['delivery_rate']
        ]);

        return $stats;
    }
}
