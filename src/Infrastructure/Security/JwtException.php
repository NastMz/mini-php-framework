<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use Exception;

/**
 * JwtException
 *
 * Exception thrown when JWT operations fail.
 */
class JwtException extends Exception
{
    public static function secretNotConfigured(): self
    {
        return new self('JWT_SECRET is not configured. Run: php bin/console jwt:generate-secret');
    }

    public static function invalidToken(string $reason = 'Token is invalid'): self
    {
        return new self($reason);
    }

    public static function expiredToken(): self
    {
        return new self('Token has expired');
    }

    public static function malformedToken(): self
    {
        return new self('Token is malformed');
    }
}
