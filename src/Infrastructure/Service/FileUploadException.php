<?php
declare(strict_types=1);

namespace App\Infrastructure\Service;

use RuntimeException;

/**
 * FileUploadException
 *
 * Base exception for file upload errors.
 * This should result in a 400 Bad Request response.
 */
class FileUploadException extends RuntimeException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
