<?php
declare(strict_types=1);

namespace App\Application\Command;

/**
 * UploadFileCommand
 *
 * Command to upload a file to the system.
 */
class UploadFileCommand implements CommandInterface
{
    public function __construct(
        public readonly array $fileData,
        public readonly string $destinationDir
    ) {}
}
