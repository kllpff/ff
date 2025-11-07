<?php

namespace FF\Http;

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
     * Raw request body cache
     *
     * @var string|null
     */
    protected ?string $rawBody = null;

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
     * Convenience: get request method
     *
     * @return string
     */
    public function method(): string
    {
        return $this->getMethod();
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
     * Get a server variable from the request context.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getServer(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
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

    /**
     * Get path portion of the URI
     *
     * @return string
     */
    public function getPath(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        return $path !== null ? $path : '/';
    }

    /**
     * Convenience: get path portion of current URL
     *
     * @return string
     */
    public function path(): string
    {
        return $this->getPath();
    }

    /**
     * Build absolute URL for current request (without query string).
     *
     * @return string
     */
    public function url(): string
    {
        // Determine scheme
        $scheme = $this->isSecure() ? 'https' : 'http';
        $forwardedProto = strtolower((string)$this->header('x-forwarded-proto', ''));
        if ($forwardedProto === 'https' || $forwardedProto === 'http') {
            $scheme = $forwardedProto;
        }

        // Determine host
        $host = (string)$this->header('x-forwarded-host', '');
        if ($host === '') {
            $host = (string)$this->header('host', '');
        }
        if ($host === '') {
            $host = (string)($this->getServer('HTTP_HOST') ?? 'localhost');
        }

        // Determine port
        $port = (int)($this->getServer('SERVER_PORT') ?? 0);
        $defaultPort = $scheme === 'https' ? 443 : 80;
        $authority = $host;
        if ($port !== 0 && $port !== $defaultPort && strpos($host, ':') === false) {
            $authority = $host . ':' . $port;
        }

        return $scheme . '://' . $authority . $this->getPath();
    }

    /**
     * Build absolute URL including query string for current request.
     *
     * @return string
     */
    public function fullUrl(): string
    {
        $url = $this->url();
        if (!empty($this->query)) {
            $qs = http_build_query($this->query);
            if ($qs !== '') {
                $url .= '?' . $qs;
            }
        }
        return $url;
    }

    /**
     * Determine if request is an AJAX call.
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return strtolower((string)$this->header('x-requested-with', '')) === 'xmlhttprequest';
    }

    /**
     * Determine if the request is over HTTPS
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        $forwardedProto = strtolower((string)$this->header('x-forwarded-proto', ''));
        if ($forwardedProto === 'https') {
            return true;
        }

        $https = $this->getServer('HTTPS');
        if ($https && ($https === 'on' || $https === '1')) {
            return true;
        }

        $port = (int)($this->getServer('SERVER_PORT') ?? 0);
        return $port === 443;
    }

    /**
     * Determine if the client expects a JSON response
     *
     * @return bool
     */
    public function wantsJson(): bool
    {
        $accept = strtolower((string)$this->header('accept', ''));
        $requestedWith = strtolower((string)$this->header('x-requested-with', ''));
        return strpos($accept, 'json') !== false || $requestedWith === 'xmlhttprequest';
    }

    /**
     * Get the raw request body.
     *
     * @return string
     */
    public function getBody(): string
    {
        if ($this->rawBody !== null) {
            return $this->rawBody;
        }

        $input = file_get_contents('php://input');
        $this->rawBody = $input !== false ? $input : '';
        return $this->rawBody;
    }

    /**
     * Parse JSON request body and return associative array.
     * Falls back to POST form data when JSON body is not available.
     *
     * @return array
     */
    public function json(): array
    {
        $raw = $this->getBody();
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return $this->post;
    }

    /**
     * Get raw uploaded files array (from $_FILES).
     *
     * @return array
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * Get an uploaded file by form field name.
     * Supports single-file fields; for multi-file inputs returns the first item.
     *
     * @param string $key
     * @return UploadedFile|null
     */
    public function file(string $key): ?UploadedFile
    {
        if (!array_key_exists($key, $this->files)) {
            return null;
        }

        $entry = $this->files[$key];

        // Handle multi-file inputs (name[])
        if (is_array($entry['name'] ?? null)) {
            $first = [
                'name' => $entry['name'][0] ?? '',
                'type' => $entry['type'][0] ?? null,
                'tmp_name' => $entry['tmp_name'][0] ?? '',
                'error' => $entry['error'][0] ?? UPLOAD_ERR_NO_FILE,
                'size' => $entry['size'][0] ?? 0,
            ];
            $file = new UploadedFile($first);
            return $file->isValid() ? $file : null;
        }

        $file = new UploadedFile($entry);
        return $file->isValid() ? $file : null;
    }

    /**
     * Validate request input (including uploaded files when present).
     * Throws ValidationException on failure.
     *
     * @param array $rules
     * @param array $messages
     * @return array The validated data subset
     * @throws \FF\Exceptions\ValidationException
     */
    public function validate(array $rules, array $messages = []): array
    {
        // Merge input and attached file objects for fields present in rules
        $data = $this->all();
        foreach ($rules as $field => $fieldRules) {
            $uploaded = $this->file($field);
            if ($uploaded !== null) {
                $data[$field] = $uploaded;
            }
        }

        $validator = new \FF\Validation\Validator($data, $rules, $messages);
        if (!$validator->validate()) {
            throw new \FF\Exceptions\ValidationException($validator->getErrors());
        }

        // Return only fields specified in rules
        $keys = array_keys($rules);
        return array_intersect_key($data, array_flip($keys));
    }
}
