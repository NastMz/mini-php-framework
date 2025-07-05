<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Security\JwtService;
use App\Infrastructure\Security\JwtException;
use App\Infrastructure\Validation\Attributes\Required;
use App\Infrastructure\Validation\Attributes\Email;
use App\Infrastructure\Validation\Attributes\MinLength;
use App\Infrastructure\Routing\Attributes\Route;
use App\Infrastructure\Routing\Attributes\Controller;
use App\Infrastructure\Routing\HttpMethod;

/**
 * AuthController
 *
 * Handles user authentication (login/logout) and JWT token management.
 */
#[Controller(prefix: '/auth')]
class AuthController
{
    private const CONTENT_TYPE_JSON = 'application/json';
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';
    private const TOKEN_EXPIRY_MINUTES = 60;
    private const TOKEN_EXPIRY_SECONDS = 3600;
    /**
     * User login endpoint
     *
     * @param RequestInterface $request HTTP request object
     * @return ResponseInterface JSON response with JWT token or error
     */
    #[Route(HttpMethod::POST, '/login', name: 'auth.login')]
    public function login(RequestInterface $request): ResponseInterface {
        try {
            // Try to get JSON from parsed body first, then from raw body
            $data = $request->getParsedBody();
            
            if (empty($data)) {
                $rawBody = file_get_contents('php://input');
                $data = json_decode($rawBody, true);
            }
            
            if (!$data || !isset($data['email']) || !isset($data['password'])) {
                return $this->errorResponse('Email and password are required', 400);
            }
            
            $email = $data['email'];
            $password = $data['password'];
            
            // Basic validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->errorResponse('Invalid email format', 400);
            }
            
            if (strlen($password) < 6) {
                return $this->errorResponse('Password must be at least 6 characters', 400);
            }
            
            $loginResult = $this->authenticateUser($email, $password);
            
            if (!$loginResult) {
                return $this->errorResponse('Invalid credentials', 401);
            }
            
            return $this->createLoginResponse($loginResult);
        } catch (JwtException $e) {
            return $this->errorResponse('Authentication service error: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred during authentication', 500);
        }
    }

    /**
     * User logout endpoint
     *
     * @return ResponseInterface JSON response confirming logout
     */
    #[Route(HttpMethod::POST, '/logout', name: 'auth.logout')]
    public function logout(): ResponseInterface
    {
        // In a stateless JWT system, logout is typically handled client-side
        // by simply discarding the token. For completeness, we provide this endpoint.
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'success' => true,
                'message' => 'Logout successful'
            ]));
    }

    /**
     * Get current user information
     *
     * @param RequestInterface $request The incoming request (with auth middleware)
     * @return ResponseInterface JSON response with user data
     */
    #[Route(HttpMethod::GET, '/me', name: 'auth.me')]
    public function me(RequestInterface $request): ResponseInterface
    {
        // This endpoint should be protected by JwtAuthenticationMiddleware
        $userId = $request->getAttribute('auth_user_id');
        $userEmail = $request->getAttribute('auth_user_email');
        $authPayload = $request->getAttribute('auth_payload');
        
        if (!$userId) {
            return $this->errorResponse('Authentication required', 401);
        }
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'success' => true,
                'user' => [
                    'id' => $userId,
                    'email' => $userEmail,
                    'role' => $authPayload['role'] ?? 'user',
                    'token_issued_at' => date(self::DATETIME_FORMAT, $authPayload['iat'] ?? time()),
                    'token_expires_at' => date(self::DATETIME_FORMAT, $authPayload['exp'] ?? time())
                ]
            ]));
    }

    /**
     * Refresh JWT token
     *
     * @param RequestInterface $request The incoming request (with auth middleware)
     * @return ResponseInterface JSON response with new token
     */
    #[Route(HttpMethod::POST, '/refresh', name: 'auth.refresh')]
    public function refresh(RequestInterface $request): ResponseInterface
    {
        try {
            $refreshResult = $this->generateRefreshToken($request);
            
            if (!$refreshResult) {
                return $this->errorResponse('Authentication required', 401);
            }
            
            return $this->createTokenResponse($refreshResult);
        } catch (JwtException $e) {
            return $this->errorResponse('Token refresh failed: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred during token refresh', 500);
        }
    }

    /**
     * Authenticate user credentials
     *
     * @param string $email User email
     * @param string $password User password
     * @return array|null User data if authenticated, null otherwise
     */
    private function authenticateUser(string $email, string $password): ?array
    {
        // For demonstration purposes, we'll use a simple check
        // In a real application, you would validate against database with hashed passwords
        if ($email === 'admin@example.com' && $password === 'password123') {
            return [
                'user_id' => 1,
                'email' => $email,
                'role' => 'admin'
            ];
        }
        
        return null;
    }

    /**
     * Generate refresh token data
     *
     * @param RequestInterface $request The incoming request
     * @return array|null Token data if successful, null otherwise
     */
    private function generateRefreshToken(RequestInterface $request): ?array
    {
        $userId = $request->getAttribute('auth_user_id');
        $userEmail = $request->getAttribute('auth_user_email');
        $authPayload = $request->getAttribute('auth_payload');
        
        if (!$userId) {
            return null;
        }
        
        return [
            'user_id' => $userId,
            'email' => $userEmail,
            'role' => $authPayload['role'] ?? 'user'
        ];
    }

    /**
     * Create login response with JWT token
     *
     * @param array $userData User data
     * @return ResponseInterface JSON response with token
     */
    private function createLoginResponse(array $userData): ResponseInterface
    {
        $token = JwtService::generate($userData, self::TOKEN_EXPIRY_MINUTES);
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $userData['user_id'],
                    'email' => $userData['email'],
                    'role' => $userData['role']
                ],
                'expires_in' => self::TOKEN_EXPIRY_SECONDS
            ]));
    }

    /**
     * Create token response
     *
     * @param array $tokenData Token data
     * @return ResponseInterface JSON response with token
     */
    private function createTokenResponse(array $tokenData): ResponseInterface
    {
        $newToken = JwtService::generate($tokenData, self::TOKEN_EXPIRY_MINUTES);
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'token' => $newToken,
                'expires_in' => self::TOKEN_EXPIRY_SECONDS
            ]));
    }

    /**
     * Create an error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @return ResponseInterface JSON error response
     */
    private function errorResponse(string $message, int $statusCode = 400): ResponseInterface
    {
        return (new Response())
            ->withStatus($statusCode)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'success' => false,
                'error' => $message,
                'timestamp' => date(self::DATETIME_FORMAT)
            ]));
    }
}
