# Controllers Guide

Controllers handle HTTP requests and return responses.

## Creating a Controller

Controllers are in `app/Controllers/`:

```php
// app/Controllers/UserController.php
<?php

namespace App\Controllers;

use App\Models\User;
use FF\Framework\Http\Request;

class UserController
{
    // Action methods handle requests
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
        
        session()->flash('success', 'User created!');
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
        
        session()->flash('success', 'User updated!');
        return redirect('/users');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        session()->flash('success', 'User deleted!');
        return redirect('/users');
    }
}
```

## Dependency Injection

Inject services into controller methods:

```php
<?php

namespace App\Controllers;

use FF\Framework\Cache\Cache;
use FF\Framework\Log\Logger;
use App\Models\Post;

class PostController
{
    // Inject via constructor
    public function __construct(Cache $cache, Logger $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    // Or via method parameter
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

## Request Handling

Access request data:

```php
public function store(Request $request)
{
    // Get input
    $name = $request->input('name');
    $email = $request->input('email', 'default@example.com');
    
    // All input
    $all = $request->all();
    
    // Specific fields
    $data = $request->only(['name', 'email']);
    
    // Get file
    $file = $request->file('avatar');
    
    // Check request type
    if ($request->isPost()) {
        // Handle POST
    }
    
    // Check for AJAX
    if ($request->isAjax()) {
        return json_encode(['status' => 'ok']);
    }
}
```

## Returning Responses

### Return View

```php
return view('home', ['title' => 'Welcome']);
```

### Return JSON

```php
return response()->json([
    'status' => 'success',
    'data' => $user
]);
```

### Return Redirect

```php
return redirect('/home');
return redirect(route('home'));
return redirect()->back();
```

### Return File

```php
return response()->download('/path/to/file.pdf', 'download.pdf');
```

### Return String

```php
return response('<h1>Hello</h1>', 200);
```

## Validation in Controller

```php
public function store(Request $request)
{
    // Validate and get data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);
    
    // If validation fails, redirects with errors
    // If passes, returns validated data
    
    User::create($validated);
}
```

## Sessions and Flash Messages

```php
public function login(Request $request)
{
    // ... authentication logic ...
    
    // Store in session
    session()->put('user_id', $user->id);
    
    // Flash message (show once)
    session()->flash('success', 'Welcome back!');
    
    return redirect('/dashboard');
}

public function logout()
{
    // Remove from session
    session()->forget('user_id');
    
    // Clear all
    session()->flush();
    
    session()->flash('success', 'You have been logged out');
    return redirect('/');
}
```

## Logging

```php
public function store(Request $request)
{
    logger()->info('Creating user', [
        'email' => $request->input('email'),
        'ip' => $request->ip()
    ]);
    
    $user = User::create($request->validated());
    
    logger()->info('User created', ['user_id' => $user->id]);
}
```

## Caching

```php
public function index(Request $request)
{
    $cache = cache();
    
    if (!$cache->has('users')) {
        logger()->info('Cache miss: Loading users');
        $users = User::all();
        $cache->put('users', $users, 3600);
    } else {
        $users = $cache->get('users');
    }
    
    return view('users/index', ['users' => $users]);
}
```

---

[← Back to Docs](./README.md)
