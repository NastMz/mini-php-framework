<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Templating\TemplateEngine;
use App\Infrastructure\Routing\Attributes\Route;
use App\Infrastructure\Routing\HttpMethod;

/**
 * Home Controller
 *
 * Handles home page requests
 */
class HomeController
{
    public function __construct(
        private TemplateEngine $templateEngine
    ) {}

    /**
     * Display the home page
     */
    #[Route(HttpMethod::GET, '/', name: 'home.index')]
    public function index(RequestInterface $request): ResponseInterface
    {
        $html = $this->renderHomeView($request);
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->write($html);
    }

    /**
     * Test page (same as home)
     */
    #[Route(HttpMethod::GET, '/test', name: 'home.test')]
    public function test(RequestInterface $request): ResponseInterface
    {
        return $this->index($request);
    }

    /**
     * Home view renderer using template engine
     */
    private function renderHomeView(RequestInterface $request): string
    {
        $method = $request->getMethod();
        $uri = $request->getPath();
        $time = date('Y-m-d H:i:s');
        
        return $this->templateEngine->render('home', [
            'method' => $method,
            'uri' => $uri,
            'time' => $time
        ]);
    }
}
