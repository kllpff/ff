# Security Guide

Build secure applications with FF Framework.

## Password Hashing

Always hash passwords:

```php
use FF\Security\Hash;

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
use FF\Security\Encrypt;

// Using the service from container
$encryptor = app('encrypt');
$encrypted = $encryptor->encrypt('credit-card-number');
$decrypted = $encryptor->decrypt($encrypted);

// Or using static methods
$encrypted = Encrypt::hash('credit-card-number');
$decrypted = Encrypt::reveal($encrypted);

// Store encrypted
User::create([
    'ssn' => $encryptor->encrypt('123-45-6789'),
]);
```

## CSRF Protection

Protect forms from attacks:

```php
<form method="POST" action="/users">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
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

## Sanitizing HTML Content

When rendering user-generated HTML (e.g., blog post body or comments), sanitize the content and allow only whitelisted tags/attributes. Escaping alone is not sufficient for rich text; combine sanitization with explicit rendering.

```php
// Example: sanitize HTML using a library or internal sanitizer
$raw = $request->input('content');
$clean = Sanitizer::cleanHtml($raw); // or use HTML Purifier or similar

// Render trusted HTML explicitly
echo raw_html($clean);
```

Guidelines:

- Use `raw_html()` only for trusted, sanitized HTML.
- Never echo raw user input directly.
- Prefer `h()` for titles, plain text, and attributes.
- Maintain a whitelist of allowed tags (e.g., `p`, `strong`, `em`, `ul`, `li`, `a`) and safe attributes (`href`, `title`) with proper validation.


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
use FF\Security\RateLimiter;

$limiter = app('rateLimiter');

public function login(Request $request)
{
    $identifier = "login:" . $request->ip();

    if ($limiter->isLimited($identifier, 5, 15)) {
        return response()->json(['error' => 'Too many login attempts'], 429);
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
use FF\Http\Request;

public function upload(Request $request)
{
    // Validation: required file, only jpg|png, size up to 2 MB
    $request->validate([
        'file' => 'required|file|mimes:jpg,png|max:2048',
    ]);

    $file = $request->file('file');

    // Additional MIME check using file content
    $allowed = ['image/jpeg', 'image/png'];
    if (!in_array($file->getMimeType(), $allowed, true)) {
        return error('Invalid file type');
    }

    // Safe move: ONLY public/uploads or storage/uploads are allowed
    // Public avatars/images:
    $path = $file->moveToPublicUploads('avatar_' . time() . '.jpg');

    // Private documents (outside public directory):
    // $path = $file->moveToStorageUploads('doc_' . time() . '.pdf');

    return $path;
}
```

Notes:
- The `max` rule for files is in kilobytes: `max:2048` ≈ 2 MB.
- `UploadedFile::moveToPublicUploads()` and `moveToStorageUploads()` enforce path checks and will block saving outside `public/uploads` or `storage/uploads`.
- Use `storage/uploads` for sensitive data; use `public/uploads` for publicly accessible images.

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
