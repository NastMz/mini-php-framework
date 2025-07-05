<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use RuntimeException;

/**
 * EncryptionException
 *
 * Exception thrown when encryption or decryption operations fail.
 * This should result in a 400 Bad Request response.
 */
class EncryptionException extends RuntimeException
{
    public function __construct(string $message = 'Encryption operation failed', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
