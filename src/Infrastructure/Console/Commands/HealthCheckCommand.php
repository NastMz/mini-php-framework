<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;
use App\Infrastructure\Config\EnvLoader;
use App\Infrastructure\Security\AppSecurity;

class HealthCheckCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('health:check');
        $this->setDescription('Check application health and configuration');
    }

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
    
    private function checkAppKey(): array
    {
        try {
            $key = EnvLoader::get('APP_KEY');
            
            if (empty($key)) {
                return [
                    'status' => 'ERROR',
                    'message' => 'APP_KEY is not set. Run: php bin/console key:generate'
                ];
            }
            
            // Test encryption/decryption
            $testData = 'test-encryption-' . time();
            $encrypted = AppSecurity::encrypt($testData);
            $decrypted = AppSecurity::decrypt($encrypted);
            
            if ($testData === $decrypted) {
                return [
                    'status' => 'OK',
                    'message' => 'Application key is valid and encryption works'
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'message' => 'Application key is invalid or encryption failed'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'ERROR',
                'message' => 'Application key error: ' . $e->getMessage()
            ];
        }
    }
    
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
    
    private function checkCache(): array
    {
        $cacheDir = __DIR__ . '/../../../../storage/cache';
        
        if (!is_dir($cacheDir)) {
            return [
                'status' => 'ERROR',
                'message' => 'Cache directory does not exist'
            ];
        }
        
        if (!is_writable($cacheDir)) {
            return [
                'status' => 'ERROR',
                'message' => 'Cache directory is not writable'
            ];
        }
        
        // Test cache write/read
        $testFile = $cacheDir . '/health_check_test.tmp';
        $testData = 'test-' . time();
        
        if (file_put_contents($testFile, $testData) === false) {
            return [
                'status' => 'ERROR',
                'message' => 'Cannot write to cache directory'
            ];
        }
        
        $readData = file_get_contents($testFile);
        unlink($testFile);
        
        if ($readData === $testData) {
            return [
                'status' => 'OK',
                'message' => 'Cache system is working'
            ];
        } else {
            return [
                'status' => 'ERROR',
                'message' => 'Cache read/write test failed'
            ];
        }
    }
}
