<?php
declare(strict_types=1);

namespace App\Infrastructure\RateLimit;

/**
 * SimpleRateLimitService
 *
 * A file-based rate limiting service that doesn't require a database.
 * Uses temporary files to track request counts per IP.
 */
class SimpleRateLimitService implements RateLimitServiceInterface
{
    private string $storageDir;

    /**
     * Constructs a new SimpleRateLimitService.
     *
     * @param int $maxRequests The maximum number of requests allowed in the time window.
     * @param int $windowSize The size of the time window in seconds.
     * @param string|null $storageDir Directory to store rate limit files.
     */
    public function __construct(
        private int $maxRequests = 60,
        private int $windowSize = 60,
        ?string $storageDir = null
    ) {
        $this->storageDir = $storageDir ?? sys_get_temp_dir() . '/rate_limits';
        
        // Create storage directory if it doesn't exist
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Returns true if this IP is allowed to make a request now.
     */
    public function allow(string $ip): bool
    {
        $now = time();
        $window = (int) floor($now / $this->windowSize) * $this->windowSize;
        $filename = $this->storageDir . '/' . md5($ip) . '.json';
        
        $data = $this->loadData($filename);
        
        // New window or first request?
        if (!$data || $data['window_start'] !== $window) {
            $data = [
                'window_start' => $window,
                'request_count' => 1
            ];
            $this->saveData($filename, $data);
            return true;
        }
        
        // Within same window
        if ($data['request_count'] < $this->maxRequests) {
            $data['request_count']++;
            $this->saveData($filename, $data);
            return true;
        }
        
        // Over the limit
        return false;
    }

    /**
     * Returns the window size in seconds.
     */
    public function getWindowSize(): int
    {
        return $this->windowSize;
    }

    /**
     * Load rate limit data from file.
     */
    private function loadData(string $filename): ?array
    {
        if (!file_exists($filename)) {
            return null;
        }
        
        $content = file_get_contents($filename);
        if ($content === false) {
            return null;
        }
        
        $data = json_decode($content, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Save rate limit data to file.
     */
    private function saveData(string $filename, array $data): void
    {
        file_put_contents($filename, json_encode($data), LOCK_EX);
    }

    /**
     * Cleanup old rate limit files (optional maintenance method).
     */
    public function cleanup(): void
    {
        $files = glob($this->storageDir . '/*.json');
        $cutoff = time() - $this->windowSize * 2; // Keep files for 2 windows
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
