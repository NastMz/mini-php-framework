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
use App\Infrastructure\Templating\TemplateEngine;
use App\Infrastructure\Security\CsrfTokenManager;

/**
 * FileUploadController
 *
 * Handles file upload requests
 */
#[Controller(prefix: '/')]
class FileUploadController
{
    private const JSON_CONTENT_TYPE = 'application/json';

    public function __construct(
        private FileUploadService $uploadService,
        private FileStorageInterface $storage,
        private TemplateEngine $templateEngine
    ) {}

    /**
     * Show upload form
     */
    #[Route(HttpMethod::GET, '/upload-test', name: 'upload.form')]
    public function showForm(): ResponseInterface
    {
        // Get base URL for API calls
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $apiBase = $protocol . '://' . $host;
        
        $data = [
            'title' => 'File Upload Test',
            'api_base' => $apiBase,
            'csrf_token' => CsrfTokenManager::getToken(),
            'upload_limits' => [
                'max_size' => '10 MB',
                'allowed_types' => 'JPEG, PNG, GIF',
                'validation' => 'Los archivos se validan por contenido, no solo por extensión'
            ]
        ];

        $content = $this->templateEngine->render('file-upload', $data);

        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'text/html')
            ->write($content);
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
                ->withHeader('Content-Type', self::JSON_CONTENT_TYPE)
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
            ->withHeader('Content-Type', self::JSON_CONTENT_TYPE)
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
    public function delete(string $path): ResponseInterface
    {
        // Decode path parameter
        $filePath = urldecode($path);
        
        // Check if file exists
        if (!$this->storage->exists($filePath)) {
            return (new Response())
                ->withStatus(404)
                ->withHeader('Content-Type', self::JSON_CONTENT_TYPE)
                ->write(json_encode([
                    'error' => 'Not Found',
                    'message' => 'File not found',
                ]));
        }
        
        // Delete the file
        $this->storage->delete($filePath);
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::JSON_CONTENT_TYPE)
            ->write(json_encode([
                'success' => true,
                'message' => 'File deleted successfully',
            ]));
    }

    /**
     * List uploaded files
     */
    #[Route(HttpMethod::GET, '/api/upload/list', name: 'upload.list')]
    public function listFiles(): ResponseInterface
    {
        try {
            // Get all files from storage directories
            $directories = ['uploads', 'avatars', 'documents'];
            $files = [];
            
            foreach ($directories as $directory) {
                $directoryPath = $this->storage->getPath($directory);
                if (is_dir($directoryPath)) {
                    $dirFiles = scandir($directoryPath);
                    foreach ($dirFiles as $file) {
                        if ($file !== '.' && $file !== '..' && is_file($directoryPath . '/' . $file)) {
                            $filePath = $directory . '/' . $file;
                            $files[] = [
                                'name' => $file,
                                'path' => $filePath,
                                'url' => $this->storage->url($filePath),
                                'directory' => $directory,
                                'size' => filesize($directoryPath . '/' . $file),
                                'modified' => date('Y-m-d H:i:s', filemtime($directoryPath . '/' . $file))
                            ];
                        }
                    }
                }
            }
            
            // Sort by modification time (newest first)
            usort($files, function($a, $b) {
                return strcmp($b['modified'], $a['modified']);
            });
            
            return (new Response())
                ->withStatus(200)
                ->withHeader('Content-Type', self::JSON_CONTENT_TYPE)
                ->write(json_encode([
                    'success' => true,
                    'data' => $files,
                    'total' => count($files)
                ]));
        } catch (\Exception $e) {
            return (new Response())
                ->withStatus(500)
                ->withHeader('Content-Type', self::JSON_CONTENT_TYPE)
                ->write(json_encode([
                    'success' => false,
                    'message' => 'Error al listar archivos: ' . $e->getMessage()
                ]));
        }
    }
}
