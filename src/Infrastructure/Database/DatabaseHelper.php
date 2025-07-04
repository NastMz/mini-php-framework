<?php
declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;

/**
 * DatabaseHelper provides methods to create a PDO connection and manage database configurations.
 */
class DatabaseHelper
{
    /**
     * Create a PDO connection with automatic SQLite database creation
     */
    public static function createPdo(array $config): PDO
    {
        $dsn = $config['dsn'];
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;
        
        // Check if it's SQLite and create database file if needed
        if (str_starts_with($dsn, 'sqlite:')) {
            $dbPath = str_replace('sqlite:', '', $dsn);
            
            // Handle relative paths - convert to absolute
            if (!str_starts_with($dbPath, '/') && !preg_match('/^[A-Za-z]:/', $dbPath)) {
                $dbPath = __DIR__ . '/../../../' . $dbPath;
            }
            
            // Normalize path
            $dbPath = realpath(dirname($dbPath)) . DIRECTORY_SEPARATOR . basename($dbPath);
            
            // Create directory if it doesn't exist
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new \RuntimeException("Cannot create directory: {$dir}");
                }
            }
            
            // Create empty database file if it doesn't exist
            if (!file_exists($dbPath)) {
                if (touch($dbPath) === false) {
                    throw new \RuntimeException("Cannot create database file: {$dbPath}");
                }
                chmod($dbPath, 0664);
            }
            
            // Update DSN to use absolute path
            $dsn = 'sqlite:' . $dbPath;
        }
        
        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    
    /**
     * Get database configuration from settings
     */
    public static function getConfig(array $database): array
    {
        return [
            'dsn' => $database['dsn'],
            'username' => $database['username'] ?? null,
            'password' => $database['password'] ?? null,
        ];
    }
}
