<?php
declare(strict_types=1);

namespace App\Infrastructure\Service;

/**
 * FileTypeException
 *
 * Exception thrown when uploaded file type is not allowed.
 * This should result in a 415 Unsupported Media Type response.
 */
class FileTypeException extends FileUploadException
{
    public function __construct(string $actualType, array $allowedTypes)
    {
        $allowed = implode(', ', $allowedTypes);
        $message = "File type '{$actualType}' is not allowed. Allowed types: {$allowed}";
        parent::__construct($message);
    }
}
