<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

/**
 * Class Response
 *
 * Represents an HTTP response in the application.
 */
class Response implements ResponseInterface
{
    private int    $status  = 200;
    private array  $headers = [];
    private string $body    = '';

    /**
     * Private constructor to enforce the use of withStatus(), withHeader(), and write() methods.
     */
    public function withStatus(int $code): static
    {
        $clone = clone $this;
        $clone->status = $code;
        return $clone;
    }

    /**
     * Set a header for the response.
     *
     * @param string $name  The name of the header (case-insensitive).
     * @param string $value The value of the header.
     * @return static A new instance with the header set.
     */
    public function withHeader(string $name, string $value): static
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    /**
     * Set the body content of the response.
     *
     * @param string $body The body content to send in the response.
     * @return static A new instance with the body set.
     */
    public function write(string $body): static
    {
        $clone = clone $this;
        $clone->body .= $body;
        return $clone;
    }

    /**
     * Set the response body as JSON.
     *
     * @param mixed $data The data to encode as JSON.
     * @param int $status The HTTP status code (optional).
     * @return static A new instance with JSON body and appropriate header.
     */
    public function withJson($data, int $status = 200): static
    {
        $clone = clone $this;
        $clone->status = $status;
        $clone->headers['Content-Type'] = 'application/json';
        $clone->body = json_encode($data);
        return $clone;
    }

    /**
     * Send the response to the client.
     *
     * This method outputs the HTTP headers and body content to the client.
     * It should be called after all headers and body content have been set.
     *
     * @return void
     */
    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
    }

    /**
     * Get the body content of the response.
     *
     * @return string The body content of the response.
     */
    public function getBody(): string
    {
        return $this->body;
    }
}
