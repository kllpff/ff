# Configuration Guide

Configure your FF Framework application.

## Configuration Files

All configuration in `config/` directory:

- `app.php` - Application settings
- `database.php` - Database configuration  
- `cache.php` - Caching settings
- `session.php` - Session configuration
- `mail.php` - Email settings

## app.php

```php
return [
    'name' => env('APP_NAME', 'FF'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'locale' => 'en',
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

## Using Configuration

```php
// Get config value
$appName = config('app.name');
$dbHost = config('database.connections.mysql.host');

// Fallback default
$timezone = config('app.timezone', 'UTC');
```

## Environment Variables

Store sensitive data in `.env`:

```env
APP_NAME=FF
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
APP_KEY=your_encryption_key

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=ff
DB_USERNAME=root
DB_PASSWORD=secret
```

Access in code:

```php
$appEnv = env('APP_ENV');
$dbPassword = env('DB_PASSWORD');
```

---

[← Back to Docs](./README.md)
