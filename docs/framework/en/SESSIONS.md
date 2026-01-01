# Sessions Guide

Manage user sessions and temporary data.

## Basic Session Operations

```php
$session = session();

// Store
$session->put('user_id', 123);
$session->put([
    'user_id' => 123,
    'username' => 'john',
]);

// Get
$userId = $session->get('user_id');
$username = $session->get('username', 'Guest');

// Check
if ($session->has('user_id')) {
    // User is logged in
}

// Remove
$session->forget('user_id');

// Clear all
$session->flush();
```

## Flash Messages

Temporary messages shown once:

```php
// In controller
session()->flash('success', 'User created!');
session()->flash('error', 'An error occurred');

// In view
<?php if (session()->has('success')): ?>
    <div class="alert alert-success">
        <?php echo session('success'); ?>
    </div>
<?php endif; ?>
```

## Helper Functions

```php
// Get session
session('user_id');

// Get old input (for forms)
old('email');

// Flash message
session()->flash('message', 'Saved!');
```

## Security

```php
// Regenerate session ID (after login)
session()->regenerate();

// Invalidate completely (logout)
session()->flush();
```

---

[← Back to Docs](./README.md)
