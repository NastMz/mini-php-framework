<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Service\FileUploadService;
use App\Domain\Service\FileStorageInterface;
use App\Infrastructure\Routing\Attributes\Route;
use App\Infrastructure\Routing\Attributes\Controller;
use App\Infrastructure\Routing\HttpMethod;

/**
 * FileUploadController
 *
 * Handles file upload requests
 */
#[Controller(prefix: '/upload')]
class FileUploadController
{
    public function __construct(
        private FileUploadService $uploadService,
        private FileStorageInterface $storage
    ) {}

    /**
     * Show upload form
     */
    #[Route(HttpMethod::GET, '/', name: 'upload.form')]
    public function showForm(): ResponseInterface
    {
        // This method will be implemented to show the upload form
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'text/html')
            ->write($this->getUploadFormHtml());
    }

    /**
     * Handle file upload via POST
     */
    #[Route(HttpMethod::POST, '/api/upload', name: 'upload.store')]
    public function upload(RequestInterface $request): ResponseInterface
    {
        // Check if file was uploaded
        if (empty($_FILES['file'])) {
            return (new Response())
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    'error' => 'Bad Request',
                    'message' => 'No file uploaded. Use "file" field name.',
                ]));
        }

        // Get destination directory from request (default to 'uploads')
        $destDir = $request->getParsedBody()['directory'] ?? 'uploads';
        
        // Upload the file (exceptions will be handled by ErrorHandlerMiddleware)
        $path = $this->uploadService->upload($_FILES['file'], $destDir);
        
        // Return success response with file info
        return (new Response())
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'path' => $path,
                    'url' => $this->storage->url($path),
                    'size' => $_FILES['file']['size'],
                    'type' => $_FILES['file']['type'],
                    'original_name' => $_FILES['file']['name'],
                ],
            ]));
    }

    /**
     * Delete uploaded file via DELETE
     */
    #[Route(HttpMethod::DELETE, '/api/upload/{path}', name: 'upload.delete')]
    public function delete(RequestInterface $request, string $path): ResponseInterface
    {
        // Decode path parameter
        $filePath = urldecode($path);
        
        // Check if file exists
        if (!$this->storage->exists($filePath)) {
            return (new Response())
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    'error' => 'Not Found',
                    'message' => 'File not found',
                ]));
        }
        
        // Delete the file
        $this->storage->delete($filePath);
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'success' => true,
                'message' => 'File deleted successfully',
            ]));
    }

    /**
     * Get upload form HTML
     */
    private function getUploadFormHtml(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <title>File Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .form-group { margin: 20px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { padding: 8px; margin-bottom: 10px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #005a8b; }
        .result { margin-top: 20px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>📁 File Upload Test</h1>
    
    <form action="/upload/api/upload" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="file">Select File:</label>
            <input type="file" id="file" name="file" accept="image/*" required>
        </div>
        
        <div class="form-group">
            <label for="directory">Directory:</label>
            <select id="directory" name="directory">
                <option value="uploads">uploads</option>
                <option value="avatars">avatars</option>
                <option value="documents">documents</option>
            </select>
        </div>
        
        <button type="submit">Upload File</button>
    </form>
    
    <div class="result">
        <h3>📋 Upload Guidelines:</h3>
        <ul>
            <li><strong>Max Size:</strong> 10 MB</li>
            <li><strong>Allowed Types:</strong> JPEG, PNG, GIF</li>
            <li><strong>Security:</strong> Files are validated by content, not just extension</li>
        </ul>
    </div>
</body>
</html>';
    }
}
