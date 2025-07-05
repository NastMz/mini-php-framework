<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Config\EnvLoader;

/**
 * JwtService
 *
 * Provides JWT (JSON Web Token) functionality for authentication.
 * Uses HMAC SHA256 for signing tokens.
 */
class JwtService
{
    private const ALGORITHM = 'HS256';
    private const TOKEN_TYPE = 'JWT';

    /**
     * Generate a JWT token for a user
     *
     * @param array $payload User data to include in token
     * @param int $expirationMinutes Token expiration time in minutes (default: 60)
     * @return string The JWT token
     */
    public static function generate(array $payload, int $expirationMinutes = 60): string
    {
        $header = [
            'typ' => self::TOKEN_TYPE,
            'alg' => self::ALGORITHM
        ];

        $now = time();
        $claims = array_merge($payload, [
            'iat' => $now,                              // Issued at
            'exp' => $now + ($expirationMinutes * 60),  // Expiration
            'iss' => EnvLoader::get('APP_NAME', 'MiniFramework'), // Issuer
        ]);

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($claims));
        
        $signature = self::sign($headerEncoded . '.' . $payloadEncoded);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    /**
     * Validate and decode a JWT token
     *
     * @param string $token The JWT token to validate
     * @return array|null The decoded payload if valid, null if invalid
     */
    public static function validate(string $token): ?array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signature] = $parts;

        // Verify signature
        $expectedSignature = self::sign($headerEncoded . '.' . $payloadEncoded);
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        // Decode header and payload
        $header = json_decode(self::base64UrlDecode($headerEncoded), true);
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if (!$header || !$payload) {
            return null;
        }

        // Check algorithm
        if ($header['alg'] !== self::ALGORITHM) {
            return null;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Extract user ID from token
     *
     * @param string $token The JWT token
     * @return int|null The user ID if found, null otherwise
     */
    public static function getUserId(string $token): ?int
    {
        $payload = self::validate($token);
        return $payload['user_id'] ?? null;
    }

    /**
     * Check if token is expired
     *
     * @param string $token The JWT token
     * @return bool True if expired, false otherwise
     */
    public static function isExpired(string $token): bool
    {
        $payload = self::validate($token);
        if (!$payload) {
            return true;
        }

        return isset($payload['exp']) && $payload['exp'] < time();
    }

    /**
     * Create signature for token parts
     *
     * @param string $data Data to sign
     * @return string Base64 URL encoded signature
     */
    private static function sign(string $data): string
    {
        $key = self::getJwtSecret();
        $signature = hash_hmac('sha256', $data, $key, true);
        return self::base64UrlEncode($signature);
    }

    /**
     * Get JWT secret from configuration
     *
     * @return string The JWT secret key
     * @throws JwtException If JWT secret is not configured
     */
    private static function getJwtSecret(): string
    {
        $secret = EnvLoader::get('JWT_SECRET');
        
        if (empty($secret)) {
            throw JwtException::secretNotConfigured();
        }

        return $secret;
    }

    /**
     * Base64 URL encode (RFC 4648)
     *
     * @param string $data Data to encode
     * @return string Base64 URL encoded string
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode (RFC 4648)
     *
     * @param string $data Data to decode
     * @return string Decoded string
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
