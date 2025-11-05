<?php

namespace FF\Framework\Http;

use Closure;
use FF\Framework\Core\Container;

/**
 * Route - HTTP Route Definition
 * 
 * Represents a single HTTP route with method, URI, action, and middleware.
 */
class Route
{
    /**
     * The HTTP method(s)
     * 
     * @var array
     */
    protected array $methods = [];

    /**
     * The route URI pattern
     * 
     * @var string
     */
    protected string $uri;

    /**
     * The route action (controller or closure)
     * 
     * @var mixed
     */
    protected $action;

    /**
     * Middleware for this route
     * 
     * @var array
     */
    protected array $middleware = [];

    /**
     * The route name
     * 
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * Route parameters
     * 
     * @var array
     */
    protected array $parameters = [];

    /**
     * Create a new Route instance
     * 
     * @param array $methods The HTTP methods
     * @param string $uri The route URI
     * @param mixed $action The route action
     */
    public function __construct(array $methods, string $uri, $action)
    {
        $this->methods = array_map('strtoupper', $methods);
        $this->uri = $uri;
        $this->action = $action;
    }

    /**
     * Add middleware to this route
     * 
     * @param string|array $middleware The middleware to add
     * @return self
     */
    public function middleware($middleware): self
    {
        if (is_string($middleware)) {
            $this->middleware[] = $middleware;
        } else if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        }
        return $this;
    }

    /**
     * Set the name for this route
     * 
     * @param string $name The route name
     * @return self
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the HTTP methods
     * 
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Get the route URI
     * 
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get the route action
     * 
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get the route middleware
     * 
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Get the route name
     * 
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get route parameters (from matching)
     * 
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set route parameters
     * 
     * @param array $parameters
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }
}

/**
 * Router - HTTP Router
 * 
 * Handles route registration, matching, and dispatching to controllers.
 */
class Router
{
    /**
     * The container instance
     * 
     * @var Container|null
     */
    protected ?Container $app = null;

    /**
     * All registered routes
     * 
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * Named routes for URL generation
     * 
     * @var array
     */
    protected array $namedRoutes = [];

    /**
     * Current group stack
     * 
     * @var array
     */
    protected array $groupStack = [];

    /**
     * The currently matched route
     * 
     * @var Route|null
     */
    protected ?Route $currentRoute = null;

    /**
     * Create a new Router instance
     * 
     * @param Container|null $app The container instance
     */
    public function __construct(Container $app = null)
    {
        $this->app = $app;
    }

    /**
     * Set the container instance
     * 
     * @param Container $app The container
     * @return self
     */
    public function setContainer(Container $app): self
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Register a GET route
     * 
     * @param string $uri The route URI
     * @param mixed $action The route action
     * @return Route
     */
    public function get(string $uri, $action): Route
    {
        return $this->addRoute(['GET'], $uri, $action);
    }

    /**
     * Register a POST route
     * 
     * @param string $uri The route URI
     * @param mixed $action The route action
     * @return Route
     */
    public function post(string $uri, $action): Route
    {
        return $this->addRoute(['POST'], $uri, $action);
    }

    /**
     * Register a PUT route
     * 
     * @param string $uri The route URI
     * @param mixed $action The route action
     * @return Route
     */
    public function put(string $uri, $action): Route
    {
        return $this->addRoute(['PUT'], $uri, $action);
    }

    /**
     * Register a DELETE route
     * 
     * @param string $uri The route URI
     * @param mixed $action The route action
     * @return Route
     */
    public function delete(string $uri, $action): Route
    {
        return $this->addRoute(['DELETE'], $uri, $action);
    }

    /**
     * Register a PATCH route
     * 
     * @param string $uri The route URI
     * @param mixed $action The route action
     * @return Route
     */
    public function patch(string $uri, $action): Route
    {
        return $this->addRoute(['PATCH'], $uri, $action);
    }

    /**
     * Register a route for any HTTP method
     * 
     * @param string $uri The route URI
     * @param mixed $action The route action
     * @return Route
     */
    public function any(string $uri, $action): Route
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $uri, $action);
    }

    /**
     * Register a route for specific HTTP methods
     * 
     * @param array $methods The HTTP methods
     * @param string $uri The route URI
     * @param mixed $action The route action
     * @return Route
     */
    public function match(array $methods, string $uri, $action): Route
    {
        return $this->addRoute($methods, $uri, $action);
    }

    /**
     * Add a route to the collection
     * 
     * @param array $methods The HTTP methods
     * @param string $uri The route URI
     * @param mixed $action The route action
     * @return Route
     */
    protected function addRoute(array $methods, string $uri, $action): Route
    {
        // Apply group prefix and middleware
        $uri = $this->applyGroupPrefix($uri);

        $route = new Route($methods, $uri, $action);

        // Apply group middleware
        $this->applyGroupMiddleware($route);

        $this->routes[] = $route;

        return $route;
    }

    /**
     * Create a route group with shared attributes
     * 
     * @param array $attributes Group attributes (prefix, middleware, namespace)
     * @param Closure $callback Callback to register routes in group
     * @return void
     */
    public function group(array $attributes, Closure $callback): void
    {
        array_push($this->groupStack, $attributes);
        call_user_func($callback, $this);
        array_pop($this->groupStack);
    }

    /**
     * Apply group prefix to a URI
     * 
     * @param string $uri The original URI
     * @return string The prefixed URI
     */
    protected function applyGroupPrefix(string $uri): string
    {
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $uri = '/' . trim($group['prefix'], '/') . '/' . trim($uri, '/');
            }
        }
        return '/' . trim($uri, '/');
    }

    /**
     * Apply group middleware to a route
     * 
     * @param Route $route The route
     * @return void
     */
    protected function applyGroupMiddleware(Route $route): void
    {
        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $middleware = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $route->middleware($middleware);
            }
        }
    }

    /**
     * Dispatch a request to find and execute the matching route
     * 
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     */
    public function dispatch(Request $request): Response
    {
        $route = $this->findRoute($request);

        if (!$route) {
            throw new \Exception('No routes matched for: ' . $request->getMethod() . ' ' . $request->getUri());
        }

        $this->currentRoute = $route;

        return $this->runRoute($route, $request);
    }

    /**
     * Find a matching route for the request
     * 
     * @param Request $request The HTTP request
     * @return Route|null The matching route or null
     */
    public function findRoute(Request $request): ?Route
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->routes as $route) {
            if (!in_array($method, $route->getMethods())) {
                continue;
            }

            if ($this->matchUri($route->getUri(), $uri, $route)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Match a route URI pattern against a request URI
     * 
     * @param string $pattern The route pattern
     * @param string $uri The request URI
     * @param Route $route The route object (to store parameters)
     * @return bool True if pattern matches
     */
    protected function matchUri(string $pattern, string $uri, Route $route): bool
    {
        // Exact match
        if ($pattern === $uri) {
            return true;
        }

        // Pattern matching with parameters
        $patternParts = array_filter(explode('/', trim($pattern, '/')));
        $uriParts = array_filter(explode('/', trim($uri, '/')));

        if (count($patternParts) !== count($uriParts)) {
            return false;
        }

        $parameters = [];

        foreach ($patternParts as $key => $patternPart) {
            if (preg_match('/^\{(\w+)\??\}$/', $patternPart, $matches)) {
                // Parameter placeholder {id} or {id?}
                $parameters[$matches[1]] = $uriParts[$key];
            } else if ($patternPart !== $uriParts[$key]) {
                // Literal mismatch
                return false;
            }
        }

        $route->setParameters($parameters);
        return true;
    }

    /**
     * Run the matched route and return response
     * 
     * @param Route $route The matched route
     * @param Request $request The HTTP request
     * @return Response The response
     */
    public function runRoute(Route $route, Request $request): Response
    {
        $action = $route->getAction();
        $parameters = $route->getParameters();

        // Handle closure action
        if ($action instanceof Closure) {
            $response = call_user_func_array($action, array_values($parameters));
        } else if (is_string($action)) {
            // Handle controller@method action
            $response = $this->callControllerAction($action, $parameters);
        } else {
            throw new \Exception('Invalid route action');
        }

        // Smart response handling
        if (!($response instanceof Response)) {
            $response = $this->makeSmartResponse($action, $response);
        }

        return $response;
    }

    /**
     * Convert controller response to proper Response object
     * 
     * @param string $action The controller@method
     * @param mixed $response The controller response
     * @return Response
     */
    protected function makeSmartResponse(string $action, $response): Response
    {
        // API Controllers: Auto-convert arrays to JSON
        if (strpos($action, 'Api\\') !== false || strpos($action, '\\Api\\') !== false) {
            return response()->json($response);
        }

        // Web Controllers: Handle view and string responses
        if (is_array($response)) {
            // If array is returned, treat as view data
            return new Response((string)$response, 200, ['Content-Type' => 'text/html']);
        }

        if (is_string($response)) {
            return new Response($response, 200, ['Content-Type' => 'text/html']);
        }

        // Default: convert to string
        return new Response((string)$response, 200);
    }

    /**
     * Call a controller action
     * 
     * @param string $action The controller@method string
     * @param array $parameters Route parameters
     * @return mixed The controller action result
     */
    protected function callControllerAction(string $action, array $parameters)
    {
        [$controllerClass, $method] = explode('@', $action);

        // Resolve controller from container
        $controller = $this->app->make($controllerClass);

        // Call the method with parameters
        $result = call_user_func_array([$controller, $method], array_values($parameters));

        // Auto-detect if this is an API route and response is array
        if (is_array($result) && (strpos($action, 'Api\\') !== false || strpos($action, '\\Api\\') !== false)) {
            $response = new Response();
            return $response->json($result);
        }

        return $result;
    }

    /**
     * Get the currently matched route
     * 
     * @return Route|null
     */
    public function getCurrentRoute(): ?Route
    {
        return $this->currentRoute;
    }

    /**
     * Register a named route for URL generation
     * 
     * @param string $name The route name
     * @param Route $route The route
     * @return void
     */
    public function registerNamedRoute(string $name, Route $route): void
    {
        $this->namedRoutes[$name] = $route;
    }

    /**
     * Generate a URL for a named route
     * 
     * @param string $name The route name
     * @param array $parameters Route parameters
     * @return string The generated URL
     */
    public function url(string $name, array $parameters = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Named route '{$name}' not found");
        }

        $route = $this->namedRoutes[$name];
        $uri = $route->getUri();

        // Replace parameters in URI
        foreach ($parameters as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
            $uri = str_replace('{' . $key . '?}', $value, $uri);
        }

        return $uri;
    }

    /**
     * Get all registered routes
     * 
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
