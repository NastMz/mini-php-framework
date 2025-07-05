<?php
declare(strict_types=1);

namespace App\Application\Command\Handlers;

use App\Application\Command\CommandHandlerInterface;
use App\Application\Command\CommandInterface;
use App\Application\Command\UploadFileCommand;
use App\Infrastructure\Service\FileUploadService;
use App\Infrastructure\Logging\LoggerInterface;

/**
 * UploadFileCommandHandler
 *
 * Handles file upload operations.
 */
class UploadFileCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private FileUploadService $uploadService,
        private LoggerInterface $logger
    ) {}

    public function handle(CommandInterface $command): mixed
    {
        if (!$command instanceof UploadFileCommand) {
            throw new \InvalidArgumentException('Expected UploadFileCommand');
        }

        $this->logger->info('Processing file upload command', [
            'destination' => $command->destinationDir,
            'file_name' => $command->fileData['name'] ?? 'unknown'
        ]);

        // Use the existing FileUploadService which already dispatches events
        $path = $this->uploadService->upload($command->fileData, $command->destinationDir);

        return [
            'success' => true,
            'path' => $path,
            'uploaded_at' => date('Y-m-d H:i:s')
        ];
    }
}
