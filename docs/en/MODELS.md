# Models Guide

Models represent database tables and allow you to interact with data.

## Creating a Model

Models are in `app/Models/`:

```php
// app/Models/User.php
<?php

namespace App\Models;

use FF\Framework\Database\Model;

class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'password',
    ];

    protected array $hidden = [
        'password',
    ];
}
```

## Basic Operations

### Create

```php
// Create and save
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('secret'),
]);

// Or instantiate and save
$user = new User();
$user->name = 'Jane Doe';
$user->save();
```

### Read

```php
// Get all
$users = User::all();

// Get with condition
$user = User::where('email', 'john@example.com')->first();

// Get by ID
$user = User::find(1);

// Get or fail
$user = User::findOrFail(1);

// Count
$count = User::count();

// Check existence
$exists = User::where('email', 'john@example.com')->exists();
```

### Update

```php
$user = User::find(1);
$user->update([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
]);

// Or update multiple
User::where('status', 'inactive')
    ->update(['status' => 'active']);
```

### Delete

```php
$user = User::find(1);
$user->delete();

// Or delete multiple
User::where('status', 'inactive')->delete();
```

## Querying

Build complex queries using QueryBuilder:

```php
// WHERE conditions
User::where('status', 'active')
    ->where('verified', true)
    ->get();

// OR condition
User::where('role', 'admin')
    ->orWhere('admin', true)
    ->get();

// IN operator
User::whereIn('role', ['admin', 'moderator'])->get();

// Ordering
User::orderBy('created_at', 'desc')->get();

// Limiting
User::limit(10)->skip(20)->get();

// Select specific columns
User::select('id', 'name', 'email')->get();

// Count
$count = User::count();

// Sum/Average/Min/Max
Post::sum('views');
Review::avg('rating');
Order::min('price');
Order::max('price');
```

## Relationships

### Define Relationships

```php
// app/Models/User.php
class User extends Model
{
    // One to Many
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}

// app/Models/Post.php
class Post extends Model
{
    // Belongs To
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

### Use Relationships

```php
// Get related models
$user = User::find(1);
$posts = $user->posts;

// Query relationships
$user->posts()
    ->where('published', true)
    ->get();

// Create related
$user->posts()->create([
    'title' => 'My Post',
    'content' => '...',
]);
```

## Attributes

### Mass Assignment

```php
class User extends Model
{
    protected array $fillable = [
        'name',
        'email',
        'password',
    ];
}

// Only fillable fields can be filled
$user = User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'admin' => true,  // Ignored - not in fillable
]);

// Or use fill
$user->fill([
    'name' => 'Jane',
    'email' => 'jane@example.com',
]);
$user->save();
```

### Accessors & Mutators

```php
class User extends Model
{
    // Accessor - when getting
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    // Mutator - when setting
    public function setEmailAttribute($value)
    {
        return strtolower($value);
    }
}

// Usage
$user = User::find(1);
echo $user->name;  // Capitalized
$user->email = 'JOHN@EXAMPLE.COM';
echo $user->email;  // Lowercase
```

## Transactions

Execute operations atomically:

```php
$user = User::transaction(function() {
    $user = User::create([...]);
    $user->posts()->create([...]);
    return $user;
});
```

## Scopes

Create reusable query filters:

```php
class Post extends Model
{
    // Query scope
    public static function published()
    {
        return static::where('published', true);
    }
}

// Usage
$posts = Post::published()->get();
$recent = Post::published()->latest()->limit(5)->get();
```

## Complete Example

```php
// app/Models/Post.php
class Post extends Model
{
    protected string $table = 'posts';
    protected array $fillable = ['title', 'content', 'published'];

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public static function published()
    {
        return static::where('published', true);
    }
}

// Usage in controller
$posts = Post::published()
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

foreach ($posts as $post) {
    echo $post->title;
    echo $post->author->name;  // Access relationship
}
```

---

[← Back to Docs](./README.md)
