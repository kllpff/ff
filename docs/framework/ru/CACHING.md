# Руководство по кешированию

Повышайте производительность с помощью кеширования.

## Основное кеширование

```php
$cache = cache();

// Сохранить в кеш (TTL в секундах)
$cache->put('users', $users, 3600);  // 1 час

// Получить из кеша
$users = $cache->get('users');

// С значением по умолчанию
$users = $cache->get('users', []);

// Проверить существование
if ($cache->has('users')) {
    $users = $cache->get('users');
}

// Удалить
$cache->forget('users');

// Очистить все
$cache->flush();
```

## Обычный паттерн

```php
public function index()
{
    $cache = cache();
    $cacheKey = 'posts:all';
    
    // Сначала попробовать кеш
    $posts = $cache->get($cacheKey);
    
    if (!$posts) {
        logger()->info('Промах кеша: Загрузка постов');
        $posts = Post::all();
        $cache->put($cacheKey, $posts, 3600);
    }
    
    return view('posts/index', ['posts' => $posts]);
}
```

## Аннулирование кеша

Аннулирование кеша при изменении данных:

```php
public function store(Request $request)
{
    $post = Post::create($request->validated());
    
    // Аннулировать связанные кеши
    cache()->forget('posts:all');
    cache()->forget('posts:published');
    
    logger()->debug('Кеш постов аннулирован');
    
    return redirect('/posts');
}
```

---

[← Назад к документации](./README.md)
