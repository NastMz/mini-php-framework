<?php
declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Service\FileStorageInterface;
use App\Infrastructure\Service\FileUploadException;
use App\Infrastructure\Service\FileSizeException;
use App\Infrastructure\Service\FileTypeException;
use App\Infrastructure\Service\FileCorruptedException;

/**
 * FileUploadService
 *
 * Handles file uploads, validating size and MIME type, and storing files using a FileStorageInterface.
 * This service abstracts the file upload logic and can be used in controllers or other services.
 */
class FileUploadService
{
    // File signature constants
    private const SIGNATURE_ZIP = "PK\x03\x04";
    private const SIGNATURE_RIFF = "RIFF";
    private const SIGNATURE_JPEG = "\xFF\xD8\xFF";
    private const SIGNATURE_PNG = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";

    /**
     * Constructor
     *
     * @param FileStorageInterface $storage The storage service to use for saving files
     * @param int $maxSizeBytes Maximum allowed file size in bytes (default 2MB)
     * @param array $allowedMime List of allowed MIME types (default ['image/png', 'image/jpeg'])
     */
    public function __construct(
        private FileStorageInterface $storage,
        private int $maxSizeBytes = 2_000_000,
        private array $allowedMime = ['image/png', 'image/jpeg']
    ) {}

    /**
     * Handle a single uploaded file from $_FILES.
     *
     * @param array{
     *   tmp_name: string,
     *   name:     string,
     *   type:     string,
     *   size:     int,
     *   error:    int
     * } $file
     * @param string $destDir  e.g. "avatars"
     * @return string          The relative path where it was stored
     * @throws FileUploadException on any validation or storage error
     * @throws FileSizeException when file is too large
     * @throws FileTypeException when file type is not allowed
     * @throws FileCorruptedException when file cannot be read
     */
    public function upload(array $file, string $destDir): string
    {
        // Validate upload errors
        $this->validateUploadError($file['error']);
        
        // Validate file size
        $this->validateFileSize($file['size']);
        
        // Validate MIME type (basic check)
        $this->validateMimeType($file['type']);
        
        // Validate file content (more robust check)
        $this->validateFileContent($file['tmp_name'], $file['type']);

        $ext      = $this->getFileExtension($file['name']);
        $filename = $this->generateUniqueFilename($ext);
        $path     = rtrim($destDir, '/') . '/' . $filename;
        $content  = file_get_contents($file['tmp_name']);

        if ($content === false) {
            throw new FileCorruptedException("Unable to read temporary file");
        }

        $this->storage->put($path, $content);
        return $path;
    }

    /**
     * Validate PHP upload error codes.
     *
     * @param int $error
     * @throws FileUploadException
     * @throws FileSizeException
     */
    private function validateUploadError(int $error): void
    {
        switch ($error) {
            case UPLOAD_ERR_OK:
                return;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new FileSizeException(0, $this->maxSizeBytes);
            case UPLOAD_ERR_PARTIAL:
                throw new FileUploadException("File was only partially uploaded");
            case UPLOAD_ERR_NO_FILE:
                throw new FileUploadException("No file was uploaded");
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new FileUploadException("Missing temporary directory");
            case UPLOAD_ERR_CANT_WRITE:
                throw new FileUploadException("Failed to write file to disk");
            case UPLOAD_ERR_EXTENSION:
                throw new FileUploadException("File upload stopped by extension");
            default:
                throw new FileUploadException("Unknown upload error code: {$error}");
        }
    }

    /**
     * Validate file size.
     *
     * @param int $size
     * @throws FileSizeException
     */
    private function validateFileSize(int $size): void
    {
        if ($size > $this->maxSizeBytes) {
            throw new FileSizeException($size, $this->maxSizeBytes);
        }
    }

    /**
     * Validate MIME type.
     *
     * @param string $mimeType
     * @throws FileTypeException
     */
    private function validateMimeType(string $mimeType): void
    {
        if (!in_array($mimeType, $this->allowedMime, true)) {
            throw new FileTypeException($mimeType, $this->allowedMime);
        }
    }

    /**
     * Validate file content by checking actual file signature.
     *
     * @param string $tmpPath
     * @param string $declaredMime
     * @throws FileCorruptedException
     * @throws FileTypeException
     */
    private function validateFileContent(string $tmpPath, string $declaredMime): void
    {
        if (!file_exists($tmpPath)) {
            throw new FileCorruptedException("Temporary file does not exist");
        }

        // Get file signature (magic bytes)
        $handle = fopen($tmpPath, 'rb');
        if ($handle === false) {
            throw new FileCorruptedException("Cannot open temporary file");
        }

        $signature = fread($handle, 12); // Read first 12 bytes
        fclose($handle);

        if ($signature === false) {
            throw new FileCorruptedException("Cannot read file signature");
        }

        // Check for malicious content first
        $this->checkForMaliciousContent($signature);

        $actualMime = $this->detectMimeFromSignature($signature);
        
        if ($actualMime === null) {
            throw new FileTypeException('unknown', $this->allowedMime);
        }

        // Check if actual MIME matches declared MIME
        if ($actualMime !== $declaredMime) {
            throw new FileTypeException($actualMime, $this->allowedMime);
        }
    }

    /**
     * Detect MIME type from file signature (magic bytes).
     *
     * @param string $signature
     * @return string|null
     */
    private function detectMimeFromSignature(string $signature): ?string
    {
        // Try image formats first
        $imageType = $this->detectImageType($signature);
        if ($imageType !== null) {
            return $imageType;
        }

        // Try document formats, then archive formats
        return $this->detectDocumentType($signature) ?? $this->detectArchiveType($signature);
    }

    /**
     * Detect image MIME types.
     *
     * @param string $signature
     * @return string|null
     */
    private function detectImageType(string $signature): ?string
    {
        $imageSignatures = [
            self::SIGNATURE_JPEG => 'image/jpeg',
            self::SIGNATURE_PNG => 'image/png',
            "GIF87a" => 'image/gif',
            "GIF89a" => 'image/gif',
            "BM" => 'image/bmp',
            "II\x2A\x00" => 'image/tiff',
            "MM\x00\x2A" => 'image/tiff',
            "<?xml" => 'image/svg+xml',
            "<svg" => 'image/svg+xml',
        ];

        foreach ($imageSignatures as $sig => $mime) {
            if (str_starts_with($signature, $sig)) {
                return $mime;
            }
        }

        // Special case for WebP
        if (str_starts_with($signature, self::SIGNATURE_RIFF) &&
            strlen($signature) >= 12 && substr($signature, 8, 4) === 'WEBP') {
            return 'image/webp';
        }

        return null;
    }

    /**
     * Detect document MIME types.
     *
     * @param string $signature
     * @return string|null
     */
    private function detectDocumentType(string $signature): ?string
    {
        $documentSignatures = [
            "%PDF-" => 'application/pdf',
            self::SIGNATURE_ZIP => 'application/zip',
            "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1" => 'application/msword',
            "{\\rtf" => 'application/rtf',
            "<!DOCTYPE" => 'text/html',
            "<html" => 'text/html',
            "<?xml" => 'application/xml',
        ];

        foreach ($documentSignatures as $sig => $mime) {
            if (str_starts_with($signature, $sig)) {
                return $mime;
            }
        }

        return null;
    }

    /**
     * Detect archive MIME types.
     *
     * @param string $signature
     * @return string|null
     */
    private function detectArchiveType(string $signature): ?string
    {
        $archiveSignatures = [
            self::SIGNATURE_ZIP => 'application/zip',
            "Rar!\x1A\x07\x00" => 'application/x-rar-compressed',
            "Rar!\x1A\x07\x01\x00" => 'application/x-rar-compressed',
            "7z\xBC\xAF\x27\x1C" => 'application/x-7z-compressed',
        ];

        foreach ($archiveSignatures as $sig => $mime) {
            if (str_starts_with($signature, $sig)) {
                return $mime;
            }
        }

        return null;
    }

    /**
     * Check for potentially malicious file patterns.
     *
     * @param string $signature
     * @throws FileCorruptedException
     */
    private function checkForMaliciousContent(string $signature): void
    {
        // Check for executable file signatures
        $maliciousSignatures = [
            "\x4D\x5A", // Windows PE/DOS executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xCA\xFE\xBA\xBE", // Mach-O executable
        ];

        foreach ($maliciousSignatures as $malSig) {
            if (str_starts_with($signature, $malSig)) {
                throw new FileCorruptedException("Executable files are not allowed");
            }
        }

        // Check for script tags in images (potential XSS)
        if (str_contains($signature, '<script') || str_contains($signature, 'javascript:')) {
            throw new FileCorruptedException("File contains potentially malicious script content");
        }
    }

    /**
     * Get file extension safely.
     *
     * @param string $filename
     * @return string
     */
    private function getFileExtension(string $filename): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return strtolower($ext);
    }

    /**
     * Generate a unique filename.
     *
     * @param string $extension
     * @return string
     */
    private function generateUniqueFilename(string $extension): string
    {
        return uniqid('', true) . '.' . $extension;
    }
}
