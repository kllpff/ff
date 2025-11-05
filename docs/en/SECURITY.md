# Security Guide

Build secure applications with FF Framework.

## Password Hashing

Always hash passwords:

```php
use FF\Framework\Security\Hash;

// Hash
$hashed = Hash::make('password123');

// Verify
if (Hash::check('password123', $hashed)) {
    // Password is correct
}

// Create user
User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => Hash::make($request->input('password')),
]);
```

## Data Encryption

Encrypt sensitive data:

```php
// Encrypt
$encrypted = encrypt('credit-card-number');

// Decrypt
$decrypted = decrypt($encrypted);

// Store encrypted
User::create([
    'ssn' => encrypt('123-45-6789'),
]);
```

## CSRF Protection

Protect forms from attacks:

```html
<form method="POST" action="/users">
    {{ csrf_field() }}
    <!-- form fields -->
</form>
```

Or manually:

```html
<form method="POST">
    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"/>
    <!-- form fields -->
</form>
```

## Input Validation

Always validate user input:

```php
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email',
]);

// Now safe to use
User::create($validated);
```

## Output Escaping

Always escape output:

```html
<!-- Using helper -->
<p><?php echo h($user->name); ?></p>

<!-- Or htmlspecialchars -->
<p><?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?></p>
```

## SQL Injection Prevention

Use QueryBuilder (safe):

```php
// ✅ Safe - uses prepared statements
$user = User::where('email', $email)->first();

// ❌ Unsafe - string concatenation
$user = DB::query("SELECT * FROM users WHERE email = '" . $email . "'");
```

## Rate Limiting

Prevent brute force attacks:

```php
use FF\Framework\Security\RateLimiter;

$limiter = new RateLimiter();

public function login(Request $request)
{
    $identifier = "login:" . $request->ip();
    
    if ($limiter->tooManyAttempts($identifier, 5)) {
        return error('Too many login attempts');
    }
    
    $limiter->recordAttempt($identifier, 15); // 15 minute window
    
    // Authenticate...
}
```

## Authentication

Implement authentication:

```php
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    
    $user = User::where('email', $credentials['email'])->first();
    
    if ($user && Hash::check($credentials['password'], $user->password)) {
        session()->put('user_id', $user->id);
        return redirect('/dashboard');
    }
    
    return redirect('/login')->with('error', 'Invalid credentials');
}

public function logout()
{
    session()->forget('user_id');
    session()->regenerate();
    return redirect('/');
}
```

Check authentication:

```php
<?php if (session()->has('user_id')): ?>
    <!-- User is logged in -->
<?php endif; ?>
```

## Secure Headers

```php
return response($content)
    ->header('X-Content-Type-Options', 'nosniff')
    ->header('X-Frame-Options', 'DENY')
    ->header('X-XSS-Protection', '1; mode=block');
```

## File Upload Security

```php
public function upload(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:jpg,png|max:2048',
    ]);
    
    $file = $request->file('file');
    
    // Check MIME type
    $allowed = ['image/jpeg', 'image/png'];
    if (!in_array($file->getMimeType(), $allowed)) {
        return error('Invalid file type');
    }
    
    // Save to storage (outside public)
    $path = $file->move(storage_path('uploads'));
    
    return $path;
}
```

## Environment Variables

Keep sensitive data in `.env`:

```env
APP_KEY=your_encryption_key
DB_PASSWORD=your_password
API_SECRET=your_secret
```

Access in code:

```php
$secret = env('API_SECRET');
```

## Security Checklist

Before deploying:

- ✅ `APP_DEBUG=false` in .env
- ✅ Strong `APP_KEY`
- ✅ HTTPS enabled
- ✅ All input validated
- ✅ All output escaped
- ✅ Passwords hashed with BCrypt
- ✅ CSRF tokens on forms
- ✅ Secure cookie settings
- ✅ Rate limiting enabled
- ✅ Dependencies updated

---

[← Back to Docs](./README.md)
