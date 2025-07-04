<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

/**
 * ClearCacheCommand class
 *
 * This command clears all cache files in the storage/cache directory.
 */
class ClearCacheCommand extends Command
{
    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('cache:clear');
        $this->setDescription('Clear all cache files');
    }

    /**
     * Execute the command to clear cache files.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
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

    /**
     * Recursively clear all files and directories in the specified directory.
     *
     * @param string $dir The directory to clear.
     */
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
