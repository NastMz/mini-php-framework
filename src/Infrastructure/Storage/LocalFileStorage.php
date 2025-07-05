<?php
declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Domain\Service\FileStorageInterface;

/**
 * LocalFileStorage
 *
 * A simple file storage implementation that stores files on the local filesystem.
 * It provides methods to put files, retrieve their URLs, and delete them.
 */
class LocalFileStorage implements FileStorageInterface
{
    /**
     * Constructor
     *
     * @param string $baseDir The base directory where files will be stored.
     * @param string $baseUrl The base URL for accessing stored files.
     */
    public function __construct(
        private string $baseDir,
        private string $baseUrl
    ) {}

    /**
     * Store the given binary content under $path (relative).
     *
     * @param string $path    e.g. "avatars/user123.png"
     * @param string $content Raw file contents (binary)
     */
    public function put(string $path, string $content): void
    {
        $full = rtrim($this->baseDir, '/') . '/' . ltrim($path, '/');
        $dir  = dirname($full);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($full, $content);
    }

    /**
     * Return a public URL or path where the stored file can be accessed.
     *
     * @param string $path
     * @return string
     */
    public function url(string $path): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Delete the file at $path if it exists.
     *
     * @param string $path
     */
    public function delete(string $path): void
    {
        $full = rtrim($this->baseDir, '/') . '/' . ltrim($path, '/');
        if (file_exists($full)) {
            unlink($full);
        }
    }

    /**
     * Check if a file exists at the given path.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        $full = rtrim($this->baseDir, '/') . '/' . ltrim($path, '/');
        return file_exists($full);
    }

    /**
     * Get the full path to a file or directory.
     *
     * @param string $path
     * @return string
     */
    public function getPath(string $path): string
    {
        return rtrim($this->baseDir, '/') . '/' . ltrim($path, '/');
    }
}
