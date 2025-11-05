# Database Guide

Complete guide to database operations in FF Framework.

## Database Configuration

Configure database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ff
DB_USERNAME=root
DB_PASSWORD=
```

Supported databases:
- MySQL 5.7+
- PostgreSQL 10+
- SQLite 3

## QueryBuilder

Build database queries programmatically:

### SELECT

```php
// Get all
$users = User::all();

// With conditions
$users = User::where('status', 'active')->get();

// First result
$user = User::where('email', 'john@example.com')->first();

// Specific columns
$users = User::select('id', 'name', 'email')->get();

// Distinct
User::select('role')->distinct()->get();
```

### WHERE

```php
// Basic WHERE
User::where('age', '>', 18)->get();
User::where('age', '=', 25)->get();  // = is default
User::where('age', 25)->get();       // Shorthand

// Multiple conditions (AND)
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

// Raw SQL
User::whereRaw('YEAR(created_at) = 2024')->get();
```

### ORDER & LIMIT

```php
// Order by
User::orderBy('created_at', 'desc')->get();

// Latest
User::latest('created_at')->get();

// Oldest
User::oldest('created_at')->get();

// Limit
User::limit(10)->get();

// Skip
User::skip(10)->limit(10)->get();

// Pagination
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

// Multiple joins
Post::join('users', 'posts.user_id', '=', 'users.id')
    ->join('categories', 'posts.category_id', '=', 'categories.id')
    ->get();
```

### AGGREGATE

```php
User::count();
Post::sum('views');
Review::avg('rating');
Product::min('price');
Product::max('price');
```

## Insert, Update, Delete

### Insert

```php
// Create model
$user = User::create([
    'name' => 'John',
    'email' => 'john@example.com',
]);

// Insert and get ID
$id = User::insertGetId([
    'name' => 'John',
    'email' => 'john@example.com',
]);
```

### Update

```php
// Update model
$user = User::find(1);
$user->update(['name' => 'Jane']);

// Update multiple
User::where('status', 'inactive')
    ->update(['status' => 'active']);

// Increment
User::find(1)->increment('points', 10);

// Decrement
User::find(1)->decrement('points', 5);
```

### Delete

```php
// Delete model
$user = User::find(1);
$user->delete();

// Delete multiple
User::where('status', 'inactive')->delete();

// Truncate (delete all)
User::query()->truncate();
```

## Migrations

Create and manage database schema.

### Create Migration File

```bash
php generate:migration create_users_table
```

### Migration File

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

### Run Migrations

```bash
php migrate.php
```

## Transactions

Execute operations atomically:

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

// Or with callback
$user = User::transaction(function() {
    $user = User::create([...]);
    $user->posts()->create([...]);
    return $user;
});
```

## Raw Queries

For complex queries:

```php
$users = DB::query(
    "SELECT * FROM users WHERE status = ?",
    ['active']
);

$connection = app(\FF\Framework\Database\Connection::class);
$results = $connection->query("SELECT * FROM users");
```

---

[← Back to Docs](./README.md)
