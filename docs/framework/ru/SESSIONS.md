# Руководство по сессиям

Управление пользовательскими сессиями и временными данными.

## Основные операции с сессиями

```php
$session = session();

// Сохранить
$session->put('user_id', 123);
$session->put([
    'user_id' => 123,
    'username' => 'ivan',
]);

// Получить
$userId = $session->get('user_id');
$username = $session->get('username', 'Гость');

// Проверить
if ($session->has('user_id')) {
    // Пользователь вошел
}

// Удалить
$session->forget('user_id');

// Очистить все
$session->flush();
```

## Flash-сообщения

Временные сообщения, показываемые один раз:

```php
// В контроллере
session()->flash('success', 'Пользователь создан!');
session()->flash('error', 'Произошла ошибка');

// В представлении
<?php if (session()->has('success')): ?>
    <div class="alert alert-success">
        <?php echo session('success'); ?>
    </div>
<?php endif; ?>
```

## Вспомогательные функции

```php
// Получить сессию
session('user_id');

// Получить старый ввод (для форм)
old('email');

// Flash-сообщение
session()->flash('message', 'Сохранено!');
```

## Безопасность

```php
// Перегенерировать ID сессии (после входа)
session()->regenerate();

// Полностью аннулировать (выход)
session()->flush();
```

---

[← Назад к документации](./README.md)
