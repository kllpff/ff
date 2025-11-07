# Быстрый старт

Создайте ваше первое приложение на FF за 5 минут.

## 1. Создание маршрута

```php
// config/routes.php
$router = app(\FF\Http\Router::class);

$router->get('/hello/{name}', 'HelloController@greet')->name('hello');
```

## 2. Создание контроллера

```php
// app/Controllers/HelloController.php
<?php

namespace App\Controllers;

use FF\Http\Request;

class HelloController
{
    public function greet($name)
    {
        return view('hello', ['name' => $name]);
    }
}
```

## 3. Создание представления

```php
<!-- app/Views/hello.php -->
<?php $__layout = 'app'; ?>

<h1>Привет, <?php echo h($name); ?>!</h1>
<p>Добро пожаловать в FF Framework</p>
```

## 4. Посетите в браузере

Перейдите по адресу: **http://localhost:8000/hello/Иван**

Вы должны увидеть:
```
Привет, Иван!
Добро пожаловать в FF Framework
```

## Работа с базой данных

### 1. Создание модели

```php
// app/Models/Post.php
<?php

namespace App\Models;

use FF\Database\Model;

class Post extends Model
{
    protected string $table = 'posts';
    
    protected array $fillable = [
        'title',
        'content',
        'published',
    ];
}
```

### 2. Запрос к базе данных

```php
// В контроллере
$posts = Post::all();                          // Получить все
$post = Post::find(1);                         // Получить по ID
$published = Post::where('published', true)    // Запрос
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

### 3. Создание/Обновление/Удаление

```php
// Создание
$post = Post::create([
    'title' => 'Мой первый пост',
    'content' => 'Удивительный контент...',
    'published' => true,
]);

// Обновление
$post->update(['title' => 'Обновленный заголовок']);

// Удаление
$post->delete();
```

## Обработка форм

### 1. Создание формы

```php
<!-- app/Views/posts/create.php -->
<?php $__layout = 'app'; ?>

<form method="POST" action="/posts">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div>
        <label>Заголовок</label>
        <input
            type="text"
            name="title"
            value="<?= h($title ?? '') ?>"
            required
        />
    </div>

    <div>
        <label>Содержание</label>
        <textarea name="content" required><?= h($content ?? '') ?></textarea>
    </div>

    <button type="submit">Создать</button>
</form>
```

### 2. Обработка отправки

```php
// В контроллере
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string|min:10',
    ]);
    
    Post::create($validated);
    
    session()->flash('success', 'Пост создан!');
    return redirect('/posts');
}
```

## Аутентификация

### 1. Вход

```php
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    
    $user = User::where('email', $credentials['email'])->first();
    
    if ($user && Hash::check($credentials['password'], $user->password)) {
        session()->put('user_id', $user->id);
        return redirect('/dashboard');
    }
    
    return redirect('/login')->with('error', 'Неверные учетные данные');
}
```

### 2. Проверка входа

```php
// В представлении
<?php if (session()->has('user_id')): ?>
    <p>Добро пожаловать, <?php echo $user->name; ?></p>
<?php else: ?>
    <a href="/login">Вход</a>
<?php endif; ?>
```

## Общие задачи

### Отображение сообщений

```php
// В контроллере
session()->flash('success', 'Операция выполнена успешно!');
session()->flash('error', 'Что-то пошло не так');

// В представлении
<?php if (session()->getFlash('success')): ?>
    <div class="alert"><?= h(session()->getFlash('success')) ?></div>
<?php endif; ?>
```

### Перенаправление

```php
// Перенаправление на URL
return redirect('/home');

// Перенаправление на именованный маршрут
return redirect(route('posts.show', ['id' => 1]));

// Перенаправление назад с данными
return redirect()->back()->with('data', $value);
```

### Логирование

```php
logger()->info('Пользователь вошел', ['user_id' => 1]);
logger()->error('Ошибка базы данных', ['error' => $message]);
logger()->debug('Отладочная информация');
```

### Кеширование

```php
$cache = cache();

// Сохранить в кеш (3600 секунд = 1 час)
$cache->put('posts', $posts, 3600);

// Получить из кеша
$posts = $cache->get('posts');

// Удалить из кеша
$cache->forget('posts');
```

## Следующие шаги

1. **[Руководство по маршрутизации](./ROUTING.md)** - Изучите продвинутую маршрутизацию
2. **[Руководство по базе данных](./DATABASE.md)** - Освойте ORM
3. **[Руководство по валидации](./VALIDATION.md)** - Валидация входных данных
4. **[Руководство по безопасности](./SECURITY.md)** - Защитите ваше приложение
5. **[Полная документация](./README.md)** - Изучите все функции

## Советы

✅ Всегда валидируйте пользовательский ввод  
✅ Экранируйте вывод в представлениях с помощью `h()`  
✅ Используйте именованные маршруты для гибкости  
✅ Кешируйте часто запрашиваемые данные  
✅ Логируйте важные события  
✅ Следуйте стандарту кодирования PSR-12  

---

**Готовы создавать?** Начните с [Руководства по маршрутизации](./ROUTING.md)!
