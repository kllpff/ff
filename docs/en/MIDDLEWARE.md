# Middleware Guide

Use middleware to filter HTTP requests and responses.

## Creating Middleware

```php
// app/Middleware/LogRequests.php
<?php

namespace App\Middleware;

use FF\Framework\Http\Middleware\MiddlewareInterface;

class LogRequests implements MiddlewareInterface
{
    public function handle($request, $next)
    {
        logger()->info('Request', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);
        
        $response = $next($request);
        
        logger()->info('Response', [
            'status' => $response->getStatusCode(),
        ]);
        
        return $response;
    }
}
```

## Registering Middleware

### On Routes

```php
$router->post('/users', 'UserController@store')
    ->middleware(new LogRequests());
```

### On Route Groups

```php
$router->group(['middleware' => new LogRequests()], function($router) {
    $router->post('/users', 'UserController@store');
    $router->post('/posts', 'PostController@store');
});
```

## Built-in Middleware

The framework includes middleware for:

- **Auth** - Require authentication
- **CSRF** - CSRF token validation
- **RateLimit** - Rate limiting
- **COR** - Cross-Origin Resource Sharing

---

[← Back to Docs](./README.md)
