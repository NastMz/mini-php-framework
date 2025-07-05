<?php
declare(strict_types=1);

namespace App\Domain\Service;

/**
 * FileStorageInterface
 *
 * Interface for file storage services.
 * This allows storing, retrieving, and deleting files in a storage system.
 */
interface FileStorageInterface
{
    /**
     * Store the given binary content under $path (relative).
     *
     * @param string $path    e.g. "avatars/user123.png"
     * @param string $content Raw file contents (binary)
     */
    public function put(string $path, string $content): void;

    /**
     * Return a public URL or path where the stored file can be accessed.
     *
     * @param string $path
     * @return string
     */
    public function url(string $path): string;

    /**
     * Delete the file at $path if it exists.
     *
     * @param string $path
     */
    public function delete(string $path): void;

    /**
     * Check if a file exists at the given path.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool;
}
