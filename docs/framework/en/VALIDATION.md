# Validation Guide

Input validation ensures data integrity and security.

## Basic Validation

In controller:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);

    User::create($validated);
}
```

## Validation Rules

### Basic Rules

```php
'field' => 'required'              // Required
'field' => 'string'                // String
'field' => 'integer'               // Integer
'field' => 'numeric'               // Numeric
'field' => 'boolean'               // Boolean
'field' => 'array'                 // Array
```

### String Rules

```php
'password' => 'min:8'              // Min 8 characters
'title' => 'max:255'               // Max 255 characters
'username' => 'alpha'              // Only letters
'code' => 'alpha_num'              // Letters and numbers
'slug' => 'alpha_dash'             // Letters, numbers, dash, underscore
'email' => 'email'                 // Valid email
'url' => 'url'                     // Valid URL
'pattern' => 'regex:/^[A-Z]+$/'    // Regex pattern
```

### Comparison Rules

```php
'password_confirm' => 'confirmed'  // Matches password field
'email' => 'unique:users'          // Unique in table
'email' => 'exists:users'          // Exists in table
'age' => 'same:min_age'            // Same as field
'age' => 'different:other_age'     // Different from field
```

#### In Detail: The `unique` Rule

The `unique` rule checks if a value is unique in the database. This is critical for preventing duplicate emails, usernames, and other unique fields.

**Syntax:**
```php
// Basic usage
'email' => 'unique:users'              // Check in users table by field name
'email' => 'unique:users,email'        // Explicit column specification

// When updating records (ignore current record)
'email' => 'unique:users,email,5'      // Ignore record with id=5
'email' => 'unique:users,email,5,id'   // Explicit exclusion column name
```

**Usage Examples:**

```php
// Register new user
public function register(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email|unique:users,email',
        'username' => 'required|min:3|unique:users,username'
    ]);
    
    User::create($validated);
}

// Update profile (exclude current user)
public function update(Request $request, int $userId)
{
    $validated = $request->validate([
        'email' => "required|email|unique:users,email,{$userId}",
        'username' => "required|unique:users,username,{$userId}"
    ]);
    
    User::find($userId)->update($validated);
}

// With custom ID column
public function updateByUuid(Request $request, string $uuid)
{
    $validated = $request->validate([
        'email' => "required|email|unique:users,email,{$uuid},uuid"
    ]);
    
    User::where('uuid', $uuid)->update($validated);
}
```

**Custom Error Messages:**

```php
$request->validate([
    'email' => 'required|email|unique:users,email',
], [
    'email.unique' => 'This email is already registered in the system'
]);
```

**Implementation Features:**
- ✅ Uses prepared SQL statements (SQL injection protection)
- ✅ Graceful degradation — skips check on database errors
- ✅ Logs errors via `logger()` if available
- ✅ Supports record exclusion during updates

### Selection Rules

```php
'role' => 'in:admin,user,guest'    // One of values
'status' => 'not_in:deleted,banned' // Not one of values
```

### Conditional Rules

```php
// Required if
'company' => 'required_if:account_type,business'

// Required unless
'free_account' => 'required_unless:paid,true'

// Required with
'phone' => 'required_with:address'

// Required without
'email' => 'required_without:phone'
```

## Custom Error Messages

```php
$request->validate([
    'name' => 'required|string',
    'email' => 'required|email|unique:users',
], [
    'name.required' => 'Please enter your name',
    'email.required' => 'Email is required',
    'email.unique' => 'Email already registered',
]);
```

## Display Errors in Views

```html
<!-- Check if field has error -->
<?php if ($errors->has('email')): ?>
    <span class="error"><?php echo $errors->first('email'); ?></span>
<?php endif; ?>

<!-- Show all errors for field -->
<?php if ($errors->has('password')): ?>
    <ul>
        <?php foreach ($errors->get('password') as $error): ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- Repopulate form field -->
<input 
    type="text" 
    name="name"
    value="<?php echo old('name'); ?>"
/>
```

## Array Validation

```php
$validated = $request->validate([
    'users.*.name' => 'required|string',
    'users.*.email' => 'required|email',
]);
```

## File Rules

File fields are validated with dedicated rules:

```php
$validated = $request->validate([
    'avatar' => 'required|file|mimes:jpg,png|max:2048',
]);

$file = $request->file('avatar');
// Public upload (web-accessible):
$path = $file->moveToPublicUploads('avatar_' . time() . '.jpg');
// Or private storage:
// $path = $file->moveToStorageUploads('avatar_' . time() . '.jpg');
```

- `file` — value must be a valid uploaded file (`UploadedFile`).
- `mimes:...` — allowed extensions (checked against the original filename).
- `max:N` — maximum file size in kilobytes; `max:2048` ≈ 2 MB.
- You can additionally check the real MIME type via `$file->getMimeType()`.

See the Security guide for storage path recommendations: use `public/uploads` for public files and `storage/uploads` for private ones.

## Complete Form Example

```php
// In controller
public function create()
{
    return view('users/create');
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);

    User::create($validated);
    
    return redirect('/users');
}
```

```html
<!-- In view -->
<form method="POST" action="/users">
    <?php echo csrf_field(); ?>
    
    <div>
        <label for="name">Name</label>
        <input 
            type="text"
            id="name"
            name="name"
            value="<?php echo old('name'); ?>"
            required
        />
        <?php if ($errors->has('name')): ?>
            <span class="error"><?php echo $errors->first('name'); ?></span>
        <?php endif; ?>
    </div>
    
    <div>
        <label for="email">Email</label>
        <input 
            type="email"
            id="email"
            name="email"
            value="<?php echo old('email'); ?>"
            required
        />
        <?php if ($errors->has('email')): ?>
            <span class="error"><?php echo $errors->first('email'); ?></span>
        <?php endif; ?>
    </div>
    
    <div>
        <label for="password">Password</label>
        <input 
            type="password"
            id="password"
            name="password"
            required
        />
        <?php if ($errors->has('password')): ?>
            <span class="error"><?php echo $errors->first('password'); ?></span>
        <?php endif; ?>
    </div>
    
    <div>
        <label for="password_confirmation">Confirm Password</label>
        <input 
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            required
        />
    </div>
    
    <button type="submit">Create</button>
</form>
```

---

[← Back to Docs](./README.md)
