<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Routing\Attributes\Route;
use App\Infrastructure\Routing\Attributes\Controller;
use App\Infrastructure\Routing\HttpMethod;
use App\Infrastructure\Validation\Attributes\Required;
use App\Infrastructure\Validation\Attributes\Email;
use App\Infrastructure\Validation\Attributes\MinLength;
use App\Infrastructure\Validation\Attributes\MaxLength;

/**
 * User Controller with automatic validation
 */
#[Controller(prefix: '/users')]
class UserController
{
    private const CONTENT_TYPE_JSON = 'application/json';

    #[Route(HttpMethod::GET, '/', name: 'users.index')]
    public function index(RequestInterface $request): ResponseInterface
    {
        // Simulate fetching users
        $users = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com']
        ];

        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode(['users' => $users]));
    }

    #[Route(HttpMethod::POST, '/', name: 'users.store')]
    public function store(
        RequestInterface $request,
        
        #[Required('Name is required')]
        #[MinLength(2, 'Name must be at least 2 characters')]
        #[MaxLength(50, 'Name must be at most 50 characters')]
        string $name,
        
        #[Required('Email is required')]
        #[Email('Must be a valid email address')]
        string $email,
        
        #[Required('Password is required')]
        #[MinLength(8, 'Password must be at least 8 characters')]
        string $password
    ): ResponseInterface {
        // If we reach here, validation has passed
        
        // Simulate creating user
        $userId = random_int(1000, 9999);
        $user = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return (new Response())
            ->withStatus(201)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'message' => 'User created successfully',
                'user' => $user
            ]));
    }

    #[Route(HttpMethod::GET, '/{id}', name: 'users.show')]
    public function show(RequestInterface $request, string $id): ResponseInterface
    {
        // Simulate fetching user
        $user = [
            'id' => (int)$id,
            'name' => 'User ' . $id,
            'email' => 'user' . $id . '@example.com'
        ];

        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode(['user' => $user]));
    }

    #[Route(HttpMethod::PUT, '/{id}', name: 'users.update')]
    public function update(
        RequestInterface $request,
        string $id,
        
        #[Required('Name is required')]
        #[MinLength(2, 'Name must be at least 2 characters')]
        #[MaxLength(50, 'Name must be at most 50 characters')]
        string $name,
        
        #[Email('Must be a valid email address')]
        ?string $email = null
    ): ResponseInterface {
        // If we reach here, validation has passed
        
        // Simulate updating user
        $user = [
            'id' => (int)$id,
            'name' => $name,
            'email' => $email ?? 'user' . $id . '@example.com',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'message' => 'User updated successfully',
                'user' => $user
            ]));
    }

    #[Route(HttpMethod::DELETE, '/{id}', name: 'users.destroy')]
    public function destroy(RequestInterface $request, string $id): ResponseInterface
    {
        // Simulate deleting user
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'message' => 'User deleted successfully',
                'id' => (int)$id
            ]));
    }
}
