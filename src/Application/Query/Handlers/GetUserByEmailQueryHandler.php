<?php
declare(strict_types=1);

namespace App\Application\Query\Handlers;

use App\Application\Query\QueryHandlerInterface;
use App\Application\Query\QueryInterface;
use App\Application\Query\GetUserByEmailQuery;
use App\Infrastructure\Logging\LoggerInterface;

/**
 * GetUserByEmailQueryHandler
 *
 * Handles queries for retrieving users by email.
 */
class GetUserByEmailQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handle(QueryInterface $query): mixed
    {
        if (!$query instanceof GetUserByEmailQuery) {
            throw new \InvalidArgumentException('Expected GetUserByEmailQuery');
        }

        $this->logger->info('Fetching user by email', [
            'email' => $query->email
        ]);

        // Simulate database query
        // In real implementation, you would use a repository here
        $users = [
            'john@example.com' => [
                'id' => 'user_123',
                'email' => 'john@example.com',
                'name' => 'John Doe',
                'created_at' => '2025-01-01 12:00:00'
            ],
            'jane@example.com' => [
                'id' => 'user_456',
                'email' => 'jane@example.com',
                'name' => 'Jane Smith',
                'created_at' => '2025-01-02 14:30:00'
            ]
        ];

        return $users[$query->email] ?? null;
    }
}
