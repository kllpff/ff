# Руководство по событиям

Реализуйте архитектуру, управляемую событиями.

## Создание событий

```php
// app/Events/UserCreated.php
<?php

namespace App\Events;

class UserCreated
{
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
}
```

## Создание слушателей

```php
// app/Listeners/SendWelcomeEmail.php
<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Services\MailService;

class SendWelcomeEmail
{
    public function handle(UserCreated $event)
    {
        $user = $event->user;
        app(MailService::class)->sendWelcome($user);
    }
}
```

## Отправка событий

```php
// В контроллере
$user = User::create($data);
event()->dispatch(new UserCreated($user));

// Или использовать помощник
dispatch(new UserCreated($user));
```

## Регистрация слушателей

```php
// В провайдере сервисов
$dispatcher = app('events');
$dispatcher->listen(UserCreated::class, SendWelcomeEmail::class);
```

---

[← Назад к документации](./README.md)
