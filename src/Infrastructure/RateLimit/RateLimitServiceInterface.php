<?php
declare(strict_types=1);

namespace App\Infrastructure\RateLimit;

/**
 * RateLimitServiceInterface
 *
 * Interface for rate limiting services.
 */
interface RateLimitServiceInterface
{
    /**
     * Returns true if this IP is allowed to make a request now.
     */
    public function allow(string $ip): bool;

    /**
     * Returns the window size in seconds.
     */
    public function getWindowSize(): int;
}
