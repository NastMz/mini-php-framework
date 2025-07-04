<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

class ClearCacheCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('cache:clear');
        $this->setDescription('Clear all cache files');
    }

    protected function execute(array $arguments, array $options): int
    {
        $cacheDir = __DIR__ . '/../../../../storage/cache';
        
        if (!is_dir($cacheDir)) {
            $this->info('Cache directory does not exist');
            return 0;
        }

        $this->clearDirectory($cacheDir);
        $this->success('Cache cleared successfully');
        
        return 0;
    }

    private function clearDirectory(string $dir): void
    {
        $files = glob($dir . '/*');
        
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->clearDirectory($file);
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}
