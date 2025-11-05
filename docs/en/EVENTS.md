# Events Guide

Implement event-driven architecture.

## Creating Events

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

## Creating Listeners

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

## Dispatching Events

```php
// In controller
$user = User::create($data);
event()->dispatch(new UserCreated($user));

// Or use helper
dispatch(new UserCreated($user));
```

## Registering Listeners

```php
// In service provider
$dispatcher = app('events');
$dispatcher->listen(UserCreated::class, SendWelcomeEmail::class);
```

---

[← Back to Docs](./README.md)
