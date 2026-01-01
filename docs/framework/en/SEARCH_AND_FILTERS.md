# Search and Filters

FF Framework includes a built-in search and filtering system designed for admin panels and data management interfaces.

## Table of Contents

- [Overview](#overview)
- [Basic Usage](#basic-usage)
- [Implementation Examples](#implementation-examples)
- [UI Components](#ui-components)
- [Integration with Pagination](#integration-with-pagination)
- [Best Practices](#best-practices)

## Overview

The search and filtering system provides:
- **Text search** with `LIKE` operator for partial matches
- **Multiple filters** that can be combined
- **Query parameter preservation** across pagination
- **Clear/Reset functionality** to remove all filters
- **Automatic logging** for debugging

## Basic Usage

### Controller Implementation

```php
use FF\Http\Request;
use App\Models\Post;

class PostController
{
    public function index(Request $request): Response
    {
        $query = Post::query();

        // Text search
        if ($search = $request->get('search')) {
            $query->where('title', 'LIKE', "%{$search}%");
        }

        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', '=', $status);
        }

        // Category filter
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', '=', (int)$categoryId);
        }

        // Get results with pagination
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

### View Implementation

```php
<!-- app/Views/posts/index.php -->

<!-- Search and Filters Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/posts" class="row g-3">
            <!-- Search Field -->
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?php echo h($filters['search'] ?? ''); ?>"
                       placeholder="Search by title...">
            </div>

            <!-- Status Filter -->
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="draft" <?php echo ($filters['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>
                        Draft
                    </option>
                    <option value="published" <?php echo ($filters['status'] ?? '') === 'published' ? 'selected' : ''; ?>>
                        Published
                    </option>
                </select>
            </div>

            <!-- Category Filter -->
            <div class="col-md-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo h($category->id); ?>"
                        <?php echo ($filters['category_id'] ?? '') == $category->id ? 'selected' : ''; ?>>
                        <?php echo h($category->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['category_id'])): ?>
                    <a href="/posts" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Results Table -->
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

<!-- Pagination (automatically preserves filters) -->
<?php echo $posts->links(); ?>
```

## Implementation Examples

### Example 1: Simple Text Search

```php
// Controller
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
<!-- View -->
<form method="GET" action="/categories">
    <input type="text" name="search" value="<?php echo h($filters['search'] ?? ''); ?>"
           placeholder="Search categories...">
    <button type="submit">Search</button>
    <?php if (!empty($filters['search'])): ?>
        <a href="/categories">Clear</a>
    <?php endif; ?>
</form>
```

### Example 2: Multiple Field Search

```php
// Controller
public function index(Request $request): Response
{
    $query = User::query();

    // Search in multiple fields
    if ($search = $request->get('search')) {
        $query->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%");
    }

    // Role filter
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

### Example 3: Date Range Filter

```php
// Controller
public function index(Request $request): Response
{
    $query = Post::query();

    // Date range filter
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
<!-- View -->
<form method="GET">
    <input type="date" name="date_from" value="<?php echo h($filters['date_from'] ?? ''); ?>">
    <input type="date" name="date_to" value="<?php echo h($filters['date_to'] ?? ''); ?>">
    <button type="submit">Filter</button>
</form>
```

## UI Components

### Bootstrap 5 Styling

The framework uses Bootstrap 5 classes for consistent styling:

```php
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search">
            </div>
            <div class="col-md-3">
                <label class="form-label">Filter</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>
```

### Icons

Use Bootstrap Icons for visual clarity:

```php
<!-- Search icon -->
<svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
</svg>

<!-- Clear icon -->
<svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
</svg>
```

## Integration with Pagination

Filters automatically work with pagination - the Paginator preserves query parameters:

```php
// Controller
$posts = $query->paginate(20); // Filters are preserved

// In view - pagination links automatically include filter parameters
<?php echo $posts->links(); ?>

// Generated URLs will include filters:
// /posts?search=example&status=draft&page=2
```

### Manual URL Building

If you need to build URLs manually:

```php
$baseUrl = '/posts';
$params = array_filter([
    'search' => $filters['search'] ?? null,
    'status' => $filters['status'] ?? null,
    'page' => 2
]);

$url = $baseUrl . '?' . http_build_query($params);
```

## Best Practices

### 1. Always Sanitize Input

```php
// Bad
$query->where('title', 'LIKE', "%{$_GET['search']}%");

// Good
$search = $request->get('search');
if ($search) {
    $query->where('title', 'LIKE', "%{$search}%");
}
```

### 2. Use Type Casting for IDs

```php
// Good
if ($categoryId = $request->get('category_id')) {
    $query->where('category_id', '=', (int)$categoryId);
}
```

### 3. Log Filter Usage

```php
if ($search = $request->get('search')) {
    $query->where('title', 'LIKE', "%{$search}%");
    $this->logger->debug('Searching posts', ['search' => $search]);
}
```

### 4. Provide Clear Reset Functionality

```php
<!-- Show clear button only when filters are active -->
<?php if (!empty($filters['search']) || !empty($filters['status'])): ?>
    <a href="/posts" class="btn btn-outline-secondary">Clear Filters</a>
<?php endif; ?>
```

### 5. Handle Empty Results

```php
<?php if (empty($posts->items())): ?>
    <div class="alert alert-info">
        No results found. Try adjusting your filters.
    </div>
<?php else: ?>
    <!-- Display results -->
<?php endif; ?>
```

### 6. Use Validation for Complex Filters

```php
// Validate date format
if ($from = $request->get('date_from')) {
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        $query->where('created_at', '>=', $from);
    }
}
```

### 7. Preserve Filter State in Forms

```php
<!-- Always preserve filter values in form fields -->
<input type="text" name="search" value="<?php echo h($filters['search'] ?? ''); ?>">

<select name="status">
    <option value="">All</option>
    <option value="draft" <?php echo ($filters['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>
        Draft
    </option>
</select>
```

## Complete Example: Admin Posts

```php
// app/Controllers/Admin/PostController.php
public function index(Request $request): Response
{
    if ($redirect = $this->ensureAdmin()) {
        return $redirect;
    }

    $query = Post::query();

    // Search
    if ($search = $request->get('search')) {
        $query->where('title', 'LIKE', "%{$search}%");
        $this->logger->debug('Searching posts', ['search' => $search]);
    }

    // Status filter
    if ($status = $request->get('status')) {
        $query->where('status', '=', $status);
        $this->logger->debug('Filtering by status', ['status' => $status]);
    }

    // Category filter
    if ($categoryId = $request->get('category_id')) {
        $query->where('category_id', '=', (int)$categoryId);
        $this->logger->debug('Filtering by category', ['category_id' => $categoryId]);
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
        'title' => 'Manage Posts'
    ]));
}
```

## See Also

- [Pagination Documentation](PAGINATION.md)
- [Database Documentation](DATABASE.md)
- [QueryBuilder Documentation](DATABASE.md#query-builder)
- [Admin Panel Documentation](../../ADMIN_PANEL.md)
