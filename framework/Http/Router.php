<?php

namespace FF\Http;

use Closure;
use FF\Core\Container;
use FF\Http\Middleware\MiddlewareInterface;

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
     * Parameter constraints (regex patterns)
     *
     * @var array<string,string>
     */
    protected array $constraints = [];

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
        if (is_array($middleware)) {
            foreach ($middleware as $entry) {
                $this->pushMiddleware($entry);
            }
            return $this;
        }

        $this->pushMiddleware($middleware);
        return $this;
    }

    /**
     * Normalize middleware definition before storing.
     *
     * @param mixed $middleware
     * @return void
     */
    protected function pushMiddleware($middleware): void
    {
        if ($middleware === null) {
            return;
        }

        $this->middleware[] = $middleware;
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

    /**
     * Define parameter constraints for the route.
     *
     * Examples:
     *  $route->where('id', '[0-9]+');
     *  $route->where(['slug' => '[a-z0-9-]+', 'year' => '\\d{4}']);
     *
     * @param string|array $key Parameter name or array of name=>regex
     * @param string|null $pattern Regex pattern (without delimiters) when $key is string
     * @return self
     */
    public function where($key, ?string $pattern = null): self
    {
        if (is_array($key)) {
            foreach ($key as $param => $regex) {
                if (is_string($param) && is_string($regex)) {
                    $this->constraints[$param] = $regex;
                }
            }
        } elseif (is_string($key) && is_string($pattern)) {
            $this->constraints[$key] = $pattern;
        }
        return $this;
    }

    /**
     * Get parameter constraints
     *
     * @return array<string,string>
     */
    public function getConstraints(): array
    {
        return $this->constraints;
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

        // Pattern matching with parameters (supports optional {param?})
        $patternParts = array_values(array_filter(explode('/', trim($pattern, '/'))));
        $uriParts = array_values(array_filter(explode('/', trim($uri, '/'))));

        $parameters = [];

        $i = 0; // index for patternParts
        $j = 0; // index for uriParts
        $patternCount = count($patternParts);
        $uriCount = count($uriParts);

        while ($i < $patternCount) {
            $patternPart = $patternParts[$i];

            if (preg_match('/^\{(\w+)(\?)?\}$/', $patternPart, $matches)) {
                $paramName = $matches[1];
                $isOptional = ($matches[2] ?? '') === '?';

                if ($j < $uriCount) {
                    $parameters[$paramName] = $uriParts[$j];
                    $j++;
                } else if ($isOptional) {
                    // Optional parameter missing
                    $parameters[$paramName] = null;
                } else {
                    // Required parameter missing
                    return false;
                }
            } else {
                // Literal segment must match exactly
                if ($j >= $uriCount || $patternPart !== $uriParts[$j]) {
                    return false;
                }
                $j++;
            }

            $i++;
        }

        // If there are extra URI segments beyond the pattern, no match
        if ($j !== $uriCount) {
            return false;
        }

        // Validate parameter constraints (if defined)
        $constraints = $route->getConstraints();
        foreach ($constraints as $param => $regex) {
            // Only validate if parameter exists and not null
            if (!array_key_exists($param, $parameters) || $parameters[$param] === null) {
                continue;
            }
            $value = (string) $parameters[$param];
            $delimiterWrapped = '#^' . $regex . '$#';
            if (@preg_match($delimiterWrapped, '') === false) {
                // Invalid regex - treat as non-match for safety
                return false;
            }
            if (!preg_match($delimiterWrapped, $value)) {
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

        $destination = function (Request $request) use ($action, $parameters) {
            return $this->executeRouteAction($action, $parameters);
        };

        $response = $this->runRouteMiddleware($route, $request, $destination);

        return $this->normalizeResponse($action, $response);
    }

    /**
     * Execute the underlying route action.
     *
     * @param mixed $action
     * @param array $parameters
     * @return mixed
     */
    protected function executeRouteAction($action, array $parameters)
    {
        if ($action instanceof Closure) {
            return call_user_func_array($action, array_values($parameters));
        }

        if (is_string($action)) {
            return $this->callControllerAction($action, $parameters);
        }

        throw new \Exception('Invalid route action');
    }

    /**
     * Normalize any handler output into a Response.
     *
     * @param mixed $action
     * @param mixed $response
     * @return Response
     */
    protected function normalizeResponse($action, $response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        $actionName = 'middleware';

        if (is_string($action)) {
            $actionName = $action;
        } elseif (is_object($action)) {
            $actionName = get_class($action);
        }

        return $this->makeSmartResponse($actionName, $response);
    }

    /**
     * Convert controller response to proper Response object
     * 
     * @param string $action The controller/closure identifier
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
            throw new \InvalidArgumentException(
                'Controller returned an array. Return Response/string or use response()->json() explicitly.'
            );
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
        return call_user_func_array([$controller, $method], array_values($parameters));
    }

    /**
     * Execute route-specific middleware stack.
     *
     * @param Route $route
     * @param Request $request
     * @param callable $destination
     * @return mixed
     */
    protected function runRouteMiddleware(Route $route, Request $request, callable $destination)
    {
        $middleware = $route->getMiddleware();

        if (empty($middleware)) {
            return $destination($request);
        }

        $pipeline = array_reduce(
            array_reverse($middleware),
            function ($next, $definition) {
                return function (Request $request) use ($definition, $next) {
                    $instance = $this->resolveRouteMiddleware($definition);
                    return $instance->handle($request, function (Request $request) use ($next) {
                        return $next($request);
                    });
                };
            },
            $destination
        );

        return $pipeline($request);
    }

    /**
     * Resolve middleware definition to an executable instance.
     *
     * @param mixed $middleware
     * @return MiddlewareInterface
     */
    protected function resolveRouteMiddleware($middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        if ($middleware instanceof Closure) {
            return new class($middleware) implements MiddlewareInterface {
                protected Closure $callback;

                public function __construct(Closure $callback)
                {
                    $this->callback = $callback;
                }

                public function handle(Request $request, Closure $next)
                {
                    return ($this->callback)($request, $next);
                }
            };
        }

        if (is_string($middleware)) {
            return $this->instantiateMiddlewareClass($middleware);
        }

        if (is_object($middleware) && method_exists($middleware, 'handle')) {
            if ($middleware instanceof MiddlewareInterface) {
                return $middleware;
            }
        }

        throw new \InvalidArgumentException('Invalid middleware provided to route.');
    }

    /**
     * Instantiate middleware using the container when available.
     *
     * @param string $middleware
     * @return MiddlewareInterface
     */
    protected function instantiateMiddlewareClass(string $middleware): MiddlewareInterface
    {
        if ($this->app && $this->app->has($middleware)) {
            $instance = $this->app->make($middleware);
        } elseif (class_exists($middleware)) {
            $instance = $this->app
                ? $this->app->make($middleware)
                : new $middleware();
        } else {
            throw new \InvalidArgumentException("Middleware class '{$middleware}' not found.");
        }

        if (!$instance instanceof MiddlewareInterface) {
            throw new \InvalidArgumentException("Middleware '{$middleware}' must implement MiddlewareInterface.");
        }

        return $instance;
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
