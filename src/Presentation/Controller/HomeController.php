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
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        h1 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        .subtitle {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.2rem;
        }
        .features {
            text-align: left;
            margin: 2rem 0;
        }
        .feature {
            margin: 1rem 0;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .feature:last-child {
            border-bottom: none;
        }
        .status {
            background: #e8f5e8;
            color: #2d5a2d;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            font-weight: bold;
        }
        .code {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: #333;
            text-align: left;
            margin: 1rem 0;
        }
    </style>
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
        
        <p style="color: #888; font-size: 0.9rem; margin-top: 2rem;">
            Mini Framework PHP - Versión de Test
        </p>
    </div>
</body>
</html>
HTML;
    }
}
