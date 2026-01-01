# Поиск и фильтры

FF Framework включает встроенную систему поиска и фильтрации, разработанную для админ-панелей и интерфейсов управления данными.

## Содержание

- [Обзор](#обзор)
- [Базовое использование](#базовое-использование)
- [Примеры реализации](#примеры-реализации)
- [UI компоненты](#ui-компоненты)
- [Интеграция с пагинацией](#интеграция-с-пагинацией)
- [Лучшие практики](#лучшие-практики)

## Обзор

Система поиска и фильтрации предоставляет:
- **Текстовый поиск** с оператором `LIKE` для частичных совпадений
- **Множественные фильтры**, которые можно комбинировать
- **Сохранение параметров запроса** при пагинации
- **Функционал очистки/сброса** для удаления всех фильтров
- **Автоматическое логирование** для отладки

## Базовое использование

### Реализация в контроллере

```php
use FF\Http\Request;
use App\Models\Post;

class PostController
{
    public function index(Request $request): Response
    {
        $query = Post::query();

        // Текстовый поиск
        if ($search = $request->get('search')) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        // Фильтр по статусу
        if ($status = $request->get('status')) {
            $query->where('status', '=', $status);
        }

        // Фильтр по категории
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', '=', (int)$categoryId);
        }

        // Получение результатов с пагинацией
        $posts = $query->orderBy('created_at', 'DESC')->paginate(20);

        return response(view('posts/index', [
            'posts' => $posts,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
                'category_id' => $categoryId ?? ''
            ]
        ]));
    }
}
```

### Реализация в представлении

```php
<!-- app/Views/posts/index.php -->

<!-- Форма поиска и фильтров -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/posts" class="row g-3">
            <!-- Поле поиска -->
            <div class="col-md-4">
                <label for="search" class="form-label">Поиск</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?php echo h($filters['search'] ?? ''); ?>"
                       placeholder="Поиск по заголовку...">
            </div>

            <!-- Фильтр статуса -->
            <div class="col-md-3">
                <label for="status" class="form-label">Статус</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Все статусы</option>
                    <option value="draft" <?php echo ($filters['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>
                        Черновик
                    </option>
                    <option value="published" <?php echo ($filters['status'] ?? '') === 'published' ? 'selected' : ''; ?>>
                        Опубликовано
                    </option>
                </select>
            </div>

            <!-- Фильтр категории -->
            <div class="col-md-3">
                <label for="category_id" class="form-label">Категория</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Все категории</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo h($category->id); ?>"
            <?php echo ($filters['category_id'] ?? '') == $category->id ? 'selected' : ''; ?>>
            <?php echo h($category->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Кнопки -->
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Применить</button>
                <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['category_id'])): ?>
                    <a href="/posts" class="btn btn-outline-secondary">Очистить</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Таблица результатов -->
<div class="card">
    <table class="table">
        <?php foreach ($posts->items() as $post): ?>
            <tr>
                <td><?php echo h($post->title); ?></td>
        <td><?php echo h($post->status); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Пагинация (автоматически сохраняет фильтры) -->
<?php echo $posts->links(); ?>
```

## Примеры реализации

### Пример 1: Простой текстовый поиск

```php
// Контроллер
public function index(Request $request): Response
{
    $query = Category::query();

    if ($search = $request->get('search')) {
        $query->where('name', 'LIKE', "%{$search}%");
    }

    $categories = $query->orderBy('name', 'ASC')->paginate(20);

    return response(view('categories/index', [
        'categories' => $categories,
        'filters' => ['search' => $search ?? '']
    ]));
}
```

```php
<!-- Представление -->
<form method="GET" action="/categories">
    <input type="text" name="search" value="<?php echo h($filters['search'] ?? ''); ?>"
           placeholder="Поиск категорий...">
    <button type="submit">Найти</button>
    <?php if (!empty($filters['search'])): ?>
        <a href="/categories">Очистить</a>
    <?php endif; ?>
</form>
```

### Пример 2: Поиск по нескольким полям

```php
// Контроллер
public function index(Request $request): Response
{
    $query = User::query();

    // Поиск по нескольким полям
    if ($search = $request->get('search')) {
        $query->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%");
    }

    // Фильтр по роли
    if ($role = $request->get('role')) {
        if ($role === 'admin') {
            $query->where('is_admin', '=', 1);
        } elseif ($role === 'user') {
            $query->where('is_admin', '=', 0);
        }
    }

    $users = $query->orderBy('created_at', 'DESC')->paginate(20);

    return response(view('users/index', [
        'users' => $users,
        'filters' => [
            'search' => $search ?? '',
            'role' => $role ?? ''
        ]
    ]));
}
```

### Пример 3: Фильтр по диапазону дат

```php
// Контроллер
public function index(Request $request): Response
{
    $query = Post::query();

    // Фильтр по диапазону дат
    if ($from = $request->get('date_from')) {
        $query->where('created_at', '>=', $from);
    }

    if ($to = $request->get('date_to')) {
        $query->where('created_at', '<=', $to . ' 23:59:59');
    }

    $posts = $query->orderBy('created_at', 'DESC')->paginate(20);

    return response(view('posts/index', [
        'posts' => $posts,
        'filters' => [
            'date_from' => $from ?? '',
            'date_to' => $to ?? ''
        ]
    ]));
}
```

```php
<!-- Представление -->
<form method="GET">
    <input type="date" name="date_from" value="<?php echo h($filters['date_from'] ?? ''); ?>">
    <input type="date" name="date_to" value="<?php echo h($filters['date_to'] ?? ''); ?>">
    <button type="submit">Применить</button>
</form>
```

## UI компоненты

### Стилизация Bootstrap 5

Фреймворк использует классы Bootstrap 5 для единообразного стиля:

```php
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Поиск</label>
                <input type="text" class="form-control" name="search">
            </div>
            <div class="col-md-3">
                <label class="form-label">Фильтр</label>
                <select class="form-select" name="status">
                    <option value="">Все</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Применить</button>
            </div>
        </form>
    </div>
</div>
```

### Иконки

Используйте Bootstrap Icons для визуальной ясности:

```php
<!-- Иконка поиска -->
<svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
</svg>

<!-- Иконка очистки -->
<svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
</svg>
```

## Интеграция с пагинацией

Фильтры автоматически работают с пагинацией - Paginator сохраняет параметры запроса:

```php
// Контроллер
$posts = $query->paginate(20); // Фильтры сохраняются

// В представлении - ссылки пагинации автоматически включают параметры фильтров
<?php echo $posts->links(); ?>

// Сгенерированные URL будут включать фильтры:
// /posts?search=example&status=draft&page=2
```

### Ручное построение URL

Если вам нужно строить URL вручную:

```php
$baseUrl = '/posts';
$params = array_filter([
    'search' => $filters['search'] ?? null,
    'status' => $filters['status'] ?? null,
    'page' => 2
]);

$url = $baseUrl . '?' . http_build_query($params);
```

## Лучшие практики

### 1. Всегда очищайте входные данные

```php
// Плохо
$query->where('title', 'LIKE', "%{$_GET['search']}%");

// Хорошо
$search = $request->get('search');
if ($search) {
    $query->where('title', 'LIKE', "%{$search}%");
}
```

### 2. Используйте приведение типов для ID

```php
// Хорошо
if ($categoryId = $request->get('category_id')) {
    $query->where('category_id', '=', (int)$categoryId);
}
```

### 3. Логируйте использование фильтров

```php
if ($search = $request->get('search')) {
    $query->where('title', 'LIKE', "%{$search}%");
    $this->logger->debug('Поиск постов', ['search' => $search]);
}
```

### 4. Предоставьте четкий функционал сброса

```php
<!-- Показывать кнопку очистки только когда фильтры активны -->
<?php if (!empty($filters['search']) || !empty($filters['status'])): ?>
    <a href="/posts" class="btn btn-outline-secondary">Очистить фильтры</a>
<?php endif; ?>
```

### 5. Обрабатывайте пустые результаты

```php
<?php if (empty($posts->items())): ?>
    <div class="alert alert-info">
        Результатов не найдено. Попробуйте изменить фильтры.
    </div>
<?php else: ?>
    <!-- Отображение результатов -->
<?php endif; ?>
```

### 6. Используйте валидацию для сложных фильтров

```php
// Проверка формата даты
if ($from = $request->get('date_from')) {
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        $query->where('created_at', '>=', $from);
    }
}
```

### 7. Сохраняйте состояние фильтров в формах

```php
<!-- Всегда сохраняйте значения фильтров в полях формы -->
<input type="text" name="search" value="<?php echo h($filters['search'] ?? ''); ?>">

<select name="status">
    <option value="">Все</option>
    <option value="draft" <?php echo ($filters['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>
        Черновик
    </option>
</select>
```

## Полный пример: Админ-панель постов

```php
// app/Controllers/Admin/PostController.php
public function index(Request $request): Response
{
    if ($redirect = $this->ensureAdmin()) {
        return $redirect;
    }

    $query = Post::query();

    // Поиск
    if ($search = $request->get('search')) {
        $query->where('title', 'LIKE', "%{$search}%");
        $this->logger->debug('Поиск постов', ['search' => $search]);
    }

    // Фильтр по статусу
    if ($status = $request->get('status')) {
        $query->where('status', '=', $status);
        $this->logger->debug('Фильтрация по статусу', ['status' => $status]);
    }

    // Фильтр по категории
    if ($categoryId = $request->get('category_id')) {
        $query->where('category_id', '=', (int)$categoryId);
        $this->logger->debug('Фильтрация по категории', ['category_id' => $categoryId]);
    }

    $posts = $query->orderBy('created_at', 'DESC')->paginate(20);
    $categories = Category::query()->orderBy('name', 'ASC')->get();

    return response(view('admin.posts.index', [
        '__layout' => 'admin/layouts/app',
        'posts' => $posts,
        'categories' => $categories,
        'filters' => [
            'search' => $search ?? '',
            'status' => $status ?? '',
            'category_id' => $categoryId ?? ''
        ],
        'title' => 'Управление постами'
    ]));
}
```

## См. также

- [Документация по пагинации](PAGINATION.md)
- [Документация по базе данных](DATABASE.md)
- [Документация по QueryBuilder](DATABASE.md#query-builder)
- [Документация по админ-панели](../../ADMIN_PANEL.md)
