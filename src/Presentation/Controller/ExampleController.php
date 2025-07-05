<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Security\AppSecurity;
use App\Infrastructure\Security\EncryptionException;
use App\Infrastructure\Config\EnvLoader;
use App\Infrastructure\Routing\Attributes\Route;
use App\Infrastructure\Routing\Attributes\Controller;
use App\Infrastructure\Routing\HttpMethod;

/**
 * ExampleController
 *
 * This controller provides example endpoints for application information,
 * data encryption/decryption, and CSRF token generation.
 */
#[Controller(prefix: '/examples')]
class ExampleController
{
    /**
     * Show application information such as name, environment, debug mode, and app key status.
     */
    #[Route(HttpMethod::GET, '/app-info', name: 'examples.app-info')]
    public function showAppInfo(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $appName = EnvLoader::get('APP_NAME', 'Default App');
        $appEnv = EnvLoader::get('APP_ENV', 'production');
        $isDebug = EnvLoader::get('APP_DEBUG', 'false') === 'true';
        
        $info = [
            'app_name' => $appName,
            'environment' => $appEnv,
            'debug_mode' => $isDebug ? 'ON' : 'OFF',
            'has_app_key' => !empty(EnvLoader::get('APP_KEY')),
        ];
        
        return $response->withJson($info);
    }
    
    /**
     * Encrypt data using the application key.
     */
    #[Route(HttpMethod::POST, '/encrypt', name: 'examples.encrypt')]
    public function encryptData(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getBody();
        
        if (empty($data)) {
            return $response->withJson(['error' => 'No data provided'], 400);
        }
        
        try {
            $encrypted = AppSecurity::encrypt($data);
            return $response->withJson(['encrypted' => $encrypted]);
        } catch (EncryptionException $e) {
            // Let the middleware handle this as 400 Bad Request
            throw $e;
        } catch (\Exception $e) {
            return $response->withJson(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Decrypt data using the application key.
     */
    #[Route(HttpMethod::POST, '/decrypt', name: 'examples.decrypt')]
    public function decryptData(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $encryptedData = $request->getBody();
        
        if (empty($encryptedData)) {
            return $response->withJson(['error' => 'No encrypted data provided'], 400);
        }
        
        try {
            $decrypted = AppSecurity::decrypt($encryptedData);
            return $response->withJson(['decrypted' => $decrypted]);
        } catch (EncryptionException $e) {
            // Let the middleware handle this as 400 Bad Request
            throw $e;
        } catch (\Exception $e) {
            return $response->withJson(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Generate a CSRF token.
     */
    #[Route(HttpMethod::GET, '/csrf-token', name: 'examples.csrf-token')]
    public function generateCsrfToken(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $token = AppSecurity::generateCsrfToken();
        return $response->withJson(['csrf_token' => $token]);
    }
}
