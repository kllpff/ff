# Пагинация

FF Framework включает мощную систему пагинации для отображения больших наборов данных на нескольких страницах.

## Содержание

- [Базовое использование](#базовое-использование)
- [Методы Paginator](#методы-paginator)
- [Отображение пагинации](#отображение-пагинации)
- [Настройка](#настройка)
- [Продвинутое использование](#продвинутое-использование)

## Базовое использование

### Использование метода paginate()

Самый простой способ разбить результаты на страницы — использовать метод `paginate()` в QueryBuilder:

```php
use App\Models\Post;

class PostController
{
    public function index()
    {
        // Получить 20 постов на странице
        $posts = Post::query()
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return view('posts/index', [
            'posts' => $posts
        ]);
    }
}
```

### В представлениях

Paginator возвращает объект `Paginator` вместо обычного массива. Используйте `items()` для доступа к данным:

```php
<!-- app/Views/posts/index.php -->

<?php foreach ($posts->items() as $post): ?>
    <article>
        <h2><?php echo h($post->title); ?></h2>
        <p><?php echo h($post->excerpt); ?></p>
    </article>
<?php endforeach; ?>

<!-- Информация о пагинации -->
<div class="pagination-info">
    <?php echo $posts->info(); ?>
</div>

<!-- Ссылки пагинации -->
<?php if ($posts->lastPage() > 1): ?>
    <?php echo $posts->links(); ?>
<?php endif; ?>
```

## Методы Paginator

### Доступ к данным

```php
// Получить элементы текущей страницы
$items = $posts->items();

// Получить общее количество элементов
$total = $posts->total();

// Получить количество элементов на странице
$perPage = $posts->perPage();
```

### Информация о странице

```php
// Номер текущей страницы
$currentPage = $posts->currentPage();

// Номер последней страницы
$lastPage = $posts->lastPage();

// Номер первого элемента на текущей странице
$firstItem = $posts->firstItem();

// Номер последнего элемента на текущей странице
$lastItem = $posts->lastItem();
```

### Навигация

```php
// Проверить, есть ли еще страницы
if ($posts->hasMorePages()) {
    echo "Есть еще страницы";
}

// Проверить, есть ли предыдущие страницы
if ($posts->hasPreviousPages()) {
    echo "Есть предыдущие страницы";
}

// Получить URL следующей страницы
$nextUrl = $posts->nextPageUrl(); // Вернет null, если нет следующей страницы

// Получить URL предыдущей страницы
$prevUrl = $posts->previousPageUrl(); // Вернет null, если нет предыдущей страницы

// Получить URL конкретной страницы
$pageUrl = $posts->url(5); // URL для страницы 5
```

### Помощники отображения

```php
// Получить текст информации о пагинации
echo $posts->info();
// Вывод: "Showing 1 to 20 of 100 items"

// Получить HTML ссылки пагинации
echo $posts->links();
// Вывод: HTML пагинации, совместимый с Bootstrap
```

## Отображение пагинации

### Базовое отображение пагинации

```php
<!-- Простая пагинация с информацией -->
<div class="pagination-wrapper">
    <div class="pagination-info">
        <small><?php echo $posts->info(); ?></small>
    </div>

    <?php if ($posts->lastPage() > 1): ?>
        <div class="pagination-links">
            <?php echo $posts->links(); ?>
        </div>
    <?php endif; ?>
</div>
```

### С Bootstrap

Метод `links()` генерирует HTML, совместимый с Bootstrap по умолчанию:

```php
<!-- Использование классов Bootstrap 5 -->
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        <small><?php echo $posts->info(); ?></small>

        <?php if ($posts->lastPage() > 1): ?>
            <nav aria-label="Page navigation">
                <?php echo $posts->links(); ?>
            </nav>
        <?php endif; ?>
</div>
```

### Генерируемая HTML структура

Метод `links()` генерирует HTML такого вида:

```html
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <!-- Кнопка "Назад" -->
        <li class="page-item disabled">
            <span class="page-link">Previous</span>
        </li>

        <!-- Номера страниц -->
        <li class="page-item active">
            <span class="page-link">1</span>
        </li>
        <li class="page-item">
            <a class="page-link" href="/posts?page=2">2</a>
        </li>
        <li class="page-item">
            <a class="page-link" href="/posts?page=3">3</a>
        </li>

        <!-- Многоточие для большого количества страниц -->
        <li class="page-item disabled">
            <span class="page-link">...</span>
        </li>

        <!-- Последняя страница -->
        <li class="page-item">
            <a class="page-link" href="/posts?page=10">10</a>
        </li>

        <!-- Кнопка "Вперед" -->
        <li class="page-item">
            <a class="page-link" href="/posts?page=2">Next</a>
        </li>
    </ul>
</nav>
```

## Настройка

### Кастомное количество элементов на странице

```php
// По умолчанию 15 элементов на странице
$posts = Post::query()->paginate();

// Кастомное количество элементов
$posts = Post::query()->paginate(50); // 50 элементов на странице
```

### С параметрами запроса

Paginator сохраняет существующие параметры запроса:

```php
// URL: /posts?category=tech&page=2
$posts = Post::query()
    ->where('category', '=', 'tech')
    ->paginate(20);

// Ссылки пагинации будут включать ?category=tech&page=X
```

### Ручное создание Paginator

For advanced use cases, you can create a Paginator manually:

```php
use FF\Pagination\Paginator;

$items = [...]; // Ваши элементы для текущей страницы
$total = 100;   // Общее количество элементов
$perPage = 20;
$currentPage = 2;
$path = '/posts';
$queryParams = ['category' => 'tech'];

$paginator = new Paginator(
    $items,
    $total,
    $perPage,
    $currentPage,
    $path,
    $queryParams
);
```

## Продвинутое использование

### Кастомный диапазон страниц

По умолчанию paginator показывает 3 страницы с каждой стороны от текущей. Вы можете настроить это:

```php
// В расширении класса Paginator
$pageRange = $paginator->getPageRange(5); // Показать 5 страниц с каждой стороны
```

### AJAX пагинация

Для AJAX пагинации вы можете использовать методы данных paginator:

```php
// В вашем контроллере
public function ajaxIndex(Request $request)
{
    $posts = Post::query()
        ->orderBy('created_at', 'DESC')
        ->paginate(20);

    return response()->json([
        'items' => $posts->items(),
        'pagination' => [
            'current_page' => $posts->currentPage(),
            'last_page' => $posts->lastPage(),
            'per_page' => $posts->perPage(),
            'total' => $posts->total(),
            'next_page_url' => $posts->nextPageUrl(),
            'prev_page_url' => $posts->previousPageUrl(),
        ]
    ]);
}
```

### Обработка пустого состояния

```php
<!-- В вашем представлении -->
<?php if (empty($posts->items())): ?>
    <div class="alert alert-info">
        Постов не найдено.
    </div>
<?php else: ?>
    <?php foreach ($posts->items() as $post): ?>
        <!-- Отображение поста -->
    <?php endforeach; ?>

    <!-- Пагинация -->
    <?php if ($posts->lastPage() > 1): ?>
        <?php echo $posts->links(); ?>
    <?php endif; ?>
<?php endif; ?>
```

### Соображения производительности

Метод `paginate()` выполняет два запроса:

1. `COUNT(*)` для получения общего количества элементов
2. `SELECT ... LIMIT X OFFSET Y` для получения элементов текущей страницы

Для лучшей производительности с большими наборами данных:

```php
// Добавьте индексы на столбцы, используемые в WHERE и ORDER BY
CREATE INDEX idx_created_at ON posts(created_at);
CREATE INDEX idx_status ON posts(status);

// Оптимизируйте запросы
$posts = Post::query()
    ->where('status', '=', 'published') // Индексированный столбец
    ->orderBy('created_at', 'DESC')     // Индексированный столбец
    ->paginate(20);
```

## Пример: Полная реализация пагинации

```php
// Контроллер
class PostController
{
    public function index(Request $request)
    {
        $query = Post::query()->where('status', '=', 'published');

        // Фильтр по категории, если указано
        if ($category = $request->get('category')) {
            $query->where('category', '=', $category);
        }

        // Поиск, если указано
        if ($search = $request->get('search')) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        $posts = $query
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return view('posts/index', [
            'posts' => $posts,
            'category' => $category,
            'search' => $search
        ]);
    }
}
```

```php
<!-- Представление: app/Views/posts/index.php -->
<div class="container">
    <h1>Посты блога</h1>

    <!-- Форма поиска -->
    <form method="GET" class="mb-4">
        <input type="text" name="search" value="<?php echo h($search ?? ''); ?>" placeholder="Поиск...">
        <button type="submit">Поиск</button>
    </form>

    <!-- Посты -->
    <?php if (empty($posts->items())): ?>
        <p>Постов не найдено.</p>
    <?php else: ?>
        <?php foreach ($posts->items() as $post): ?>
            <article class="post">
                <h2><?php echo h($post->title); ?></h2>
        <p><?php echo h($post->excerpt); ?></p>
        <a href="/posts/<?php echo h($post->slug); ?>">Читать далее</a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Пагинация -->
    <div class="pagination-wrapper">
        <div class="pagination-info">
            <?php echo $posts->info(); ?>
            </div>

            <?php if ($posts->lastPage() > 1): ?>
                <div class="pagination-links">
                    <?php echo $posts->links(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
```

## Справочник API

### FF\Pagination\Paginator

#### Конструктор

```php
public function __construct(
    array $items,
    int $total,
    int $perPage = 15,
    int $currentPage = 1,
    string $path = '',
    array $query = []
)
```

#### Методы

| Метод | Тип возврата | Описание |
|-------|--------------|----------|
| `items()` | `array` | Получить элементы текущей страницы |
| `total()` | `int` | Получить общее количество элементов |
| `perPage()` | `int` | Получить количество элементов на странице |
| `currentPage()` | `int` | Получить номер текущей страницы |
| `lastPage()` | `int` | Получить номер последней страницы |
| `firstItem()` | `int` | Получить номер первого элемента на текущей странице |
| `lastItem()` | `int` | Получить номер последнего элемента на текущей странице |
| `hasMorePages()` | `bool` | Проверить, есть ли еще страницы |
| `hasPreviousPages()` | `bool` | Проверить, есть ли предыдущие страницы |
| `url(int $page)` | `string` | Получить URL конкретной страницы |
| `nextPageUrl()` | `string\|null` | Получить URL следующей страницы |
| `previousPageUrl()` | `string\|null` | Получить URL предыдущей страницы |
| `getPageRange(int $onEachSide = 3)` | `array` | Получить массив номеров страниц для отображения |
| `links()` | `string` | Отрендерить HTML ссылки пагинации |
| `info()` | `string` | Получить текст информации о пагинации |

## См. также

- [Документация по базе данных](DATABASE.md)
- [Документация по моделям](MODELS.md)
- [Документация QueryBuilder](DATABASE.md#query-builder)
