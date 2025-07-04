<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Validation\FieldValidator;
use App\Infrastructure\Validation\ValidationException;

/**
 * ValidationMiddleware
 *
 * Middleware that validates request bodies against defined rules.
 * It checks the request method and path to determine which validation rules to apply.
 */
class ValidationMiddleware implements MiddlewareInterface
{
    /**
     * @param array<string, FieldValidator[]> $validatorsPerRoute
     *   e.g. ['POST /user' => [new FieldValidator('name')->addRule(...), ...]]
     */
    public function __construct(private array $validatorsPerRoute) {}

    /**
     * Process the request and validate the body against defined rules.
     *
     * @param RequestInterface $req The incoming request
     * @param RequestHandlerInterface $next The next handler in the middleware chain
     * @return ResponseInterface The response after processing
     * @throws ValidationException If validation fails
     */
    public function process(RequestInterface $req, RequestHandlerInterface $next): ResponseInterface
    {
        $key = $req->getMethod() . ' ' . $req->getPath();
        if (isset($this->validatorsPerRoute[$key])) {
            $errors = [];
            $body   = $req->getParsedBody();

            /** @var FieldValidator $validator */
            foreach ($this->validatorsPerRoute[$key] as $validator) {
                $field = $validator->getFieldName();
                $value = $body[$field] ?? null;
                $err   = $validator->validate($value);
                if ($err !== null) {
                    $errors[] = $err;
                }
            }

            if (!empty($errors)) {
                // Return 422 Unprocessable Entity
                throw new ValidationException($errors);
            }
        }

        return $next->handle($req);
    }
}
