# Руководство по контроллерам

Контроллеры обрабатывают HTTP запросы и возвращают ответы.

## Создание контроллера

Контроллеры находятся в `app/Controllers/`:

```php
// app/Controllers/UserController.php
<?php

namespace App\Controllers;

use App\Models\User;
use FF\Http\Request;

class UserController
{
    // Методы действий обрабатывают запросы
    public function index()
    {
        $users = User::all();
        return view('users/index', ['users' => $users]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('users/show', ['user' => $user]);
    }

    public function create()
    {
        return view('users/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        User::create($validated);
        
        session()->flash('success', 'Пользователь создан!');
        return redirect('/users');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users/edit', ['user' => $user]);
    }

    public function update($id, Request $request)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user->update($validated);
        
        session()->flash('success', 'Пользователь обновлен!');
        return redirect('/users');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        session()->flash('success', 'Пользователь удален!');
        return redirect('/users');
    }
}
```

## Инъекция зависимостей

Внедрение сервисов в методы контроллера:

```php
<?php

namespace App\Controllers;

use FF\Cache\Cache;
use FF\Log\Logger;
use App\Models\Post;

class PostController
{
    // Внедрение через конструктор
    public function __construct(Cache $cache, Logger $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    // Или через параметр метода
    public function index(Cache $cache)
    {
        $posts = $cache->get('posts');
        if (!$posts) {
            $posts = Post::all();
            $cache->put('posts', $posts, 3600);
        }
        return view('posts/index', ['posts' => $posts]);
    }
}
```

## Обработка запросов

Доступ к данным запроса:

```php
public function store(Request $request)
{
    // Получить входные данные
    $name = $request->input('name');
    $email = $request->input('email', 'default@example.com');
    
    // Все входные данные
    $all = $request->all();
    
    // Определенные поля
    $data = $request->only(['name', 'email']);
    
    // Получить файл
    $file = $request->file('avatar');
    
    // Проверить тип запроса
    if ($request->isPost()) {
        // Обработать POST
    }
    
    // Проверить AJAX
    if ($request->isAjax()) {
        return response()->json(['status' => 'ok']);
    }
}
```

## Возврат ответов

### Возврат представления

```php
// Базовое представление
return view('home', ['title' => 'Добро пожаловать']);

// С пользовательским макетом
return view('home', [
    '__layout' => 'main',
    'title' => 'Добро пожаловать'
]);

// С админским макетом
return view('admin/dashboard', [
    '__layout' => 'admin/layouts/app',
    'stats' => $stats
]);

// Без макета
return view('api/data', [
    '__layout' => null,
    'data' => $data
]);
```

### Возврат JSON

```php
return response()->json([
    'status' => 'success',
    'data' => $user
]);
```

### Возврат перенаправления

```php
return redirect('/home');
return redirect(route('home'));
return redirect()->back();
```

### Возврат файла

```php
return response()->download('/path/to/file.pdf', 'download.pdf');
```

### Возврат строки

```php
return response('<h1>Привет</h1>', 200);
```

## Валидация в контроллере

```php
public function store(Request $request)
{
    // Валидация и получение данных
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);
    
    // Если валидация не прошла, перенаправляет с ошибками
    // Если прошла, возвращает валидированные данные
    
    User::create($validated);
}
```

## Сессии и flash-сообщения

```php
public function login(Request $request)
{
    // ... логика аутентификации ...
    
    // Сохранить в сессии
    session()->put('user_id', $user->id);
    
    // Flash-сообщение (показывается один раз)
    session()->flash('success', 'Добро пожаловать!');
    
    return redirect('/dashboard');
}

public function logout()
{
    // Удалить из сессии
    session()->forget('user_id');
    
    // Очистить все
    session()->flush();
    
    session()->flash('success', 'Вы вышли из системы');
    return redirect('/');
}
```

## Логирование

```php
public function store(Request $request)
{
    logger()->info('Создание пользователя', [
        'email' => $request->input('email'),
        'ip' => $request->ip()
    ]);
    
    $user = User::create($request->validated());
    
    logger()->info('Пользователь создан', ['user_id' => $user->id]);
}
```

## Кеширование

```php
public function index(Request $request)
{
    $cache = cache();
    
    if (!$cache->has('users')) {
        logger()->info('Промах кеша: Загрузка пользователей');
        $users = User::all();
        $cache->put('users', $users, 3600);
    } else {
        $users = $cache->get('users');
    }
    
    return view('users/index', ['users' => $users]);
}
```

---

[← Назад к документации](./README.md)
