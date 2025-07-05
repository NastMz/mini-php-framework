<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Service;

use Tests\TestCase;
use App\Infrastructure\Service\FileUploadService;
use App\Domain\Service\FileStorageInterface;
use App\Infrastructure\Event\DomainEventDispatcher;
use App\Infrastructure\Service\FileSizeException;
use App\Infrastructure\Service\FileTypeException;
use App\Infrastructure\Service\FileCorruptedException;

class FileUploadServiceTest extends TestCase
{
    private FileUploadService $service;
    private $fileStorageMock;
    private $eventDispatcherMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->fileStorageMock = $this->createMock(FileStorageInterface::class);
        $this->eventDispatcherMock = $this->createMock(DomainEventDispatcher::class);
        
        $this->service = new FileUploadService(
            $this->fileStorageMock,
            1048576, // 1MB max size
            ['image/jpeg', 'image/png', 'text/plain'],
            $this->eventDispatcherMock
        );
    }

    public function testUploadValidFile(): void
    {
        // Create a temporary test file
        $tempFile = $this->getTempFilePath('.txt');
        file_put_contents($tempFile, 'Test content');

        $fileInfo = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => $tempFile,
            'size' => 12,
            'error' => UPLOAD_ERR_OK
        ];

        $this->fileStorageMock
            ->expects($this->once())
            ->method('store')
            ->with($tempFile, 'test.txt')
            ->willReturn(['path' => '/uploads/test.txt', 'url' => '/uploads/test.txt']);

        $this->eventDispatcherMock
            ->expects($this->once())
            ->method('dispatch');

        $result = $this->service->upload($fileInfo);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('url', $result);

        // Clean up
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    public function testUploadThrowsFileSizeExceptionForLargeFile(): void
    {
        $fileInfo = [
            'name' => 'large.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/test',
            'size' => 2097152, // 2MB, larger than 1MB limit
            'error' => UPLOAD_ERR_OK
        ];

        $this->expectException(FileSizeException::class);
        $this->expectExceptionMessage('File size exceeds maximum allowed size');

        $this->service->upload($fileInfo);
    }

    public function testUploadThrowsFileTypeExceptionForInvalidType(): void
    {
        $fileInfo = [
            'name' => 'test.exe',
            'type' => 'application/octet-stream',
            'tmp_name' => '/tmp/test',
            'size' => 1024,
            'error' => UPLOAD_ERR_OK
        ];

        $this->expectException(FileTypeException::class);
        $this->expectExceptionMessage('File type not allowed');

        $this->service->upload($fileInfo);
    }

    public function testUploadThrowsFileCorruptedExceptionForUploadError(): void
    {
        $fileInfo = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/test',
            'size' => 1024,
            'error' => UPLOAD_ERR_PARTIAL
        ];

        $this->expectException(FileCorruptedException::class);
        $this->expectExceptionMessage('File upload was corrupted');

        $this->service->upload($fileInfo);
    }

    public function testValidateFileInfoWithValidData(): void
    {
        $fileInfo = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/test',
            'size' => 1024,
            'error' => UPLOAD_ERR_OK
        ];

        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('validateFileInfo');
        $method->setAccessible(true);

        // Should not throw exception
        $this->assertNull($method->invoke($this->service, $fileInfo));
    }

    public function testValidateFileInfoWithMissingFields(): void
    {
        $fileInfo = [
            'name' => 'test.txt',
            // Missing required fields
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('validateFileInfo');
        $method->setAccessible(true);

        $this->expectException(FileCorruptedException::class);
        $method->invoke($this->service, $fileInfo);
    }

    public function testDeleteFileCallsStorage(): void
    {
        $filePath = '/uploads/test.txt';

        $this->fileStorageMock
            ->expects($this->once())
            ->method('delete')
            ->with($filePath)
            ->willReturn(true);

        $result = $this->service->delete($filePath);

        $this->assertTrue($result);
    }

    public function testGetFileInfoReturnsStorageInfo(): void
    {
        $filePath = '/uploads/test.txt';
        $expectedInfo = [
            'path' => $filePath,
            'url' => '/uploads/test.txt',
            'size' => 1024,
            'type' => 'text/plain'
        ];

        $this->fileStorageMock
            ->expects($this->once())
            ->method('getInfo')
            ->with($filePath)
            ->willReturn($expectedInfo);

        $result = $this->service->getFileInfo($filePath);

        $this->assertEquals($expectedInfo, $result);
    }
}
