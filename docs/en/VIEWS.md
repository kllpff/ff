# Views Guide

Create beautiful templates with FF Framework.

## Creating Views

Views are PHP files in `app/Views/`:

```php
<!-- app/Views/home.php -->
<h1>Welcome!</h1>
<p>This is a view.</p>
```

## Rendering Views

In controller:

```php
public function index()
{
    return view('home', [
        'title' => 'Home Page',
        'posts' => Post::all(),
    ]);
}
```

Variables are automatically available in the view.

## Layouts

Use layouts for consistent HTML structure:

```php
<!-- app/Views/layouts/app.php -->
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title ?? 'App'; ?></title>
</head>
<body>
    <header>Header</header>
    <main>
        <?php echo $__content; ?>
    </main>
    <footer>Footer</footer>
</body>
</html>
```

Use layout in view:

```php
<h1>Page Title</h1>
<p>Page content...</p>
```

## Escaping Output

Always escape user input:

```php
<!-- Using h() helper -->
<p><?php echo h($user->name); ?></p>

<!-- Or htmlspecialchars -->
<p><?php echo htmlspecialchars($user->bio, ENT_QUOTES); ?></p>
```

## Conditionals

```php
<?php if ($user): ?>
    <p>Welcome, <?php echo h($user->name); ?></p>
<?php else: ?>
    <p>Please login</p>
<?php endif; ?>

<?php echo $admin ? 'Admin' : 'User'; ?>
```

## Loops

```php
<!-- foreach -->
<?php foreach ($users as $user): ?>
    <p><?php echo h($user->name); ?></p>
<?php endforeach; ?>

<!-- for -->
<?php for ($i = 0; $i < 5; $i++): ?>
    <p>Item <?php echo $i; ?></p>
<?php endfor; ?>
```

## Forms

```html
<form method="POST" action="/users">
    {{ csrf_field() }}
    
    <input 
        type="text"
        name="name"
        value="<?php echo old('name'); ?>"
    />
    
    <?php if ($errors->has('name')): ?>
        <span class="error"><?php echo $errors->first('name'); ?></span>
    <?php endif; ?>
    
    <button type="submit">Submit</button>
</form>
```

## Flash Messages

```php
<!-- In controller -->
session()->flash('success', 'User created!');

<!-- In view -->
<?php if (session()->has('success')): ?>
    <div class="alert">
        <?php echo session('success'); ?>
    </div>
<?php endif; ?>
```

## URL Generation

```php
<!-- To route -->
<a href="<?php echo route('users.index'); ?>">Users</a>
<a href="<?php echo route('users.show', ['id' => 1]); ?>">User 1</a>

<!-- To URL -->
<a href="<?php echo url('/users'); ?>">Users</a>
```

## Including Other Views

```php
<!-- Include another view -->
<?php include view('partials/header'); ?>
```

## Complete Example

```php
<!-- app/Views/users/index.php -->

<h1>Users</h1>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success">
        <?php echo session('success'); ?>
    </div>
<?php endif; ?>

<a href="<?php echo route('users.create'); ?>" class="btn">Add User</a>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo h($user->name); ?></td>
                <td><?php echo h($user->email); ?></td>
                <td>
                    <a href="<?php echo route('users.show', ['id' => $user->id]); ?>">
                        View
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

---

[← Back to Docs](./README.md)
