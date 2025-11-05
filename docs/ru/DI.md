# Руководство по инъекции зависимостей

Используйте сервис контейнер для слабой связи и тестируемости.

## Основная концепция

Вместо создания зависимостей вручную:

```php
// ❌ Плохо - сильная связь
class UserService
{
    private $database = new PDO(...);
}
```

Внедряйте их:

```php
// ✅ Хорошо - слабая связь
class UserService
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
}
```

## Использование контейнера

### Связывание сервисов

```php
$app = app();

// Простое связывание
$app->bind('config', function() {
    return new ConfigRepository();
});

// Синглтон (создать один раз)
$app->singleton('database', function() {
    return new Database(...);
});

// Конкретный класс
$app->bind('UserService', UserService::class);
```

### Разрешение сервисов

```php
$service = app('config');
$service = app(UserService::class);
```

## Конструкторная инъекция

Контейнер автоматически внедряет зависимости:

```php
class UserController
{
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }
}

// Контейнер автоматически создает UserService и внедряет его
$controller = app(UserController::class);
```

## Инъекция методов

```php
class PostController
{
    public function index(Cache $cache, Logger $logger)
    {
        // Контейнер автоматически внедряет Cache и Logger
        $posts = $cache->get('posts');
        if (!$posts) {
            $logger->info('Загрузка постов из базы данных');
            $posts = Post::all();
        }
    }
}
```

## Провайдеры сервисов

Регистрация сервисов в одном месте:

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

[← Назад к документации](./README.md)
