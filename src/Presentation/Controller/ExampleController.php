<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Security\AppSecurity;
use App\Infrastructure\Config\EnvLoader;

/**
 * ExampleController
 *
 * This controller provides example endpoints for application information,
 * data encryption/decryption, and CSRF token generation.
 */
class ExampleController
{
    /**
     * Show application information such as name, environment, debug mode, and app key status.
     *
     * @param RequestInterface $request The HTTP request object.
     * @param ResponseInterface $response The HTTP response object.
     * @return ResponseInterface The response containing application information in JSON format.
     */
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
     *
     * @param RequestInterface $request The HTTP request object containing the data to encrypt.
     * @param ResponseInterface $response The HTTP response object.
     * @return ResponseInterface The response containing the encrypted data in JSON format.
     */
    public function encryptData(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getBody();
        
        if (empty($data)) {
            return $response->withJson(['error' => 'No data provided'], 400);
        }
        
        try {
            $encrypted = AppSecurity::encrypt($data);
            return $response->withJson(['encrypted' => $encrypted]);
        } catch (\Exception $e) {
            return $response->withJson(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Decrypt data using the application key.
     *
     * @param RequestInterface $request The HTTP request object containing the encrypted data.
     * @param ResponseInterface $response The HTTP response object.
     * @return ResponseInterface The response containing the decrypted data in JSON format.
     */
    public function decryptData(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $encryptedData = $request->getBody();
        
        if (empty($encryptedData)) {
            return $response->withJson(['error' => 'No encrypted data provided'], 400);
        }
        
        try {
            $decrypted = AppSecurity::decrypt($encryptedData);
            return $response->withJson(['decrypted' => $decrypted]);
        } catch (\Exception $e) {
            return $response->withJson(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Generate a CSRF token.
     *
     * @param RequestInterface $request The HTTP request object.
     * @param ResponseInterface $response The HTTP response object.
     * @return ResponseInterface The response containing the CSRF token in JSON format.
     */
    public function generateCsrfToken(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $token = AppSecurity::generateCsrfToken();
        return $response->withJson(['csrf_token' => $token]);
    }
}
