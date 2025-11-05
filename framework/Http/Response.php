<?php

namespace FF\Framework\Http;

/**
 * Response - HTTP Response Class
 * 
 * Encapsulates HTTP response data including status code, headers, and content.
 */
class Response
{
    /**
     * The response content
     * 
     * @var string
     */
    protected string $content = '';

    /**
     * The HTTP status code
     * 
     * @var int
     */
    protected int $statusCode = 200;

    /**
     * Response headers
     * 
     * @var array
     */
    protected array $headers = [];

    /**
     * Create a new Response instance
     * 
     * @param string $content The response content
     * @param int $statusCode The HTTP status code
     * @param array $headers Response headers
     */
    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Set the response content
     * 
     * @param string $content The content
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get the response content
     * 
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set the HTTP status code
     * 
     * @param int $code The status code
     * @return self
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Get the HTTP status code
     * 
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Add a header to the response
     * 
     * @param string $key The header name
     * @param string $value The header value
     * @return self
     */
    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Add multiple headers at once
     * 
     * @param array $headers Headers to add
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
        return $this;
    }

    /**
     * Get all response headers
     * 
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Return a JSON response
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $status HTTP status code
     * @return self
     */
    public function json($data, int $status = 200): self
    {
        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'application/json';
        $this->content = json_encode($data);
        return $this;
    }

    /**
     * Return a redirect response
     * 
     * @param string $url The URL to redirect to
     * @param int $status HTTP status code (default 302)
     * @return self
     */
    public function redirect(string $url, int $status = 302): self
    {
        $this->statusCode = $status;
        $this->headers['Location'] = $url;
        return $this;
    }

    /**
     * Send the response to the client
     * 
     * @return void
     */
    public function send(): void
    {
        // Set HTTP status code
        http_response_code($this->statusCode);

        // Send headers
        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }

        // Output content
        echo $this->content;
    }
}
