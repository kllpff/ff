<?php

namespace FF\Framework\Http;

/**
 * Request - HTTP Request Class
 * 
 * Encapsulates HTTP request data including method, URI, headers, and body.
 */
class Request
{
    /**
     * The request method (GET, POST, etc.)
     * 
     * @var string
     */
    protected string $method;

    /**
     * The request URI
     * 
     * @var string
     */
    protected string $uri;

    /**
     * Request headers
     * 
     * @var array
     */
    protected array $headers = [];

    /**
     * Query string parameters
     * 
     * @var array
     */
    protected array $query = [];

    /**
     * POST data
     * 
     * @var array
     */
    protected array $post = [];

    /**
     * Uploaded files
     * 
     * @var array
     */
    protected array $files = [];

    /**
     * Server variables
     * 
     * @var array
     */
    protected array $server = [];

    /**
     * Cookies
     * 
     * @var array
     */
    protected array $cookies = [];

    /**
     * Create a new Request instance
     * 
     * @param string $method The request method
     * @param string $uri The request URI
     * @param array $headers Request headers
     * @param array $query Query parameters
     * @param array $post POST data
     * @param array $files Uploaded files
     * @param array $server Server variables
     * @param array $cookies Cookies
     */
    public function __construct(
        string $method = 'GET',
        string $uri = '/',
        array $headers = [],
        array $query = [],
        array $post = [],
        array $files = [],
        array $server = [],
        array $cookies = []
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->headers = $headers;
        $this->query = $query;
        $this->post = $post;
        $this->files = $files;
        $this->server = $server;
        $this->cookies = $cookies;
    }

    /**
     * Create a request from PHP globals
     * 
     * @return self
     */
    public static function capture(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Parse query string
        parse_str($_SERVER['QUERY_STRING'] ?? '', $query);

        return new self(
            $method,
            $uri,
            self::getHeaders(),
            $query,
            $_POST,
            $_FILES,
            $_SERVER,
            $_COOKIE
        );
    }

    /**
     * Get all request headers
     * 
     * @return array
     */
    private static function getHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerKey = str_replace('HTTP_', '', $key);
                $headerKey = str_replace('_', '-', $headerKey);
                $headerKey = strtolower($headerKey);
                $headers[$headerKey] = $value;
            }
        }

        return $headers;
    }

    /**
     * Get the request method
     * 
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the request URI
     * 
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get a query parameter
     * 
     * @param string $key The parameter key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get a POST parameter
     * 
     * @param string $key The parameter key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get input from either GET or POST
     * 
     * @param string $key The parameter key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get a header value
     * 
     * @param string $key The header key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function header(string $key, $default = null)
    {
        $key = strtolower(str_replace('_', '-', $key));
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get the client IP address
     * 
     * @return string
     */
    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Check if request method matches
     * 
     * @param string $method The method to check
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    /**
     * Check if this is a GET request
     * 
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    /**
     * Check if this is a POST request
     * 
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * Check if this is a PUT request
     * 
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    /**
     * Check if this is a DELETE request
     * 
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    /**
     * Get all query parameters
     * 
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post);
    }

    /**
     * Get only specific keys from input
     * 
     * @param array $keys The keys to retrieve
     * @return array
     */
    public function only(array $keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * Get all input except specific keys
     * 
     * @param array $keys The keys to exclude
     * @return array
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        return array_diff_key($all, array_flip($keys));
    }
}
