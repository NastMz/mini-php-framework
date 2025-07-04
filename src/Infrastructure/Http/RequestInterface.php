<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

/**
 * Interface RequestInterface
 *
 * Represents an HTTP request in the application.
 */
interface RequestInterface
{
    /**
     * Get the HTTP method of the request (e.g., GET, POST).
     *
     * @return string The HTTP method.
     */
    public function getMethod(): string;

    /**
     * Get the path of the request (e.g., /api/resource).
     *
     * @return string The request path.
     */
    public function getPath(): string;

    /**
     * Get a specific header value by name.
     *
     * @param string $name The name of the header (case-insensitive).
     * @return string|null The header value or null if not found.
     */
    public function getHeader(string $name): ?string;

    /**
     * Get the parsed body of the request (e.g., form data, JSON).
     *
     * @return array The parsed body as an associative array.
     */
    public function getParsedBody(): array;

    /**
     * Set an attribute on the request.
     *
     * @param string $key The attribute key.
     * @param mixed  $value The attribute value.
     * @return static A new instance with the attribute set.
     */
    public function withAttribute(string $key, mixed $value): static;

    /**
     * Get an attribute value by key.
     *
     * @param string $key The attribute key.
     * @param mixed  $default Default value if the attribute is not set.
     * @return mixed The attribute value or the default value if not set.
     */
    public function getAttribute(string $key, mixed $default = null): mixed;
}
