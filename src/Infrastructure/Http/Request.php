<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

/**
 * Class Request
 *
 * Represents an HTTP request in the application.
 */
class Request implements RequestInterface
{
    private string $method;
    private string $path;
    private array  $headers     = [];
    private array  $parsedBody  = [];
    private array  $attributes  = [];
    private string $body;

    /**
     * Private constructor to enforce the use of fromGlobals() method.
     */
    private function __construct() {}

    /**
     * Create a Request instance from global PHP variables.
     *
     * This method populates the request properties based on the current
     * PHP environment, including the request method, path, headers,
     * parsed body, and raw body content.
     *
     * @return self A new Request instance populated with global data.
     */
    public static function fromGlobals(): self
    {
        $req = new self();
        $req->method      = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $req->path        = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $req->parsedBody  = $_POST;
        $req->body        = file_get_contents('php://input') ?: '';

        // Extract HTTP_ headers
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $req->headers[$name] = (string)$value;
            }
        }

        return $req;
    }

    /**
     * Get the HTTP method of the request (e.g., GET, POST).
     *
     * @return string The HTTP method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the path of the request (e.g., /api/resource).
     *
     * @return string The request path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get a specific header value by name.
     *
     * @param string $name The name of the header (case-insensitive).
     * @return string|null The header value or null if not found.
     */
    public function getHeader(string $name): ?string
    {
        $lower = strtolower($name);
        return $this->headers[$lower] ?? null;
    }

    /**
     * Get the parsed body of the request (e.g., form data, JSON).
     *
     * @return array The parsed body as an associative array.
     */
    public function getParsedBody(): array
    {
        return $this->parsedBody;
    }

    /**
     * Set an attribute on the request.
     *
     * @param string $key The attribute key.
     * @param mixed  $value The attribute value.
     * @return static A new instance with the attribute set.
     */
    public function withAttribute(string $key, mixed $value): static
    {
        $clone = clone $this;
        $clone->attributes[$key] = $value;
        return $clone;
    }

    /**
     * Get an attribute value by key.
     *
     * @param string $key The attribute key.
     * @param mixed  $default Default value if the attribute is not set.
     * @return mixed The attribute value or the default value if not set.
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
