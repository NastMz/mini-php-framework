<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Config\EnvLoader;

/**
 * AppSecurity
 *
 * Provides security-related functionalities such as encryption, decryption,
 * password hashing, and CSRF token generation and verification.
 */
class AppSecurity
{
    private static ?string $key = null;

    /**
     * Get the application key from environment variables
     *
     * @throws \RuntimeException if APP_KEY is not set
     */
    public static function getKey(): string
    {
        if (self::$key === null) {
            self::$key = EnvLoader::get('APP_KEY');
            
            if (empty(self::$key)) {
                throw new \RuntimeException('APP_KEY is not set. Run: php bin/console key:generate');
            }
        }

        return self::$key;
    }

    /**
     * Encrypt data using the application key
     */
    public static function encrypt(string $data): string
    {
        $key = self::getKey();
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', base64_decode($key), 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data using the application key
     */
    public static function decrypt(string $encryptedData): string
    {
        $key = self::getKey();
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', base64_decode($key), 0, $iv);
        
        if ($decrypted === false) {
            throw new \RuntimeException('Failed to decrypt data');
        }
        
        return $decrypted;
    }

    /**
     * Generate a secure hash for passwords
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password against a hash
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate a secure token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string
    {
        $token = self::generateToken(32);
        session_start();
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(string $token): bool
    {
        session_start();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
