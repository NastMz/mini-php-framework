<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;
use App\Infrastructure\Config\EnvLoader;
use App\Infrastructure\Security\AppSecurity;

/**
 * Class HealthCheckCommand
 *
 * This command checks the health of the application by verifying environment configuration,
 * application key, database connection, storage directories, and cache system.
 */
class HealthCheckCommand extends Command
{
    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('health:check');
        $this->setDescription('Check application health and configuration');
    }

    /**
     * Execute the command to check application health.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
    protected function execute(array $arguments, array $options): int
    {
        $this->info('🔍 Checking application health...');
        
        $checks = [
            'Environment Configuration' => $this->checkEnvironment(),
            'Application Key' => $this->checkAppKey(),
            'Database Connection' => $this->checkDatabase(),
            'Storage Directories' => $this->checkStorage(),
            'Cache System' => $this->checkCache(),
        ];
        
        $allPassed = true;
        
        foreach ($checks as $checkName => $result) {
            if ($result['status'] === 'OK') {
                $this->success("✅ {$checkName}: {$result['message']}");
            } else {
                $this->error("❌ {$checkName}: {$result['message']}");
                $allPassed = false;
            }
        }
        
        if ($allPassed) {
            $this->success('🎉 All health checks passed!');
            return 0;
        } else {
            $this->error('⚠️  Some health checks failed.');
            return 1;
        }
    }
    
    /**
     * Check the environment configuration for required variables.
     *
     * @return array The result of the environment check.
     */
    private function checkEnvironment(): array
    {
        $envFile = __DIR__ . '/../../../../.env';
        
        if (!file_exists($envFile)) {
            return [
                'status' => 'ERROR',
                'message' => '.env file not found. Run: cp .env.example .env'
            ];
        }
        
        $requiredVars = ['APP_NAME', 'APP_ENV', 'APP_KEY'];
        $missing = [];
        
        foreach ($requiredVars as $var) {
            if (empty(EnvLoader::get($var))) {
                $missing[] = $var;
            }
        }
        
        if (!empty($missing)) {
            return [
                'status' => 'ERROR',
                'message' => 'Missing required environment variables: ' . implode(', ', $missing)
            ];
        }
        
        return [
            'status' => 'OK',
            'message' => 'Environment configuration is valid'
        ];
    }
    
    /**
     * Check the application key for validity and encryption functionality.
     *
     * @return array The result of the application key check.
     */
    private function checkAppKey(): array
    {
        $result = [
            'status' => 'ERROR',
            'message' => 'Unknown error checking app key'
        ];
        
        try {
            $key = EnvLoader::get('APP_KEY');
            
            if (empty($key)) {
                $result = [
                    'status' => 'ERROR',
                    'message' => 'APP_KEY is not set. Run: php bin/console key:generate'
                ];
            } else {
                // Test encryption/decryption
                $testData = 'test-encryption-' . time();
                $encrypted = AppSecurity::encrypt($testData);
                $decrypted = AppSecurity::decrypt($encrypted);
                
                if ($testData === $decrypted) {
                    $result = [
                        'status' => 'OK',
                        'message' => 'Application key is valid and encryption works'
                    ];
                } else {
                    $result = [
                        'status' => 'ERROR',
                        'message' => 'Application key is invalid or encryption failed'
                    ];
                }
            }
        } catch (\Exception $e) {
            $result = [
                'status' => 'ERROR',
                'message' => 'Application key error: ' . $e->getMessage()
            ];
        }
        
        return $result;
    }

    /**
     * Check the database connection and basic query execution.
     *
     * @return array The result of the database check.
     */
    private function checkDatabase(): array
    {
        try {
            $dsn = EnvLoader::get('DB_DSN');
            if (empty($dsn)) {
                // Build DSN from individual components
                $host = EnvLoader::get('DB_HOST', 'localhost');
                $port = EnvLoader::get('DB_PORT', '3306');
                $database = EnvLoader::get('DB_DATABASE', 'miniframework');
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            }
            
            $username = EnvLoader::get('DB_USERNAME', 'root');
            $password = EnvLoader::get('DB_PASSWORD', '');
            
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            
            // Test with a simple query
            $stmt = $pdo->query('SELECT 1');
            $result = $stmt->fetch();
            
            if ($result) {
                return [
                    'status' => 'OK',
                    'message' => 'Database connection successful'
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'message' => 'Database connection failed'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'ERROR',
                'message' => 'Database connection error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check the storage directories for existence and writability.
     *
     * @return array The result of the storage check.
     */
    private function checkStorage(): array
    {
        $directories = [
            __DIR__ . '/../../../../storage/cache',
            __DIR__ . '/../../../../storage/database',
            __DIR__ . '/../../../../logs',
        ];
        
        $errors = [];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    $errors[] = "Cannot create directory: {$dir}";
                }
            } elseif (!is_writable($dir)) {
                $errors[] = "Directory not writable: {$dir}";
            }
        }
        
        if (!empty($errors)) {
            return [
                'status' => 'ERROR',
                'message' => implode(', ', $errors)
            ];
        }
        
        return [
            'status' => 'OK',
            'message' => 'All storage directories are accessible'
        ];
    }
    
    /**
     * Check the cache system for existence and basic functionality.
     *
     * @return array The result of the cache check.
     */
    private function checkCache(): array
    {
        $result = [
            'status' => 'OK',
            'message' => 'Cache system is working'
        ];
        
        $cacheDir = __DIR__ . '/../../../../storage/cache';
        
        if (!is_dir($cacheDir)) {
            $result = [
                'status' => 'ERROR',
                'message' => 'Cache directory does not exist'
            ];
        } elseif (!is_writable($cacheDir)) {
            $result = [
                'status' => 'ERROR',
                'message' => 'Cache directory is not writable'
            ];
        } else {
            // Test cache write/read
            $testFile = $cacheDir . '/health_check_test.tmp';
            $testData = 'test-' . time();
            
            if (file_put_contents($testFile, $testData) === false) {
                $result = [
                    'status' => 'ERROR',
                    'message' => 'Cannot write to cache directory'
                ];
            } else {
                $readData = file_get_contents($testFile);
                unlink($testFile);
                
                if ($readData !== $testData) {
                    $result = [
                        'status' => 'ERROR',
                        'message' => 'Cache read/write test failed'
                    ];
                }
            }
        }
        
        return $result;
    }
}
