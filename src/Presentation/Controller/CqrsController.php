<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Application\Command\CommandBusInterface;
use App\Application\Query\QueryBusInterface;
use App\Application\Command\CreateUserCommand;
use App\Application\Query\GetUserByEmailQuery;
use App\Application\Query\GetSystemHealthQuery;

/**
 * CqrsController
 *
 * Demonstrates CQRS usage in web context.
 */
class CqrsController
{
    private const CONTENT_TYPE_JSON = 'application/json';

    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus
    ) {}

    /**
     * Create a new user via Command.
     */
    public function createUser(Request $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        
        $command = new CreateUserCommand(
            $data['email'] ?? '',
            $data['name'] ?? '',
            $data['password'] ?? ''
        );

        $result = $this->commandBus->dispatch($command);

        return (new Response())
            ->withStatus(201)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'success' => true,
                'data' => $result
            ]));
    }

    /**
     * Get user by email via Query.
     */
    public function getUserByEmail(Request $request): ResponseInterface
    {
        $email = $request->getQueryParam('email');
        
        if (!$email) {
            return (new Response())
                ->withStatus(400)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error' => 'Email parameter is required'
                ]));
        }

        $query = new GetUserByEmailQuery($email);
        $user = $this->queryBus->dispatch($query);

        if (!$user) {
            return (new Response())
                ->withStatus(404)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error' => 'User not found'
                ]));
        }

        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'success' => true,
                'data' => $user
            ]));
    }

    /**
     * Get system health via Query.
     */
    public function getHealth(): ResponseInterface
    {
        $query = new GetSystemHealthQuery();
        $health = $this->queryBus->dispatch($query);

        $status = $health['status'] === 'healthy' ? 200 : 503;

        return (new Response())
            ->withStatus($status)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode($health));
    }
}
