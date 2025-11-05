# Caching Guide

Improve performance with caching.

## Basic Caching

```php
$cache = cache();

// Store in cache (TTL in seconds)
$cache->put('users', $users, 3600);  // 1 hour

// Get from cache
$users = $cache->get('users');

// With default
$users = $cache->get('users', []);

// Check if exists
if ($cache->has('users')) {
    $users = $cache->get('users');
}

// Remove
$cache->forget('users');

// Clear all
$cache->flush();
```

## Common Pattern

```php
public function index()
{
    $cache = cache();
    $cacheKey = 'posts:all';
    
    // Try cache first
    $posts = $cache->get($cacheKey);
    
    if (!$posts) {
        logger()->info('Cache miss: Loading posts');
        $posts = Post::all();
        $cache->put($cacheKey, $posts, 3600);
    }
    
    return view('posts/index', ['posts' => $posts]);
}
```

## Cache Invalidation

Invalidate cache when data changes:

```php
public function store(Request $request)
{
    $post = Post::create($request->validated());
    
    // Invalidate related caches
    cache()->forget('posts:all');
    cache()->forget('posts:published');
    
    logger()->debug('Post cache invalidated');
    
    return redirect('/posts');
}
```

---

[← Back to Docs](./README.md)
