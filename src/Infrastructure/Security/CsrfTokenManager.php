<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

/**
 * CsrfTokenManager
 *
 * Manages CSRF tokens for form submissions and AJAX requests.
 * Generates a new token if not present in the session, and validates incoming tokens.
 */
class CsrfTokenManager
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Get the CSRF token from the session.
     * If the token does not exist, it generates a new one and stores it in the session.
     *
     * @return string The CSRF token
     */
    public static function getToken(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            // Generate a new token and store in session
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Validate the provided CSRF token against the one stored in the session.
     *
     * @param string|null $token The CSRF token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }
        // Timing-safe comparison
        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }
}
