# Руководство по миграциям

Управление схемой базы данных с помощью миграций.

## Создание миграций

```bash
php generate:migration create_posts_table
```

## Файлы миграций

```php
// database/migrations/2024_01_01_000001_create_posts_table.php
<?php

return [
    'up' => function($connection) {
        $connection->statement("
            CREATE TABLE posts (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    },
    
    'down' => function($connection) {
        $connection->statement("DROP TABLE IF EXISTS posts");
    }
];
```

## Запуск миграций

```bash
php migrate.php
```

## Лучшие практики миграций

- ✅ Создавайте одну таблицу на миграцию
- ✅ Включайте метод down() для отката
- ✅ Используйте описательные имена
- ✅ Делайте миграции идемпотентными

---

[← Назад к документации](./README.md)
