# Руководство по заполнению данных

Заполните базу данных тестовыми данными.

## Заполнение данных

```php
// database/seed.php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Создать соединение с базой данных
$connection = app(\FF\Framework\Database\Connection::class);

// Заполнить пользователей
for ($i = 1; $i <= 5; $i++) {
    $connection->insert("
        INSERT INTO users (name, email, password)
        VALUES (?, ?, ?)
    ", [
        "Пользователь $i",
        "user$i@example.com",
        Hash::make('password'),
    ]);
}

// Заполнить посты
for ($i = 1; $i <= 10; $i++) {
    $connection->insert("
        INSERT INTO posts (title, content, user_id)
        VALUES (?, ?, ?)
    ", [
        "Пост $i",
        "Содержание поста $i",
        rand(1, 5),
    ]);
}

echo "База данных заполнена!\n";
```

## Запуск заполнения

```bash
php seed.php
```

## Использование моделей

```php
// Более элегантно с моделями
User::create([
    'name' => 'Иван',
    'email' => 'ivan@example.com',
    'password' => Hash::make('password'),
]);

Post::create([
    'title' => 'Первый пост',
    'content' => '...',
    'user_id' => 1,
]);
```

---

[← Назад к документации](./README.md)
