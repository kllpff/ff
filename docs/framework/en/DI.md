# Dependency Injection Guide

Use the service container for loose coupling and testability.

## Basic Concept

Instead of creating dependencies manually:

```php
// ❌ Bad - tight coupling
class UserService
{
    private $database = new PDO(...);
}
```

Inject them:

```php
// ✅ Good - loose coupling
class UserService
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
}
```

## Using the Container

### Binding Services

```php
$app = app();

// Simple binding
$app->bind('config', function() {
    return new ConfigRepository();
});

// Singleton (create once)
$app->singleton('database', function() {
    return new Database(...);
});

// Concrete class
$app->bind('UserService', UserService::class);
```

### Resolving Services

```php
$service = app('config');
$service = app(UserService::class);
```

## Constructor Injection

The container automatically injects dependencies:

```php
class UserController
{
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }
}

// Container automatically creates UserService and injects it
$controller = app(UserController::class);
```

## Method Injection

```php
class PostController
{
    public function index(Cache $cache, Logger $logger)
    {
        // Container automatically injects Cache and Logger
        $posts = $cache->get('posts');
        if (!$posts) {
            $logger->info('Loading posts from database');
            $posts = Post::all();
        }
    }
}
```

## Service Providers

Register services in one place:

```php
class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('mailer', function() {
            return new MailService();
        });
    }
}
```

---

[← Back to Docs](./README.md)
