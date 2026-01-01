# Руководство по электронной почте

Отправляйте электронные письма из вашего приложения.

## Конфигурация

Настройка в `.env`:

```env
MAIL_DRIVER=mail
MAIL_FROM=noreply@example.com
```

## Отправка писем

```php
$mail = app('mailer');

$mail->send('user@example.com', 'Добро пожаловать', 'Привет!');

// HTML письмо
$mail->sendHtml('user@example.com', 'Добро пожаловать', '<h1>Привет</h1>');

// Из шаблона
$mail->sendView('user@example.com', 'Добро пожаловать', 'emails/welcome', [
    'name' => 'Иван',
]);
```

## Шаблоны писем

```html
<!-- app/Views/emails/welcome.php -->
<h1>Добро пожаловать, <?php echo h($name); ?>!</h1>
<p>Спасибо за регистрацию.</p>
```

---

[← Назад к документации](./README.md)
