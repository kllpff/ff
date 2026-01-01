# Email Guide

Send emails from your application.

## Configuration

Configure in `.env`:

```env
MAIL_DRIVER=mail
MAIL_FROM=noreply@example.com
```

## Sending Email

```php
$mail = app('mailer');

$mail->send('user@example.com', 'Welcome', 'Hello!');

// HTML email
$mail->sendHtml('user@example.com', 'Welcome', '<h1>Hello</h1>');

// From template
$mail->sendView('user@example.com', 'Welcome', 'emails/welcome', [
    'name' => 'John',
]);
```

## Email Templates

```html
<!-- app/Views/emails/welcome.php -->
<h1>Welcome, <?php echo h($name); ?>!</h1>
<p>Thank you for signing up.</p>
```

---

[← Back to Docs](./README.md)
