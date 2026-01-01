# Руководство по логированию

Отслеживайте события и ошибки приложения.

## Основное логирование

```php
$logger = logger();

// Уровни логирования
$logger->debug('Отладочное сообщение');
$logger->info('Информационное сообщение');
$logger->notice('Нормальное сообщение');
$logger->warning('Предупреждающее сообщение');
$logger->error('Произошла ошибка');
$logger->critical('Критическая ошибка');
$logger->alert('Сообщение тревоги');
$logger->emergency('Экстренное сообщение');
```

## Логирование с контекстом

```php
logger()->info('Действие пользователя', [
    'user_id' => 123,
    'action' => 'login',
    'ip' => $request->ip(),
    'timestamp' => now(),
]);

logger()->error('Ошибка базы данных', [
    'query' => $sql,
    'error' => $e->getMessage(),
]);
```

## Примеры использования

```php
// В контроллере
public function login(Request $request)
{
    logger()->debug('Попытка входа', [
        'email' => $request->input('email'),
        'ip' => $request->ip(),
    ]);
    
    // ... логика аутентификации ...
    
    logger()->info('Пользователь вошел', ['user_id' => $user->id]);
}

// В модели
public function save()
{
    logger()->debug('Сохранение модели', ['model' => get_class($this)]);
    parent::save();
}
```

---

[← Назад к документации](./README.md)
