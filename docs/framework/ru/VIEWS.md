# Руководство по представлениям

Создавайте красивые шаблоны с FF Framework.

## Создание представлений

Представления - это PHP файлы в `app/Views/`:

```php
<!-- app/Views/home.php -->
<h1>Добро пожаловать!</h1>
<p>Это представление.</p>
```

## Рендеринг представлений

В контроллере:

```php
public function index()
{
    return view('home', [
        'title' => 'Главная страница',
        'posts' => Post::all(),
    ]);
}
```

Переменные автоматически доступны в представлении.

## Макеты (Layouts)

FF Framework поддерживает гибкое управление макетами с несколькими способами их указания.

### Макет по умолчанию

По умолчанию все представления используют `app/Views/layouts/app.php`. Вы можете изменить значение по умолчанию в `config/view.php`:

```php
// config/view.php
return [
    'default_layout' => env('VIEW_DEFAULT_LAYOUT', 'app'),
];
```

### Базовая структура макета

```php
<!-- app/Views/layouts/app.php -->
<!DOCTYPE html>
<html>
<head>
    <title><?php echo h($title ?? 'Приложение'); ?></title>
</head>
<body>
    <header>Шапка</header>
    <main>
        <?php echo $__content; ?>
    </main>
    <footer>Подвал</footer>
</body>
</html>
```

### Указание макета в представлении

**Способ 1: Передать макет в данных контроллера**

```php
public function index()
{
    return view('home', [
        '__layout' => 'main',  // Использует app/Views/layouts/main.php
        'title' => 'Главная страница',
    ]);
}
```

**Способ 2: Использовать другой макет для конкретной страницы**

```php
public function landing()
{
    return view('landing', [
        '__layout' => 'minimal',  // Использует app/Views/layouts/minimal.php
        'features' => $this->getFeatures(),
    ]);
}
```

**Способ 3: Использовать вложенный путь к макету**

```php
public function adminDashboard()
{
    return view('admin/dashboard', [
        '__layout' => 'admin/layouts/app',  // Использует app/Views/admin/layouts/app.php
        'stats' => $this->getStats(),
    ]);
}
```

**Способ 4: Отключить макет**

```php
public function apiData()
{
    return view('api/data', [
        '__layout' => null,  // Без макета, только чистое представление
        'data' => $this->getData(),
    ]);
}
```

### Пути поиска макетов

Фреймворк ищет макеты в нескольких местах (настраивается в `config/view.php`):

1. `app/Views/layouts/` (по умолчанию)
2. `app/Views/admin/layouts/` (если настроено)
3. Пользовательские пути, определённые в конфиге

```php
// config/view.php
return [
    'layout_paths' => [
        'layouts',           // app/Views/layouts/
        'admin/layouts',     // app/Views/admin/layouts/
    ],
];
```

## Экранирование вывода

Всегда экранируйте пользовательский ввод:

```php
<!-- С помощью помощника h() -->
<p><?php echo h($user->name); ?></p>

<!-- Или htmlspecialchars -->
<p><?php echo htmlspecialchars($user->bio, ENT_QUOTES); ?></p>
```

## Условия

```php
<?php if ($user): ?>
    <p>Добро пожаловать, <?php echo h($user->name); ?></p>
<?php else: ?>
    <p>Пожалуйста, войдите</p>
<?php endif; ?>

<?php echo $admin ? 'Админ' : 'Пользователь'; ?>
```

## Циклы

```php
<!-- foreach -->
<?php foreach ($users as $user): ?>
    <p><?php echo h($user->name); ?></p>
<?php endforeach; ?>

<!-- for -->
<?php for ($i = 0; $i < 5; $i++): ?>
    <p>Элемент <?php echo $i; ?></p>
<?php endfor; ?>
```

## Формы

```html
<form method="POST" action="/users">
    <?php echo csrf_field(); ?>
    
    <input 
        type="text"
        name="name"
        value="<?php echo old('name'); ?>"
    />
    
    <?php if ($errors->has('name')): ?>
        <span class="error"><?php echo $errors->first('name'); ?></span>
    <?php endif; ?>
    
    <button type="submit">Отправить</button>
</form>
```

## Flash-сообщения

```php
<!-- В контроллере -->
session()->flash('success', 'Пользователь создан!');

<!-- В представлении -->
<?php if (session()->has('success')): ?>
    <div class="alert">
        <?php echo session('success'); ?>
    </div>
<?php endif; ?>
```

## Генерация URL

```php
<!-- К маршруту -->
<a href="<?php echo route('users.index'); ?>">Пользователи</a>
<a href="<?php echo route('users.show', ['id' => 1]); ?>">Пользователь 1</a>

<!-- К URL -->
<a href="<?php echo url('/users'); ?>">Пользователи</a>
```

## Включение других представлений

```php
<!-- Включить другое представление -->
<?php include view('partials/header'); ?>
```

## Полный пример

```php
<!-- app/Views/users/index.php -->

<h1>Пользователи</h1>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success">
        <?php echo session('success'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo route('users.create'); ?>" class="btn">Добавить пользователя</a>

<table>
    <thead>
        <tr>
            <th>Имя</th>
            <th>Email</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo h($user->name); ?></td>
                <td><?php echo h($user->email); ?></td>
                <td>
                    <a href="<?php echo route('users.show', ['id' => $user->id]); ?>">
                        Просмотр
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

---

[← Назад к документации](./README.md)
