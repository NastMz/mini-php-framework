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
}
