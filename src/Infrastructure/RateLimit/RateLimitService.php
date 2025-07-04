<?php
declare(strict_types=1);

namespace App\Infrastructure\RateLimit;

use App\Infrastructure\Persistence\QueryBuilder;
use PDO;

/**
 * RateLimitService
 *
 * This service handles rate limiting for IP addresses.
 * It allows a maximum number of requests within a specified time window.
 */
class RateLimitService implements RateLimitServiceInterface
{
    /**
     * Constructs a new RateLimitService.
     *
     * @param PDO $pdo The PDO instance for database access.
     * @param int $maxRequests The maximum number of requests allowed in the time window.
     * @param int $windowSize The size of the time window in seconds.
     */
    public function __construct(
        private PDO $pdo,
        private int $maxRequests = 60,
        private int $windowSize  = 60
    ) {}

    /**
     * Returns true if this IP is allowed to make a request now.
     */
    public function allow(string $ip): bool
    {
        $now    = time();
        $window = (int) floor($now / $this->windowSize) * $this->windowSize;
        $allowed = true;

        $qb = new QueryBuilder($this->pdo);
        $row = $qb
            ->select('rate_limits', ['window_start','request_count'])
            ->where('ip = :ip', ['ip' => $ip])
            ->execute()
            ->fetch();

        if ($row) {
            // New window?
            if ((int)$row['window_start'] !== $window) {
                $qb = new QueryBuilder($this->pdo);
                $qb->update('rate_limits', [
                    'window_start'  => $window,
                    'request_count' => 1
                ])
                ->where('ip = :ip', ['ip' => $ip])
                ->execute();
            }
            // Within same window and under limit?
            elseif ((int)$row['request_count'] < $this->maxRequests) {
                $qb = new QueryBuilder($this->pdo);
                $qb->update('rate_limits', [
                    'request_count' => (int)$row['request_count'] + 1
                ])
                ->where('ip = :ip', ['ip' => $ip])
                ->execute();
            }
            // Over the limit
            else {
                $allowed = false;
            }
        } else {
            // First request for this IP
            $qb = new QueryBuilder($this->pdo);
            $qb->insert('rate_limits', [
                'ip'            => $ip,
                'window_start'  => $window,
                'request_count' => 1
            ])->execute();
        }

        return $allowed;
    }

    /**
     * Returns the maximum number of requests allowed in the time window.
     */
    public function getWindowSize(): int
    {
        return $this->windowSize;
    }
}
