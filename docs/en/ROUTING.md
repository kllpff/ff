# Routing Guide

Learn how to define and manage application routes.

## Basic Routes

Routes are defined in `config/routes.php`:

```php
$router = app(\FF\Http\Router::class);

// GET request
$router->get('/users', 'UserController@index');

// POST request
$router->post('/users', 'UserController@store');

// PUT request
$router->put('/users/{id}', 'UserController@update');

// DELETE request
$router->delete('/users/{id}', 'UserController@destroy');

// Any method
$router->any('/api/data', 'ApiController@handle');

// Multiple methods
$router->match(['get', 'post'], '/search', 'SearchController@search');
```

## Route Parameters

### Required Parameters

```php
$router->get('/users/{id}', 'UserController@show');
// Matches: /users/1, /users/john

// In controller
public function show($id) {
    $user = User::find($id);
}
```

### Optional Parameters

```php
$router->get('/posts/{year?}', 'PostController@archive');
// Matches: /posts or /posts/2024
```

### Parameter Validation

```php
$router->get('/users/{id}', 'UserController@show')
    ->where('id', '[0-9]+');

$router->get('/posts/{slug}', 'PostController@show')
    ->where('slug', '[a-z0-9-]+');
 
 // Multiple constraints
 $router->get('/reports/{year}/{format}', 'ReportController@show')
     ->where([
         'year' => '\\d{4}',
         'format' => '(json|csv)'
     ]);
```

 Notes:
 - If a parameter does not match the regex, the route does not match (404).
 - Patterns are anchored automatically (`^...$`), no delimiters needed.
 - Optional parameters (`{param?}`) are supported; constraints apply only when the value is present.

## Named Routes

Give routes names for easy URL generation:

```php
$router->get('/users', 'UserController@index')
    ->name('users.index');

$router->get('/users/{id}', 'UserController@show')
    ->name('users.show');

// Generate URLs
route('users.index');                    // /users
route('users.show', ['id' => 1]);        // /users/1
```

## Route Groups

Group related routes:

```php
// Prefix
$router->group(['prefix' => 'api'], function($router) {
    $router->get('/users', 'UserController@index');
    // URL: /api/users
});

// Middleware
$router->group(['middleware' => 'auth'], function($router) {
    $router->post('/profile', 'ProfileController@update');
    // Requires authentication
});

// Namespace
$router->group(['namespace' => 'Admin'], function($router) {
    $router->get('/dashboard', 'DashboardController@index');
    // Uses: App\Controllers\Admin\DashboardController
});

// Combined
$router->group([
    'prefix' => 'api',
    'middleware' => ['api'],
    'namespace' => 'Api'
], function($router) {
    $router->get('/users', 'UserController@index');
    $router->post('/users', 'UserController@store');
});
```

## Route Actions

### Controller Method

```php
$router->get('/users', 'UserController@index');
// Calls: App\Controllers\UserController->index()
```

### Closure

```php
$router->get('/', function(Request $request) {
    return view('home');
});

$router->get('/api/status', function() {
    return ['status' => 'ok'];
});
```

## Request Parameters

Access route/query parameters:

```php
public function show(Request $request, $id)
{
    // Route parameter
    echo $id;                           // From {id}
    
    // GET parameter
    $search = $request->get('search');
    
    // All parameters
    $all = $request->all();
    
    // Specific fields only
    $data = $request->only(['name', 'email']);
}
```

## URL Generation

### From Route Name

```php
// In views or controllers
route('users.show', ['id' => 1]);           // /users/1
route('posts.index', [], ['page' => 2]);    // /posts?page=2
```

### Current URL

```php
request()->url();          // http://localhost/users/1
request()->fullUrl();      // http://localhost/users/1?sort=date
request()->path();         // users/1
```

### Complete Example

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

// Usage in views
<a href="{{ route('home') }}">Home</a>
<a href="{{ route('blog.index') }}">Blog</a>
<a href="{{ route('blog.show', ['slug' => $post->slug]) }}">Post</a>
<a href="{{ route('profile') }}">Profile</a>
```

## Middleware

Attach middleware to routes:

```php
$router->post('/admin', 'AdminController@index')
    ->middleware('auth');

$router->group(['middleware' => ['auth', 'admin']], function($router) {
    $router->delete('/users/{id}', 'UserController@destroy');
});
```

## RESTful Routes

Common pattern for resources:

```php
$router->group(['prefix' => 'api'], function($router) {
    // List
    $router->get('/posts', 'PostController@index')
        ->name('posts.index');
    
    // Show create form
    $router->get('/posts/create', 'PostController@create')
        ->name('posts.create');
    
    // Store
    $router->post('/posts', 'PostController@store')
        ->name('posts.store');
    
    // Show
    $router->get('/posts/{id}', 'PostController@show')
        ->name('posts.show');
    
    // Show edit form
    $router->get('/posts/{id}/edit', 'PostController@edit')
        ->name('posts.edit');
    
    // Update
    $router->put('/posts/{id}', 'PostController@update')
        ->name('posts.update');
    
    // Delete
    $router->delete('/posts/{id}', 'PostController@destroy')
        ->name('posts.destroy');
});
```

---

[← Back to Docs](./README.md)
