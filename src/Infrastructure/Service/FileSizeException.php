<?php
declare(strict_types=1);

namespace App\Infrastructure\Service;

/**
 * FileSizeException
 *
 * Exception thrown when uploaded file exceeds size limit.
 * This should result in a 413 Payload Too Large response.
 */
class FileSizeException extends FileUploadException
{
    public function __construct(int $actualSize, int $maxSize)
    {
        $message = "File size {$actualSize} bytes exceeds maximum allowed size of {$maxSize} bytes";
        parent::__construct($message);
    }
}
