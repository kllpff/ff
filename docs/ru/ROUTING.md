# Руководство по маршрутизации

Научитесь определять и управлять маршрутами приложения.

## Основные маршруты

Маршруты определяются в `config/routes.php`:

```php
$router = app(\FF\Framework\Http\Router::class);

// GET запрос
$router->get('/users', 'UserController@index');

// POST запрос
$router->post('/users', 'UserController@store');

// PUT запрос
$router->put('/users/{id}', 'UserController@update');

// DELETE запрос
$router->delete('/users/{id}', 'UserController@destroy');

// Любой метод
$router->any('/api/data', 'ApiController@handle');

// Несколько методов
$router->match(['get', 'post'], '/search', 'SearchController@search');
```

## Параметры маршрутов

### Обязательные параметры

```php
$router->get('/users/{id}', 'UserController@show');
// Совпадает: /users/1, /users/john

// В контроллере
public function show($id) {
    $user = User::find($id);
}
```

### Опциональные параметры

```php
$router->get('/posts/{year?}', 'PostController@archive');
// Совпадает: /posts или /posts/2024
```

### Валидация параметров

```php
$router->get('/users/{id}', 'UserController@show')
    ->where('id', '[0-9]+');

$router->get('/posts/{slug}', 'PostController@show')
    ->where('slug', '[a-z0-9-]+');
```

## Именованные маршруты

Присвойте маршрутам имена для удобной генерации URL:

```php
$router->get('/users', 'UserController@index')
    ->name('users.index');

$router->get('/users/{id}', 'UserController@show')
    ->name('users.show');

// Генерация URL
route('users.index');                    // /users
route('users.show', ['id' => 1]);        // /users/1
```

## Группы маршрутов

Группировка связанных маршрутов:

```php
// Префикс
$router->group(['prefix' => 'api'], function($router) {
    $router->get('/users', 'UserController@index');
    // URL: /api/users
});

// Middleware
$router->group(['middleware' => 'auth'], function($router) {
    $router->post('/profile', 'ProfileController@update');
    // Требует аутентификации
});

// Пространство имен
$router->group(['namespace' => 'Admin'], function($router) {
    $router->get('/dashboard', 'DashboardController@index');
    // Использует: App\Controllers\Admin\DashboardController
});

// Комбинированные
$router->group([
    'prefix' => 'api',
    'middleware' => ['api'],
    'namespace' => 'Api'
], function($router) {
    $router->get('/users', 'UserController@index');
    $router->post('/users', 'UserController@store');
});
```

## Действия маршрутов

### Метод контроллера

```php
$router->get('/users', 'UserController@index');
// Вызывает: App\Controllers\UserController->index()
```

### Замыкание

```php
$router->get('/', function(Request $request) {
    return view('home');
});

$router->get('/api/status', function() {
    return ['status' => 'ok'];
});
```

## Параметры запроса

Доступ к параметрам маршрута/запроса:

```php
public function show(Request $request, $id)
{
    // Параметр маршрута
    echo $id;                           // Из {id}
    
    // GET параметр
    $search = $request->get('search');
    
    // Все параметры
    $all = $request->all();
    
    // Только определенные поля
    $data = $request->only(['name', 'email']);
}
```

## Генерация URL

### По имени маршрута

```php
// В представлениях или контроллерах
route('users.show', ['id' => 1]);           // /users/1
route('posts.index', [], ['page' => 2]);    // /posts?page=2
```

### Текущий URL

```php
request()->url();          // /users/1
request()->fullUrl();      // /users/1?sort=date
request()->path();         // users/1
```

### Полный пример

```php
// config/routes.php
$router->get('/', 'HomeController@index')->name('home');

$router->group(['prefix' => 'blog'], function($router) {
    $router->get('/', 'BlogController@index')->name('blog.index');
    $router->get('/{slug}', 'BlogController@show')->name('blog.show');
    $router->post('/', 'BlogController@store')->name('blog.store');
});

$router->group(['middleware' => 'auth'], function($router) {
    $router->get('/profile', 'ProfileController@show')->name('profile');
    $router->post('/profile', 'ProfileController@update');
});

// Использование в представлениях
<a href="{{ route('home') }}">Главная</a>
<a href="{{ route('blog.index') }}">Блог</a>
<a href="{{ route('blog.show', ['slug' => $post->slug]) }}">Пост</a>
<a href="{{ route('profile') }}">Профиль</a>
```

## Middleware

Привязка middleware к маршрутам:

```php
$router->post('/admin', 'AdminController@index')
    ->middleware('auth');

$router->group(['middleware' => ['auth', 'admin']], function($router) {
    $router->delete('/users/{id}', 'UserController@destroy');
});
```

## RESTful маршруты

Обычный паттерн для ресурсов:

```php
$router->group(['prefix' => 'api'], function($router) {
    // Список
    $router->get('/posts', 'PostController@index')
        ->name('posts.index');
    
    // Показать форму создания
    $router->get('/posts/create', 'PostController@create')
        ->name('posts.create');
    
    // Сохранить
    $router->post('/posts', 'PostController@store')
        ->name('posts.store');
    
    // Показать
    $router->get('/posts/{id}', 'PostController@show')
        ->name('posts.show');
    
    // Показать форму редактирования
    $router->get('/posts/{id}/edit', 'PostController@edit')
        ->name('posts.edit');
    
    // Обновить
    $router->put('/posts/{id}', 'PostController@update')
        ->name('posts.update');
    
    // Удалить
    $router->delete('/posts/{id}', 'PostController@destroy')
        ->name('posts.destroy');
});
```

---

[← Назад к документации](./README.md)
