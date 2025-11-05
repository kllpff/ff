# Logging Guide

Track application events and errors.

## Basic Logging

```php
$logger = logger();

// Log levels
$logger->debug('Debug message');
$logger->info('Informational message');
$logger->notice('Normal message');
$logger->warning('Warning message');
$logger->error('Error occurred');
$logger->critical('Critical error');
$logger->alert('Alert message');
$logger->emergency('Emergency message');
```

## Logging with Context

```php
logger()->info('User action', [
    'user_id' => 123,
    'action' => 'login',
    'ip' => $request->ip(),
    'timestamp' => now(),
]);

logger()->error('Database error', [
    'query' => $sql,
    'error' => $e->getMessage(),
]);
```

## Usage Examples

```php
// In controller
public function login(Request $request)
{
    logger()->debug('Login attempt', [
        'email' => $request->input('email'),
        'ip' => $request->ip(),
    ]);
    
    // ... authentication logic ...
    
    logger()->info('User logged in', ['user_id' => $user->id]);
}

// In model
public function save()
{
    logger()->debug('Saving model', ['model' => get_class($this)]);
    parent::save();
}
```

---

[← Back to Docs](./README.md)
