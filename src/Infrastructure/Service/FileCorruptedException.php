<?php
declare(strict_types=1);

namespace App\Infrastructure\Service;

/**
 * FileCorruptedException
 *
 * Exception thrown when uploaded file is corrupted or cannot be read.
 * This should result in a 422 Unprocessable Entity response.
 */
class FileCorruptedException extends FileUploadException
{
    public function __construct(string $reason = 'File is corrupted or unreadable')
    {
        parent::__construct($reason);
    }
}
