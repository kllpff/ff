# Migrations Guide

Manage database schema with migrations.

## Creating Migrations

```bash
php generate:migration create_posts_table
```

## Migration Files

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

## Running Migrations

```bash
php migrate.php
```

## Migration Best Practices

- ✅ Create one table per migration
- ✅ Include down() method for rollback
- ✅ Use descriptive names
- ✅ Make migrations idempotent

---

[← Back to Docs](./README.md)
