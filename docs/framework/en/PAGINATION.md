# Pagination

FF Framework includes a powerful pagination system for displaying large datasets across multiple pages.

## Table of Contents

- [Basic Usage](#basic-usage)
- [Paginator Methods](#paginator-methods)
- [Displaying Pagination](#displaying-pagination)
- [Customization](#customization)
- [Advanced Usage](#advanced-usage)

## Basic Usage

### Using paginate() Method

The simplest way to paginate results is using the `paginate()` method on your QueryBuilder:

```php
use App\Models\Post;

class PostController
{
    public function index()
    {
        // Get 20 posts per page
        $posts = Post::query()
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return view('posts/index', [
            'posts' => $posts
        ]);
    }
}
```

### In Views

The paginator returns a `Paginator` object instead of a plain array. Use `items()` to access the actual data:

```php
<!-- app/Views/posts/index.php -->

<?php foreach ($posts->items() as $post): ?>
    <article>
        <h2><?php echo h($post->title); ?></h2>
        <p><?php echo h($post->excerpt); ?></p>
    </article>
<?php endforeach; ?>

<!-- Pagination info -->
<div class="pagination-info">
    <?php echo $posts->info(); ?>
</div>

<!-- Pagination links -->
<?php if ($posts->lastPage() > 1): ?>
    <?php echo $posts->links(); ?>
<?php endif; ?>
```

## Paginator Methods

### Data Access

```php
// Get items for current page
$items = $posts->items();

// Get total number of items
$total = $posts->total();

// Get items per page
$perPage = $posts->perPage();
```

### Page Information

```php
// Current page number
$currentPage = $posts->currentPage();

// Last page number
$lastPage = $posts->lastPage();

// First item number on current page
$firstItem = $posts->firstItem();

// Last item number on current page
$lastItem = $posts->lastItem();
```

### Navigation

```php
// Check if there are more pages
if ($posts->hasMorePages()) {
    echo "There are more pages";
}

// Check if there are previous pages
if ($posts->hasPreviousPages()) {
    echo "There are previous pages";
}

// Get URL for next page
$nextUrl = $posts->nextPageUrl(); // Returns null if no next page

// Get URL for previous page
$prevUrl = $posts->previousPageUrl(); // Returns null if no previous page

// Get URL for specific page
$pageUrl = $posts->url(5); // URL for page 5
```

### Display Helpers

```php
// Get pagination info text
echo $posts->info();
// Output: "Showing 1 to 20 of 100 items"

// Get HTML pagination links
echo $posts->links();
// Output: Bootstrap-compatible pagination HTML
```

## Displaying Pagination

### Basic Pagination Display

```php
<!-- Simple pagination with info -->
<div class="pagination-wrapper">
    <div class="pagination-info">
        <small><?php echo $posts->info(); ?></small>

        <?php if ($posts->lastPage() > 1): ?>
            <div class="pagination-links">
                <?php echo $posts->links(); ?>
            </div>
        <?php endif; ?>
</div>
```

### With Bootstrap

The `links()` method generates Bootstrap-compatible HTML by default:

```php
<!-- Using Bootstrap 5 classes -->
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

### Generated HTML Structure

The `links()` method generates HTML like this:

```html
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <!-- Previous button -->
        <li class="page-item disabled">
            <span class="page-link">Previous</span>
        </li>

        <!-- Page numbers -->
        <li class="page-item active">
            <span class="page-link">1</span>
        </li>
        <li class="page-item">
            <a class="page-link" href="/posts?page=2">2</a>
        </li>
        <li class="page-item">
            <a class="page-link" href="/posts?page=3">3</a>
        </li>

        <!-- Ellipsis for large page counts -->
        <li class="page-item disabled">
            <span class="page-link">...</span>
        </li>

        <!-- Last page -->
        <li class="page-item">
            <a class="page-link" href="/posts?page=10">10</a>
        </li>

        <!-- Next button -->
        <li class="page-item">
            <a class="page-link" href="/posts?page=2">Next</a>
        </li>
    </ul>
</nav>
```

## Customization

### Custom Items Per Page

```php
// Default is 15 items per page
$posts = Post::query()->paginate();

// Custom number of items
$posts = Post::query()->paginate(50); // 50 items per page
```

### With Query Parameters

The paginator preserves existing query parameters:

```php
// URL: /posts?category=tech&page=2
$posts = Post::query()
    ->where('category', '=', 'tech')
    ->paginate(20);

// Pagination links will include ?category=tech&page=X
```

### Manual Paginator Creation

For advanced use cases, you can create a Paginator manually:

```php
use FF\Pagination\Paginator;

$items = [...]; // Your items for current page
$total = 100;   // Total number of items
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

## Advanced Usage

### Custom Page Range

By default, the paginator shows 3 pages on each side of the current page. You can customize this:

```php
// In your Paginator class extension
$pageRange = $paginator->getPageRange(5); // Show 5 pages on each side
```

### AJAX Pagination

For AJAX pagination, you can use the paginator's data methods:

```php
// In your controller
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

### Empty State Handling

```php
<!-- In your view -->
<?php if (empty($posts->items())): ?>
    <div class="alert alert-info">
        No posts found.
    </div>
<?php else: ?>
    <?php foreach ($posts->items() as $post): ?>
        <!-- Display post -->
    <?php endforeach; ?>

    <!-- Pagination -->
    <?php if ($posts->lastPage() > 1): ?>
        <?php echo $posts->links(); ?>
    <?php endif; ?>
<?php endif; ?>
```

### Performance Considerations

The `paginate()` method executes two queries:

1. `COUNT(*)` to get total items
2. `SELECT ... LIMIT X OFFSET Y` to get items for current page

For better performance with large datasets:

```php
// Add indexes on columns used in WHERE and ORDER BY
CREATE INDEX idx_created_at ON posts(created_at);
CREATE INDEX idx_status ON posts(status);

// Optimize queries
$posts = Post::query()
    ->where('status', '=', 'published') // Indexed column
    ->orderBy('created_at', 'DESC')     // Indexed column
    ->paginate(20);
```

## Example: Complete Pagination Implementation

```php
// Controller
class PostController
{
    public function index(Request $request)
    {
        $query = Post::query()->where('status', '=', 'published');

        // Filter by category if provided
        if ($category = $request->get('category')) {
            $query->where('category', '=', $category);
        }

        // Search if provided
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
<!-- View: app/Views/posts/index.php -->
<div class="container">
    <h1>Blog Posts</h1>

    <!-- Search Form -->
    <form method="GET" class="mb-4">
        <input type="text" name="search" value="<?php echo h($search ?? ''); ?>" placeholder="Search...">
        <button type="submit">Search</button>
    </form>

    <!-- Posts -->
    <?php if (empty($posts->items())): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <?php foreach ($posts->items() as $post): ?>
            <article class="post">
                <h2><?php echo h($post->title); ?></h2>
        <p><?php echo h($post->excerpt); ?></p>
        <a href="/posts/<?php echo h($post->slug); ?>">Read more</a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pagination -->
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

## API Reference

### FF\Pagination\Paginator

#### Constructor

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

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `items()` | `array` | Get items for current page |
| `total()` | `int` | Get total number of items |
| `perPage()` | `int` | Get items per page |
| `currentPage()` | `int` | Get current page number |
| `lastPage()` | `int` | Get last page number |
| `firstItem()` | `int` | Get first item number on current page |
| `lastItem()` | `int` | Get last item number on current page |
| `hasMorePages()` | `bool` | Check if there are more pages |
| `hasPreviousPages()` | `bool` | Check if there are previous pages |
| `url(int $page)` | `string` | Get URL for specific page |
| `nextPageUrl()` | `string\|null` | Get URL for next page |
| `previousPageUrl()` | `string\|null` | Get URL for previous page |
| `getPageRange(int $onEachSide = 3)` | `array` | Get array of page numbers to display |
| `links()` | `string` | Render HTML pagination links |
| `info()` | `string` | Get pagination info text |

## See Also

- [Database Documentation](DATABASE.md)
- [Models Documentation](MODELS.md)
- [QueryBuilder Documentation](DATABASE.md#query-builder)
