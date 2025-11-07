<?php

namespace FF\Http;

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
        $normalizedKey = $this->normalizeHeaderName($key);
        $this->headers[$normalizedKey] = $this->sanitizeHeaderValue($value);
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
     * Get a single response header by name
     *
     * @param string $key The header name
     * @return string|null The header value or null if not set
     */
    public function getHeader(string $key): ?string
    {
        $normalizedKey = $this->normalizeHeaderName($key);
        return $this->headers[$normalizedKey] ?? null;
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
        $this->header('Content-Type', 'application/json');
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
        $this->header('Location', $this->sanitizeRedirectUrl($url));
        return $this;
    }

    /**
     * Return a file download response.
     *
     * Sets appropriate headers and loads file contents into response body.
     *
     * @param string $path Absolute filesystem path to the file
     * @param string|null $filename Optional filename for the browser
     * @param array $headers Additional headers to include
     * @return self
     */
    public function download(string $path, ?string $filename = null, array $headers = []): self
    {
        $trimmedPath = trim($path);
        if ($trimmedPath === '' || preg_match("/[\r\n]/", $trimmedPath)) {
            throw new \InvalidArgumentException('Invalid file path.');
        }

        if (!is_file($trimmedPath) || !is_readable($trimmedPath)) {
            $this->setStatusCode(404);
            $this->content = '';
            return $this;
        }

        $name = $filename !== null && $filename !== '' ? $filename : basename($trimmedPath);
        $name = trim(str_replace(["\r", "\n"], '', $name));

        // Determine content type
        $mime = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $detected = @mime_content_type($trimmedPath);
            if ($detected && is_string($detected)) {
                $mime = $detected;
            }
        }

        $this->header('Content-Type', $mime);
        $this->header('Content-Disposition', 'attachment; filename="' . $name . '"');

        $size = @filesize($trimmedPath);
        if ($size !== false) {
            $this->header('Content-Length', (string)$size);
        }

        if (!empty($headers)) {
            $this->withHeaders($headers);
        }

        $this->setStatusCode(200);
        $contents = @file_get_contents($trimmedPath);
        if ($contents === false) {
            // If reading failed, return 500
            $this->setStatusCode(500);
            $this->content = '';
            return $this;
        }

        $this->content = $contents;
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

    /**
     * Normalize header names to Title-Case and validate characters.
     */
    protected function normalizeHeaderName(string $name): string
    {
        $trimmed = trim($name);

        if ($trimmed === '' || preg_match('/[^A-Za-z0-9\-]/', $trimmed)) {
            throw new \InvalidArgumentException("Invalid header name: {$name}");
        }

        $parts = explode('-', strtolower($trimmed));
        $parts = array_map(static fn ($part) => ucfirst($part), $parts);

        return implode('-', $parts);
    }

    /**
     * Ensure header value does not contain CRLF sequences.
     */
    protected function sanitizeHeaderValue(string $value): string
    {
        if (preg_match("/[\r\n]/", $value)) {
            throw new \InvalidArgumentException('Header values must not contain CR or LF characters.');
        }

        return trim($value);
    }

    /**
     * Sanitize redirect URL to prevent response splitting.
     */
    protected function sanitizeRedirectUrl(string $url): string
    {
        $trimmed = trim($url);

        if ($trimmed === '' || preg_match("/[\r\n]/", $trimmed)) {
            throw new \InvalidArgumentException('Invalid redirect URL.');
        }

        return $trimmed;
    }
}
