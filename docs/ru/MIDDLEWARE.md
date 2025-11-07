# Руководство по Middleware

Используйте middleware для фильтрации HTTP запросов и ответов.

## Создание Middleware

```php
// app/Middleware/LogRequests.php
<?php

namespace App\Middleware;

use FF\Http\Middleware\MiddlewareInterface;

class LogRequests implements MiddlewareInterface
{
    public function handle($request, $next)
    {
        logger()->info('Запрос', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);
        
        $response = $next($request);
        
        logger()->info('Ответ', [
            'status' => $response->getStatusCode(),
        ]);
        
        return $response;
    }
}
```

## Регистрация Middleware

### На маршрутах

```php
$router->post('/users', 'UserController@store')
    ->middleware(new LogRequests());
```

### На группах маршрутов

```php
$router->group(['middleware' => new LogRequests()], function($router) {
    $router->post('/users', 'UserController@store');
    $router->post('/posts', 'PostController@store');
});
```

## Встроенные Middleware

Фреймворк включает middleware для:

- **Auth** - Требование аутентификации
- **CSRF** - Валидация CSRF токена
- **RateLimit** - Ограничение частоты запросов
- **COR** - Cross-Origin Resource Sharing

---

[← Назад к документации](./README.md)
