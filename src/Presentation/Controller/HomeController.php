<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;

/**
 * Home Controller
 *
 * Handles home page requests
 */
class HomeController
{
    /**
     * Display the home page
     */
    public function index(RequestInterface $request): ResponseInterface
    {
        $html = $this->renderHomeView();
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->write($html);
    }

    /**
     * Simple home view renderer
     */
    private function renderHomeView(): string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $time = date('Y-m-d H:i:s');
        
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Framework PHP - Home</title>
    <link rel="stylesheet" href="/assets/css/home.css">
</head>
<body>
    <div class="container">
        <h1>🚀 Mini Framework PHP</h1>
        <p class="subtitle">¡Framework funcionando correctamente!</p>
        
        <div class="status">
            ✅ Estado: Operativo
        </div>
        
        <div class="features">
            <div class="feature">
                <strong>✨ Dependency Injection Container</strong> - PSR-11 Compatible
            </div>
            <div class="feature">
                <strong>🛣️ Router System</strong> - HTTP Method & Path Routing
            </div>
            <div class="feature">
                <strong>🔧 Middleware Support</strong> - PSR-15 Compatible
            </div>
            <div class="feature">
                <strong>📦 Autowiring</strong> - Automatic Dependency Resolution
            </div>
        </div>
        
        <div class="code">
            Request Info:<br>
            Method: {$method}<br>
            Path: {$uri}<br>
            Time: {$time}
        </div>
        
        <p class="footer-text">
            Mini Framework PHP - Versión de Test
        </p>
    </div>
</body>
</html>
HTML;
    }
}
