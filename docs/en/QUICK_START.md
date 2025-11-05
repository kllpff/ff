# Quick Start Guide

Get your first FF application running in 5 minutes.

## 1. Create a Route

```php
// config/routes.php
$router = app(\FF\Framework\Http\Router::class);

$router->get('/hello/{name}', 'HelloController@greet')->name('hello');
```

## 2. Create a Controller

```php
// app/Controllers/HelloController.php
<?php

namespace App\Controllers;

use FF\Framework\Http\Request;

class HelloController
{
    public function greet($name)
    {
        return view('hello', ['name' => $name]);
    }
}
```

## 3. Create a View

```php
<!-- app/Views/hello.php -->
<?php $__layout = 'app'; ?>

<h1>Hello, <?php echo h($name); ?>!</h1>
<p>Welcome to FF Framework</p>
```

## 4. Visit in Browser

Navigate to: **http://localhost:8000/hello/John**

You should see:
```
Hello, John!
Welcome to FF Framework
```

## Working with Database

### 1. Create a Model

```php
// app/Models/Post.php
<?php

namespace App\Models;

use FF\Framework\Database\Model;

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

### 2. Query Database

```php
// In your controller
$posts = Post::all();                          // Get all
$post = Post::find(1);                         // Get by ID
$published = Post::where('published', true)    // Query
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

### 3. Create/Update/Delete

```php
// Create
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'Amazing content...',
    'published' => true,
]);

// Update
$post->update(['title' => 'Updated Title']);

// Delete
$post->delete();
```

## Form Handling

### 1. Create Form

```html
<!-- app/Views/posts/create.php -->
<?php $__layout = 'app'; ?>

<form method="POST" action="/posts">
    {{ csrf_field() }}
    
    <div>
        <label>Title</label>
        <input 
            type="text" 
            name="title" 
            value="{{ old('title') }}"
            required
        />
    </div>
    
    <div>
        <label>Content</label>
        <textarea name="content" required></textarea>
    </div>
    
    <button type="submit">Create</button>
</form>
```

### 2. Handle Submission

```php
// In controller
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string|min:10',
    ]);
    
    Post::create($validated);
    
    session()->flash('success', 'Post created!');
    return redirect('/posts');
}
```

## Authentication

### 1. Login

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
    
    return redirect('/login')->with('error', 'Invalid credentials');
}
```

### 2. Check if Logged In

```php
// In view
<?php if (session()->has('user_id')): ?>
    <p>Welcome, <?php echo $user->name; ?></p>
<?php else: ?>
    <a href="/login">Login</a>
<?php endif; ?>
```

## Common Tasks

### Display Messages

```php
// In controller
session()->flash('success', 'Operation successful!');
session()->flash('error', 'Something went wrong');

// In view
<?php if (session()->has('success')): ?>
    <div class="alert">{{ session('success') }}</div>
<?php endif; ?>
```

### Redirect

```php
// Redirect to URL
return redirect('/home');

// Redirect to named route
return redirect(route('posts.show', ['id' => 1]));

// Redirect back with data
return redirect()->back()->with('data', $value);
```

### Logging

```php
logger()->info('User logged in', ['user_id' => 1]);
logger()->error('Database error', ['error' => $message]);
logger()->debug('Debug info');
```

### Caching

```php
$cache = cache();

// Store in cache (3600 seconds = 1 hour)
$cache->put('posts', $posts, 3600);

// Get from cache
$posts = $cache->get('posts');

// Remove from cache
$cache->forget('posts');
```

## Next Steps

1. **[Routing Guide](./ROUTING.md)** - Learn advanced routing
2. **[Database Guide](./DATABASE.md)** - Master the ORM
3. **[Validation Guide](./VALIDATION.md)** - Input validation
4. **[Security Guide](./SECURITY.md)** - Secure your app
5. **[Full Documentation](./README.md)** - Explore all features

## Tips

✅ Always validate user input  
✅ Escape output in views with `h()`  
✅ Use named routes for flexibility  
✅ Cache frequently accessed data  
✅ Log important events  
✅ Follow PSR-12 coding standard  

---

**Ready to build?** Start with the [Routing Guide](./ROUTING.md)!
