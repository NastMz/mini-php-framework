<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Routing\Attributes\Route;
use App\Infrastructure\Routing\Attributes\Controller;
use App\Infrastructure\Routing\HttpMethod;

/**
 * Example API Controller with automatic routing
 */
#[Controller(prefix: '/api/v1', middleware: ['auth', 'rate_limit'])]
class ApiExampleController
{
    #[Route(HttpMethod::GET, '/users', name: 'api.users.index')]
    public function getUsers(RequestInterface $request): ResponseInterface
    {
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'users' => [
                    ['id' => 1, 'name' => 'John Doe'],
                    ['id' => 2, 'name' => 'Jane Smith']
                ]
            ]));
    }

    #[Route(HttpMethod::GET, '/users/{id}', name: 'api.users.show', where: ['id' => '\d+'])]
    public function getUser(RequestInterface $request, string $id): ResponseInterface
    {
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'user' => ['id' => (int)$id, 'name' => 'User ' . $id]
            ]));
    }

    #[Route(HttpMethod::POST, '/users', name: 'api.users.store')]
    public function createUser(RequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        
        // Validation would happen here
        
        return (new Response())
            ->withStatus(201)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'message' => 'User created successfully',
                'user' => $data
            ]));
    }

    #[Route(HttpMethod::PUT, '/users/{id}', name: 'api.users.update')]
    #[Route(HttpMethod::PATCH, '/users/{id}', name: 'api.users.patch')]
    public function updateUser(RequestInterface $request, string $id): ResponseInterface
    {
        $data = $request->getParsedBody();
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'message' => 'User updated successfully',
                'user' => array_merge(['id' => (int)$id], $data)
            ]));
    }

    #[Route(HttpMethod::DELETE, '/users/{id}', name: 'api.users.destroy')]
    public function deleteUser(RequestInterface $request, string $id): ResponseInterface
    {
        return (new Response())
            ->withStatus(204);
    }
}
