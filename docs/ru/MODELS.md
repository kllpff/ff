# Руководство по моделям

Модели представляют таблицы базы данных и позволяют взаимодействовать с данными.

## Создание модели

Модели находятся в `app/Models/`:

```php
// app/Models/User.php
<?php

namespace App\Models;

use FF\Framework\Database\Model;

class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'password',
    ];

    protected array $hidden = [
        'password',
    ];
}
```

## Основные операции

### Создание

```php
// Создать и сохранить
$user = User::create([
    'name' => 'Иван Иванов',
    'email' => 'ivan@example.com',
    'password' => Hash::make('secret'),
]);

// Или создать экземпляр и сохранить
$user = new User();
$user->name = 'Мария Петрова';
$user->save();
```

### Чтение

```php
// Получить все
$users = User::all();

// Получить с условием
$user = User::where('email', 'ivan@example.com')->first();

// Получить по ID
$user = User::find(1);

// Получить или ошибка
$user = User::findOrFail(1);

// Подсчет
$count = User::count();

// Проверка существования
$exists = User::where('email', 'ivan@example.com')->exists();
```

### Обновление

```php
$user = User::find(1);
$user->update([
    'name' => 'Мария Петрова',
    'email' => 'maria@example.com',
]);

// Или обновить несколько
User::where('status', 'inactive')
    ->update(['status' => 'active']);
```

### Удаление

```php
$user = User::find(1);
$user->delete();

// Или удалить несколько
User::where('status', 'inactive')->delete();
```

## Запросы

Построение сложных запросов с использованием QueryBuilder:

```php
// Условия WHERE
User::where('status', 'active')
    ->where('verified', true)
    ->get();

// Условие OR
User::where('role', 'admin')
    ->orWhere('admin', true)
    ->get();

// Оператор IN
User::whereIn('role', ['admin', 'moderator'])->get();

// Сортировка
User::orderBy('created_at', 'desc')->get();

// Ограничение
User::limit(10)->skip(20)->get();

// Выбор определенных столбцов
User::select('id', 'name', 'email')->get();

// Подсчет
$count = User::count();

// Сумма/Среднее/Минимум/Максимум
Post::sum('views');
Review::avg('rating');
Order::min('price');
Order::max('price');
```

## Отношения

### Определение отношений

```php
// app/Models/User.php
class User extends Model
{
    // Один ко многим
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}

// app/Models/Post.php
class Post extends Model
{
    // Принадлежит
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

### Использование отношений

```php
// Получить связанные модели
$user = User::find(1);
$posts = $user->posts;

// Запрос отношений
$user->posts()
    ->where('published', true)
    ->get();

// Создать связанную
$user->posts()->create([
    'title' => 'Мой пост',
    'content' => '...',
]);
```

## Атрибуты

### Массовое присвоение

```php
class User extends Model
{
    protected array $fillable = [
        'name',
        'email',
        'password',
    ];
}

// Только заполняемые поля могут быть заполнены
$user = User::create([
    'name' => 'Иван',
    'email' => 'ivan@example.com',
    'admin' => true,  // Игнорируется - не в fillable
]);

// Или использовать fill
$user->fill([
    'name' => 'Мария',
    'email' => 'maria@example.com',
]);
$user->save();
```

### Аксессоры и мутаторы

```php
class User extends Model
{
    // Аксессор - при получении
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    // Мутатор - при установке
    public function setEmailAttribute($value)
    {
        return strtolower($value);
    }
}

// Использование
$user = User::find(1);
echo $user->name;  // С заглавной буквы
$user->email = 'IVAN@EXAMPLE.COM';
echo $user->email;  // В нижнем регистре
```

## Транзакции

Атомарное выполнение операций:

```php
$user = User::transaction(function() {
    $user = User::create([...]);
    $user->posts()->create([...]);
    return $user;
});
```

## Области видимости

Создание повторно используемых фильтров запросов:

```php
class Post extends Model
{
    // Область запроса
    public static function published()
    {
        return static::where('published', true);
    }
}

// Использование
$posts = Post::published()->get();
$recent = Post::published()->latest()->limit(5)->get();
```

## Полный пример

```php
// app/Models/Post.php
class Post extends Model
{
    protected string $table = 'posts';
    protected array $fillable = ['title', 'content', 'published'];

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public static function published()
    {
        return static::where('published', true);
    }
}

// Использование в контроллере
$posts = Post::published()
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

foreach ($posts as $post) {
    echo $post->title;
    echo $post->author->name;  // Доступ к отношению
}
```

---

[← Назад к документации](./README.md)
