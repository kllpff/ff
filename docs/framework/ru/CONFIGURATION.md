# Руководство по конфигурации

Настройте ваше приложение FF Framework.

## Файлы конфигурации

Вся конфигурация в директории `config/`:

- `app.php` - Настройки приложения
- `database.php` - Конфигурация базы данных  
- `cache.php` - Настройки кеширования
- `session.php` - Конфигурация сессий
- `mail.php` - Настройки электронной почты

## app.php

```php
return [
    'name' => env('APP_NAME', 'FF'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'locale' => 'ru',
    'key' => env('APP_KEY'),
];
```

## database.php

```php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ],
    ],
];
```

## Использование конфигурации

```php
// Получить значение конфигурации
$appName = config('app.name');
$dbHost = config('database.connections.mysql.host');

// Значение по умолчанию
$timezone = config('app.timezone', 'UTC');
```

## Переменные окружения

Храните чувствительные данные в `.env`:

```env
APP_NAME=FF
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
APP_KEY=ваш_ключ_шифрования

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=ff
DB_USERNAME=root
DB_PASSWORD=секрет
```

Доступ в коде:

```php
$appEnv = env('APP_ENV');
$dbPassword = env('DB_PASSWORD');
```

---

[← Назад к документации](./README.md)
