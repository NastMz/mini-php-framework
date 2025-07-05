<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Security\JwtService;
use App\Infrastructure\Security\JwtException;

/**
 * JwtAuthenticationMiddleware
 *
 * Validates JWT tokens and protects routes requiring authentication.
 * Adds user information to the request for downstream handlers.
 */
class JwtAuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * Process the request and validate JWT token
     *
     * @param RequestInterface $request The incoming request
     * @param RequestHandlerInterface $next The next handler in the middleware chain
     * @return ResponseInterface The response after processing
     */
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $result = $this->validateAuthenticationToken($request);
        
        if (is_string($result)) {
            // Authentication failed
            return $this->unauthorizedResponse($result);
        }
        
        // Authentication successful, pass the modified request
        return $next->handle($result);
    }

    /**
     * Validate authentication token and add user attributes to request
     *
     * @param RequestInterface $request The incoming request
     * @return RequestInterface|string Modified request if successful, error message if failed
     */
    private function validateAuthenticationToken(RequestInterface $request): RequestInterface|string
    {
        // Extract token from Authorization header
        $authHeader = $request->getHeader('Authorization');
        
        if (empty($authHeader)) {
            return 'Missing Authorization header';
        }
        
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return 'Invalid Authorization header format';
        }
        
        $token = substr($authHeader, 7); // Remove "Bearer " prefix
        
        try {
            // Validate the token
            $payload = JwtService::validate($token);
            
            if ($payload === null) {
                return 'Invalid or expired token';
            }
            
            // Add user information to request attributes and return modified request
            return $request->withAttribute('auth_user_id', $payload['user_id'] ?? null)
                          ->withAttribute('auth_user_email', $payload['email'] ?? null)
                          ->withAttribute('auth_payload', $payload);
        } catch (JwtException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Create an unauthorized response
     *
     * @param string $message Error message
     * @return ResponseInterface 401 Unauthorized response
     */
    private function unauthorizedResponse(string $message): ResponseInterface
    {
        return (new Response())
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'error' => 'Unauthorized',
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
    }
}
