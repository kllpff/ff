# API Reference

Complete API documentation for FF Framework.

## Core Functions

### Application

```php
app()                              // Get application instance
app(\YourService::class)          // Get from container
app()->make(\Class::class)         // Create instance
app()->bind('key', $concrete)      // Register binding
app()->singleton('key', $concrete) // Register singleton
```

### Request & Response

```php
request()                           // Get current request
request()->input('name')            // Get input
request()->all()                    // All input
request()->validate([...])          // Validate input
request()->url()                    // Current absolute URL

response($content, 200)             // Create response
response()->json($data)             // JSON response
redirect('/url')                    // Redirect
route('name', ['id' => 1])         // Route URL
url('/path')                        // Absolute URL (based on APP_URL)
```

Best practices for APIs:

- Prefer `response()->json($data)` to ensure correct headers and encoding.
- Avoid manual `echo` for JSON or setting headers yourself.
- Returning arrays/objects from API controllers may be converted to JSON automatically, but explicit `response()->json(...)` keeps responses consistent and controllable.

```php
// Example in controller
public function index()
{
    $users = User::all();
    return response()->json([
        'ok' => true,
        'data' => $users,
    ]);
}
```

### Views

```php
view('name')                        // Render view
view('name', ['var' => $value])    // With variables
h($string)                          // HTML escape
old('field')                        // Old form value
```

### Sessions

```php
session()                           // Get session
session('key')                      // Get session value
session()->put('key', $value)       // Store
session()->flash('key', $message)   // Flash message
```

### Database

```php
// Models
User::all()                         // Get all
User::find(1)                       // Get by ID
User::where('status', 'active')    // Query
User::create($data)                 // Create
$user->update($data)                // Update
$user->delete()                     // Delete

// QueryBuilder
User::where('...')
    ->orderBy('...')
    ->limit(10)
    ->get()
```

### Security

```php
Hash::make('password')              // Hash password
Hash::check('password', $hash)      // Verify hash
encrypt('data')                     // Encrypt data
decrypt($encrypted)                 // Decrypt
csrf_token()                        // Get CSRF token
csrf_field()                        // CSRF form field
```

### Validation

```php
$request->validate([...])           // Validate
$errors->has('field')               // Check error
$errors->first('field')             // Get first error
$errors->all()                      // All errors
```

### Logging

```php
logger()                            // Get logger
logger()->info('message')           // Log
logger()->error('error')            // Log error
logger()->debug('debug')            // Log debug
```

### Caching

```php
cache()                             // Get cache
cache()->put('key', $value, 3600)  // Store
cache()->get('key')                 // Get
cache()->has('key')                 // Check
cache()->forget('key')              // Remove
cache()->flush()                    // Clear all
```

### Utilities

```php
now()                               // Current DateTime
dump($var)                          // Dump variable
dd($var)                            // Dump and die
env('KEY')                          // Environment variable
config('app.name')                  // Configuration
abort(404)                          // Abort with status
```

## Class Reference

### Request

```php
$request->input($key, $default)
$request->all()
$request->only(['key1', 'key2'])
$request->except(['key1'])
$request->file('name')
$request->get('name')
$request->post('name')
$request->header('name')
$request->method()
$request->url()
$request->fullUrl()
$request->path()
$request->ip()
$request->isMethod('post')
$request->isPost()
$request->isGet()
$request->isAjax()
$request->validate([...])
```

### Response

```php
response($content, 200)
response()->json($data)
response()->download($path)
response()->header($key, $value)
response()->withHeaders([...])
response()->setStatusCode(200)
```

### Model

```php
Model::all()
Model::find($id)
Model::findOrFail($id)
Model::where($column, $value)
Model::create($data)
$model->update($data)
$model->save()
$model->delete()
$model->toArray()
$model->toJson()
```

### Session

```php
session()->put($key, $value)
session()->get($key, $default)
session()->has($key)
session()->forget($key)
session()->flush()
session()->flash($key, $value)
session()->regenerate()
```

---

[← Back to Docs](./README.md)
