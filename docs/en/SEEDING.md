# Seeding Guide

Populate database with test data.

## Seeding Data

```php
// database/seed.php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Create database connection
$connection = app(\FF\Framework\Database\Connection::class);

// Seed users
for ($i = 1; $i <= 5; $i++) {
    $connection->insert("
        INSERT INTO users (name, email, password)
        VALUES (?, ?, ?)
    ", [
        "User $i",
        "user$i@example.com",
        Hash::make('password'),
    ]);
}

// Seed posts
for ($i = 1; $i <= 10; $i++) {
    $connection->insert("
        INSERT INTO posts (title, content, user_id)
        VALUES (?, ?, ?)
    ", [
        "Post $i",
        "Content for post $i",
        rand(1, 5),
    ]);
}

echo "Database seeded!\n";
```

## Running Seeder

```bash
php seed.php
```

## Using Models

```php
// More elegant with models
User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => Hash::make('password'),
]);

Post::create([
    'title' => 'First Post',
    'content' => '...',
    'user_id' => 1,
]);
```

---

[← Back to Docs](./README.md)
