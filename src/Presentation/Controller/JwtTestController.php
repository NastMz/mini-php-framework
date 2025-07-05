<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Routing\Attributes\Route;
use App\Infrastructure\Routing\Attributes\Controller;
use App\Infrastructure\Routing\HttpMethod;
use App\Infrastructure\Templating\TemplateEngine;
use App\Infrastructure\Http\Response;

/**
 * JwtTestController
 *
 * Controller for JWT authentication testing interface.
 */
#[Controller]
class JwtTestController
{
    public function __construct(
        private TemplateEngine $templateEngine
    ) {}

    /**
     * Show JWT authentication test page
     *
     * @param RequestInterface $request The incoming request
     * @return ResponseInterface The rendered template response
     */
    #[Route(HttpMethod::GET, '/jwt-test', name: 'jwt.test')]
    public function index(RequestInterface $request): ResponseInterface
    {
        // Construir URL base desde los headers o usar una URL por defecto
        $host = $request->getHeader('host') ?? 'localhost:8080';
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $apiBase = $protocol . '://' . $host;
        
        $data = [
            'title' => 'JWT Authentication Test',
            'api_base' => $apiBase,
            'demo_credentials' => [
                'email' => 'admin@example.com',
                'password' => 'password123'
            ]
        ];

        $content = $this->templateEngine->render('jwt-test', $data);

        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'text/html')
            ->write($content);
    }
}
