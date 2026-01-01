# Руководство по базе данных

Полное руководство по операциям с базой данных в FF Framework.

## Конфигурация базы данных

Настройка базы данных в `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ff
DB_USERNAME=root
DB_PASSWORD=
```

Поддерживаемые базы данных:
- MySQL 5.7+
- PostgreSQL 10+
- SQLite 3

## QueryBuilder

Построение запросов к базе данных программно:

### SELECT

```php
// Получить все
$users = User::all();

// С условиями
$users = User::where('status', 'active')->get();

// Первый результат
$user = User::where('email', 'ivan@example.com')->first();

// Определенные столбцы
$users = User::select('id', 'name', 'email')->get();

// Уникальные значения
User::select('role')->distinct()->get();
```

### WHERE

```php
// Базовое WHERE
User::where('age', '>', 18)->get();
User::where('age', '=', 25)->get();  // = по умолчанию
User::where('age', 25)->get();       // Сокращение

// Несколько условий (AND)
User::where('status', 'active')
    ->where('verified', true)
    ->get();

// OR
User::where('role', 'admin')
    ->orWhere('role', 'moderator')
    ->get();

// IN
User::whereIn('role', ['admin', 'user'])->get();

// NOT IN
User::whereNotIn('status', ['deleted', 'banned'])->get();

// NULL
User::whereNull('deleted_at')->get();
User::whereNotNull('verified_at')->get();

// BETWEEN
User::whereBetween('age', [18, 65])->get();

// Сырой SQL
User::whereRaw('YEAR(created_at) = ?', [2024])->get();
User::whereRaw('DATE(created_at) BETWEEN ? AND ?', ['2024-01-01', '2024-12-31'])->get();
```

### ORDER & LIMIT

```php
// Сортировка
User::orderBy('created_at', 'desc')->get();

// Последние
User::latest('created_at')->get();

// Самые старые
User::oldest('created_at')->get();

// Ограничение
User::limit(10)->get();

// Пропустить
User::skip(10)->limit(10)->get();

// Пагинация
$page = 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;
User::skip($offset)->limit($perPage)->get();
```

### JOIN

```php
// INNER JOIN
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.name')
    ->get();

// LEFT JOIN
$posts = Post::leftJoin('comments', 'posts.id', '=', 'comments.post_id')
    ->select('posts.*')
    ->get();

// Несколько соединений
Post::join('users', 'posts.user_id', '=', 'users.id')
    ->join('categories', 'posts.category_id', '=', 'categories.id')
    ->get();
```

### АГГРЕГАТНЫЕ ФУНКЦИИ

```php
User::count();
Post::sum('views');
Review::avg('rating');
Product::min('price');
Product::max('price');
```

## Вставка, обновление, удаление

### Вставка

```php
// Создать модель
$user = User::create([
    'name' => 'Иван',
    'email' => 'ivan@example.com',
]);

// Вставить и получить ID
$id = User::insertGetId([
    'name' => 'Иван',
    'email' => 'ivan@example.com',
]);
```

### Обновление

```php
// Обновить модель
$user = User::find(1);
$user->update(['name' => 'Мария']);

// Обновить несколько
User::where('status', 'inactive')
    ->update(['status' => 'active']);

// Увеличить
User::find(1)->increment('points', 10);

// Уменьшить
User::find(1)->decrement('points', 5);
```

### Удаление

```php
// Удалить модель
$user = User::find(1);
$user->delete();

// Удалить несколько
User::where('status', 'inactive')->delete();

// Очистить (удалить все)
User::query()->truncate();
```

## Миграции

Создание и управление схемой базы данных.

### Создание файла миграции

```bash
php generate:migration create_users_table
```

### Файл миграции

```php
// database/migrations/2024_01_01_000001_create_users_table.php
<?php

return [
    'up' => function($connection) {
        $connection->statement("
            CREATE TABLE users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    },
    
    'down' => function($connection) {
        $connection->statement("DROP TABLE IF EXISTS users");
    }
];
```

### Запуск миграций

```bash
php migrate.php
```

## Транзакции

Атомарное выполнение операций:

```php
try {
    DB::beginTransaction();
    
    $user = User::create([...]);
    $user->posts()->create([...]);
    
    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    throw $e;
}

// Или с обратным вызовом
$user = User::transaction(function() {
    $user = User::create([...]);
    $user->posts()->create([...]);
    return $user;
});
```

## Сырые запросы

Для сложных запросов:

```php
$users = DB::query(
    "SELECT * FROM users WHERE status = ?",
    ['active']
);

$connection = app(\FF\Database\Connection::class);
$results = $connection->query("SELECT * FROM users");
```

---

[← Назад к документации](./README.md)
