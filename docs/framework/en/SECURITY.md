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

// Using the service from container (recommended approach)
$encryptor = app('encrypt');
$encrypted = $encryptor->encrypt('credit-card-number');
$decrypted = $encryptor->decrypt($encrypted);

// Store encrypted
User::create([
    'ssn' => $encryptor->encrypt('123-45-6789'),
]);

// Alternative: using static methods (without dependency injection)
$encrypted = Encrypt::hash('credit-card-number');
$decrypted = Encrypt::reveal($encrypted);
```

## CSRF Protection

Protect forms from attacks:

### Using helper functions (recommended approach)

```php
<form method="POST" action="/users">
    <?php echo csrf_field(); ?>
    <!-- form fields -->
</form>
```

The `csrf_field()` function will generate a field with the correct token name and value.

### Manual field creation

```html
<form method="POST">
    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"/>
    <!-- form fields -->
</form>
```

### Using in AJAX requests

```javascript
fetch('/api/users', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
    },
    body: JSON.stringify(data)
});
```

### CSRF checking in middleware

The framework automatically checks CSRF tokens for POST, PUT, PATCH and DELETE requests via `CsrfMiddleware`. To exclude routes, use:

```php
// In routes.php
$router->post('/api/webhook', 'WebhookController@handle')
    ->middleware(['no_csrf']); // Exclude from CSRF protection
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
use FF\Http\Request;
use FF\Http\Response;

class AuthController
{
    protected RateLimiter $rateLimiter;
    
    public function __construct(RateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }
    
    public function login(Request $request): Response
    {
        // Limit: max 5 login attempts per 15 minutes per IP
        $key = 'login:' . $request->ip();
        
        if ($this->rateLimiter->isLimited($key, 5, 15)) {
            return response()->json(['error' => 'Too many login attempts'], 429);
        }
        
        $this->rateLimiter->recordAttempt($key, 15); // 15 minute window
        
        // Authenticate...
    }
}
```

Using RateLimiter via dependency container:

```php
// Getting instance via dependency injection
$limiter = app('rateLimiter');

// Check limit by IP
if ($limiter->limitByIp($request->ip(), 10, 60)) {
    return response('Too many requests', 429);
}

// Check limit by user ID
if ($limiter->limitByUser($userId, 100, 1)) {
    return response('User rate limit exceeded', 429);
}

// Check limit by endpoint
if ($limiter->limitByEndpoint('api/users', 50, 1)) {
    return response('API rate limit exceeded', 429);
}
```

## Security Headers

The framework automatically adds security headers via `SecurityHeadersMiddleware`:

```php
// Automatically added headers:
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: no-referrer-when-downgrade
Permissions-Policy: geolocation=(), microphone=(), camera=()
X-Permitted-Cross-Domain-Policies: none
X-XSS-Protection: 0
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'
```

For HTTPS connections, HSTS header is automatically added:
```
Strict-Transport-Security: max-age=63072000; includeSubDomains; preload
```

## Authentication

Use the framework's authentication system:

```php
use FF\Security\Auth;

// Get authentication instance
$auth = app('auth');

// Check if user is authenticated
if ($auth->check()) {
    // User is logged in
    $user = $auth->user();
    $userId = $auth->id();
}

// Log out
$auth->logout();

// In middleware to protect routes
use FF\Http\Middleware\AuthMiddleware;

$router->get('/dashboard', 'DashboardController@index')
    ->middleware([AuthMiddleware::class]);
```

## CORS (Cross-Origin Resource Sharing)

Configure CORS through `CorsMiddleware`:

```php
use FF\Http\Middleware\CorsMiddleware;

// CORS configuration
$config = [
    'allowed_origins' => ['https://example.com', 'https://app.example.com'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-CSRF-TOKEN'],
    'exposed_headers' => ['X-Total-Count'],
    'max_age' => 3600,
    'credentials' => true,
];

// Apply to routes
$router->group(['middleware' => [CorsMiddleware::class]], function($router) {
    $router->get('/api/users', 'ApiController@users');
    $router->post('/api/users', 'ApiController@createUser');
});
```

## Session Security

The framework ensures session security:

```php
// Session ID regeneration on login/logout (automatic)
$auth->login($user);
$auth->logout();

// Session security check
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Session security settings in php.ini or configuration
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

## Security Middleware

Apply security middleware to your routes:

```php
use FF\Http\Middleware\SecurityHeadersMiddleware;
use FF\Http\Middleware\CsrfMiddleware;
use FF\Http\Middleware\RateLimitMiddleware;

// Global middleware (applied to all requests)
$kernel->pushMiddleware(SecurityHeadersMiddleware::class);

// Middleware groups
$router->group(['middleware' => [
    CsrfMiddleware::class,
    RateLimitMiddleware::class . ':60,1' // 60 requests per minute
]], function($router) {
    $router->post('/api/users', 'UserController@store');
    $router->put('/api/users/{id}', 'UserController@update');
});
```

## Security Best Practices

### 1. Keep Dependencies Updated
```bash
composer update
```

### 2. Use HTTPS
```php
// Force HTTPS redirect
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}
```

### 3. File Validation
```php
// Check file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['avatar']['type'], $allowedTypes)) {
    throw new \Exception('Invalid file type');
}

// Check file extension
$extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array(strtolower($extension), $allowedExtensions)) {
    throw new \Exception('Invalid file extension');
}
```

### 4. Timing Attack Protection
```php
// Use hashing for sensitive data comparison
if (hash_equals($expectedToken, $providedToken)) {
    // Tokens match
}
```

### 5. Security Logging
```php
// Log important security events
logger()->warning('Failed login attempt', [
    'ip' => $request->ip(),
    'email' => $request->input('email'),
    'user_agent' => $request->header('User-Agent'),
]);
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
