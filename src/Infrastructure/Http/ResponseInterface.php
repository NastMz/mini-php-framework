<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

/**
 * Interface ResponseInterface
 *
 * Represents an HTTP response in the application.
 */
interface ResponseInterface
{
    /**
     * Set the HTTP status code for the response.
     *
     * @param int $code The HTTP status code (e.g., 200, 404).
     * @return static A new instance with the status code set.
     */
    public function withStatus(int $code): static;

    /**
     * Set a header for the response.
     *
     * @param string $name The name of the header (case-insensitive).
     * @param string $value The value of the header.
     * @return static A new instance with the header set.
     */
    public function withHeader(string $name, string $value): static;

    /**
     * Set the body content of the response.
     *
     * @param string $body The body content to send in the response.
     * @return static A new instance with the body set.
     */
    public function write(string $body): static;

    /**
     * Set the response body as JSON.
     *
     * @param mixed $data The data to encode as JSON.
     * @param int $status The HTTP status code (optional).
     * @return static A new instance with JSON body and appropriate header.
     */
    public function withJson($data, int $status = 200): static;

    /**
     * Send the response to the client.
     *
     * This method outputs the HTTP headers and body content to the client.
     * It should be called after all headers and body content have been set.
     *
     * @return void
     */
    public function send(): void;

    /**
     * Get the body content of the response.
     *
     * @return string The body content of the response.
     */
    public function getBody(): string;
}
