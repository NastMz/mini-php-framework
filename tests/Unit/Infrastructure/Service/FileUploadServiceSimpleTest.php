<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Service;

use Tests\TestCase;
use App\Infrastructure\Service\FileUploadService;
use App\Domain\Service\FileStorageInterface;
use App\Infrastructure\Event\DomainEventDispatcher;
use App\Infrastructure\Service\FileSizeException;
use App\Infrastructure\Service\FileTypeException;
use App\Infrastructure\Service\FileUploadException;

class FileUploadServiceSimpleTest extends TestCase
{
    private const TEST_MIME_TYPE = 'text/plain';
    private const TEST_TMP_PATH = '/tmp/test';

    public function testUploadThrowsExceptionForLargeFile(): void
    {
        $fileStorage = $this->createMock(FileStorageInterface::class);
        $eventDispatcher = $this->createMock(DomainEventDispatcher::class);
        
        $service = new FileUploadService(
            $fileStorage,
            1024, // 1KB limit
            [self::TEST_MIME_TYPE],
            $eventDispatcher
        );

        $fileInfo = [
            'name' => 'large.txt',
            'type' => self::TEST_MIME_TYPE,
            'tmp_name' => '/tmp/test',
            'size' => 2048, // 2KB, exceeds limit
            'error' => UPLOAD_ERR_OK
        ];

        $this->expectException(FileSizeException::class);
        $service->upload($fileInfo, 'test-destination');
    }

    public function testUploadThrowsExceptionForInvalidType(): void
    {
        $fileStorage = $this->createMock(FileStorageInterface::class);
        $eventDispatcher = $this->createMock(DomainEventDispatcher::class);
        
        $service = new FileUploadService(
            $fileStorage,
            1048576, // 1MB
            [self::TEST_MIME_TYPE],
            $eventDispatcher
        );

        $fileInfo = [
            'name' => 'malware.exe',
            'type' => 'application/octet-stream',
            'tmp_name' => '/tmp/test',
            'size' => 1024,
            'error' => UPLOAD_ERR_OK
        ];

        $this->expectException(FileTypeException::class);
        $service->upload($fileInfo, 'test-destination');
    }

    public function testUploadThrowsExceptionForUploadError(): void
    {
        $fileStorage = $this->createMock(FileStorageInterface::class);
        $eventDispatcher = $this->createMock(DomainEventDispatcher::class);
        
        $service = new FileUploadService(
            $fileStorage,
            1048576,
            [self::TEST_MIME_TYPE],
            $eventDispatcher
        );

        $fileInfo = [
            'name' => 'test.txt',
            'type' => self::TEST_MIME_TYPE,
            'tmp_name' => '/tmp/test',
            'size' => 1024,
            'error' => UPLOAD_ERR_PARTIAL // Upload error
        ];

        $this->expectException(FileUploadException::class);
        $this->expectExceptionMessage('File was only partially uploaded');
        $service->upload($fileInfo, 'test-destination');
    }

    public function testFileUploadServiceCanBeInstantiated(): void
    {
        $fileStorage = $this->createMock(FileStorageInterface::class);
        $eventDispatcher = $this->createMock(DomainEventDispatcher::class);
        
        $service = new FileUploadService(
            $fileStorage,
            1048576,
            [self::TEST_MIME_TYPE],
            $eventDispatcher
        );

        $this->assertInstanceOf(FileUploadService::class, $service);
    }
}
