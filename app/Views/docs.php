<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Framework Features - FF Framework</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; color: #333; line-height: 1.6; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; }
        header h1 { font-size: 2.5em; margin-bottom: 10px; }
        nav { display: flex; gap: 20px; margin-top: 20px; }
        nav a { color: white; text-decoration: none; padding: 8px 15px; background: rgba(255,255,255,0.2); border-radius: 4px; }
        nav a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .feature { background: white; border-radius: 8px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .feature h2 { color: #667eea; margin-bottom: 15px; font-size: 1.8em; }
        .feature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start; }
        .feature-desc { }
        .feature-code { background: #0d1117; color: #d4d4d4; padding: 20px; border-radius: 6px; font-family: 'Monaco', monospace; font-size: 0.9em; overflow-x: auto; }
        .feature-code code { display: block; white-space: pre; }
        .tag { display: inline-block; background: #667eea; color: white; padding: 4px 10px; border-radius: 3px; font-size: 0.85em; margin-right: 8px; margin-bottom: 8px; }
        ul { margin-left: 20px; margin-top: 10px; }
        li { margin-bottom: 8px; }
        a { color: #667eea; text-decoration: none; }
        a:hover { text-decoration: underline; }
        footer { background: #333; color: white; padding: 20px; text-align: center; margin-top: 40px; }
        @media (max-width: 768px) {
            .feature-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <h1>🔧 Framework Features</h1>
        <p>Complete guide to FF Framework capabilities with real examples</p>
        <nav>
            <a href="/">Home</a>
            <a href="/blog">Blog</a>
            <?php if(session('auth_user')): ?>
                <a href="/dashboard">Dashboard</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <!-- Routing -->
        <div class="feature">
            <h2>1. Routing</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Define application routes with flexible parameter handling and naming.</p>
                    <ul>
                        <li>Static routes: <code>/about</code></li>
                        <li>Dynamic routes: <code>/blog/{id}</code></li>
                        <li>Named routes for easy URL generation</li>
                        <li>Route grouping with middleware</li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> This entire site uses routing in <code>config/routes.php</code></p>
                </div>
                <div class="feature-code"><code>$router->get('/blog', 'BlogController@index');
$router->get('/blog/{id}', 'BlogController@show');
$router->post('/blog', 'BlogController@store');

$router->group(['prefix' => 'api'], function($router) {
    $router->get('/posts', 'Api\BlogController@index');
    $router->post('/posts', 'Api\BlogController@store');
});</code></div>
            </div>
        </div>

        <!-- Request Handling -->
        <div class="feature">
            <h2>2. Request Handling</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Easy access to HTTP request data with multiple retrieval methods.</p>
                    <ul>
                        <li>Get input: <code>$request->input('email')</code></li>
                        <li>Get all: <code>$request->all()</code></li>
                        <li>Get with default: <code>$request->input('role', 'user')</code></li>
                        <li>Check existence: <code>$request->has('field')</code></li>
                        <li>Get specific: <code>$request->only(['name', 'email'])</code></li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> <a href="/auth/login">Login form</a> uses request handling</p>
                </div>
                <div class="feature-code"><code>public function login(Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');
    
    $data = $request->all();
    $validator = Validator::make($data, [...]);
}</code></div>
            </div>
        </div>

        <!-- Response Building -->
        <div class="feature">
            <h2>3. Response Building</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Multiple ways to return responses from controllers.</p>
                    <ul>
                        <li>Render views: <code>view('name', $data)</code></li>
                        <li>Return JSON: <code>response()->json($data)</code></li>
                        <li>Redirects: <code>redirect('/path')</code></li>
                        <li>Error responses: <code>error('Not found', 404)</code></li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> API endpoints return JSON, web pages return views</p>
                </div>
                <div class="feature-code"><code>// Render view
return view('blog.post', ['post' => $post]);

// JSON response
return response()->json(['data' => $post]);

// Redirect with message
return redirect('/dashboard');

// Error response
return error('Not found', 404);</code></div>
            </div>
        </div>

        <!-- Validation -->
        <div class="feature">
            <h2>4. Validation</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Validate user input with built-in rules and custom error messages.</p>
                    <ul>
                        <li>Required, email, string, integer</li>
                        <li>Length: min, max</li>
                        <li>Password confirmation</li>
                        <li>Unique in database</li>
                        <li>URL validation</li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> <a href="/auth/register">Registration form</a> validates all inputs</p>
                </div>
                <div class="feature-code"><code>$validator = Validator::make($request->all(), [
    'name' => 'required|string|min:2|max:255',
    'email' => 'required|email',
    'password' => 'required|min:8|confirmed',
]);

if ($validator->fails()) {
    $errors = $validator->errors();
    return redirect('/auth/register');
}</code></div>
            </div>
        </div>

        <!-- Database ORM -->
        <div class="feature">
            <h2>5. Database ORM & QueryBuilder</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Fluent interface for database queries with model relationships.</p>
                    <ul>
                        <li>Find records: <code>User::find(1)</code></li>
                        <li>Query: <code>Post::where('status', 'published')->get()</code></li>
                        <li>Create: <code>User::create([...])</code></li>
                        <li>Update: <code>$post->update([...])</code></li>
                        <li>Delete: <code>$post->delete()</code></li>
                        <li>Relationships: <code>with('category', 'author')</code></li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> Blog system uses ORM for posts, categories, comments</p>
                </div>
                <div class="feature-code"><code>// Eager load relationships
$post = BlogPost::with('category', 'author')
    ->findOrFail($id);

// Query with filters
$posts = BlogPost::where('status', 'published')
    ->where('category_id', 5)
    ->latest('created_at')
    ->paginate(15);

// Create with attributes
$post = BlogPost::create([
    'title' => 'My Post',
    'content' => '...',
]);</code></div>
            </div>
        </div>

        <!-- Authentication -->
        <div class="feature">
            <h2>6. Authentication</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Session-based user authentication with password hashing.</p>
                    <ul>
                        <li>User registration</li>
                        <li>Secure login</li>
                        <li>Session management</li>
                        <li>Logout</li>
                        <li>Password reset flow</li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> Full auth system at <a href="/auth/login">Login</a>, <a href="/auth/register">Register</a>, <a href="/auth/forgot-password">Forgot Password</a></p>
                </div>
                <div class="feature-code"><code>// Hash password
$user = User::create([
    'name' => $request->input('name'),
    'email' => $request->input('email'),
    'password' => Hash::make($request->input('password')),
]);

// Verify password
if (Hash::check($password, $user->password)) {
    // Valid password
    session()->put('auth_user', [...]);
}</code></div>
            </div>
        </div>

        <!-- Caching -->
        <div class="feature">
            <h2>7. Caching</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Cache frequently accessed data for performance optimization.</p>
                    <ul>
                        <li>Store data: <code>$cache->put('key', $value, 3600)</code></li>
                        <li>Retrieve: <code>$cache->get('key', $default)</code></li>
                        <li>Check exists: <code>$cache->has('key')</code></li>
                        <li>Remove: <code>$cache->forget('key')</code></li>
                        <li>Clear all: <code>$cache->flush()</code></li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> Blog categories and home page data are cached</p>
                </div>
                <div class="feature-code"><code>$cache = new Cache();
$key = 'blog_categories_all';

$categories = $cache->get($key);
if (!$categories) {
    $categories = BlogCategory::withCount('posts')->get();
    $cache->put($key, $categories, 3600);
}

return view('blog.index', ['categories' => $categories]);</code></div>
            </div>
        </div>

        <!-- Session Management -->
        <div class="feature">
            <h2>8. Session Management</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Store and manage user data across requests with flash messages.</p>
                    <ul>
                        <li>Store: <code>$session->put('key', $value)</code></li>
                        <li>Get: <code>$session->get('key', $default)</code></li>
                        <li>Check: <code>$session->has('key')</code></li>
                        <li>Flash (one-time): <code>$session->flash('message')</code></li>
                        <li>Clear: <code>$session->forget('key')</code></li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> User auth stored in session, messages after actions</p>
                </div>
                <div class="feature-code"><code>$session = new SessionManager();

// Store user
$session->put('auth_user', [
    'id' => $user->id,
    'name' => $user->name,
]);

// Flash message
$session->flash('success', 'Logged in!');

// In view
$message = session()->getFlash('success');
if ($message) {
    echo '&lt;div class="alert">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '&lt;/div>';
}</code></div>
            </div>
        </div>

        <!-- Logging -->
        <div class="feature">
            <h2>9. Logging</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Log application events with different severity levels.</p>
                    <ul>
                        <li>Info: <code>$logger->info('message')</code></li>
                        <li>Warning: <code>$logger->warning('message')</code></li>
                        <li>Error: <code>$logger->error('message')</code></li>
                        <li>Debug: <code>$logger->debug('message')</code></li>
                        <li>Context data: <code>['user_id' => 1, 'action' => 'login']</code></li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> Blog post views are logged</p>
                </div>
                <div class="feature-code"><code>$logger = new Logger('app');

$logger->info('Post viewed', [
    'post_id' => $postId,
    'category_id' => $categoryId,
    'ip' => $_SERVER['REMOTE_ADDR'],
]);

$logger->error('Database error', [
    'query' => $sql,
    'error' => $e->getMessage(),
]);

// Logs in: storage/logs/app.log</code></div>
            </div>
        </div>

        <!-- Rate Limiting -->
        <div class="feature">
            <h2>10. Rate Limiting</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Protect endpoints from abuse with request throttling.</p>
                    <ul>
                        <li>In controller: <code>if ($limiter->isLimited($ip, 5, 15))</code></li>
                        <li>Via middleware: <code>new RateLimitMiddleware(60, 1)</code></li>
                        <li>Route grouping: Apply to multiple routes at once</li>
                        <li>Check remaining: <code>$limiter->getRemaining($ip, $max)</code></li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> Blog routes have rate limiting (30/min)</p>
                </div>
                <div class="feature-code"><code>// In controller
$limiter = new RateLimiter(new Cache());
$ip = $_SERVER['REMOTE_ADDR'];

if ($limiter->isLimited($ip, 5, 15)) { // 5 per 15 min
    return error('Too many attempts', 429);
}

$limiter->recordAttempt($ip, 15);

// In routes (middleware)
$router->post('/login', 'AuthController@login')
    ->middleware(new RateLimitMiddleware(5, 15));</code></div>
            </div>
        </div>

        <!-- Security -->
        <div class="feature">
            <h2>11. Security</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Built-in security features for protecting your application.</p>
                    <ul>
                        <li>Password hashing: BCrypt</li>
                        <li>Data encryption: AES-256</li>
                        <li>Input sanitization: <code>htmlspecialchars()</code></li>
                        <li>Rate limiting: Brute force protection</li>
                        <li>CSRF protection (optional middleware)</li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> All form inputs are validated and escaped</p>
                </div>
                <div class="feature-code"><code>// Password hashing
$hashed = Hash::make($password);
$matches = Hash::check($password, $hashed);

// Data encryption
$encrypt = new Encrypt();
$encrypted = $encrypt->encrypt('sensitive data');
$decrypted = $encrypt->decrypt($encrypted);

// HTML escaping
$safe = htmlspecialchars($userInput, ENT_QUOTES);
$safe = h($userInput); // Helper</code></div>
            </div>
        </div>

        <!-- Middleware -->
        <div class="feature">
            <h2>12. Middleware</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Request filtering and pipeline processing.</p>
                    <ul>
                        <li>Apply to single route</li>
                        <li>Apply to route groups</li>
                        <li>Built-in middleware included</li>
                        <li>Create custom middleware</li>
                        <li>Before/after request processing</li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> Rate limiting and CORS middleware available</p>
                </div>
                <div class="feature-code"><code>// Single route
$router->get('/dashboard', 'DashboardController@index')
    ->middleware(new AuthMiddleware());

// Route group
$router->group([
    'middleware' => [
        new AuthMiddleware(),
        new RateLimitMiddleware(60, 1)
    ]
], function($router) {
    $router->get('/admin', 'AdminController@index');
    $router->post('/admin/user', 'AdminController@store');
});</code></div>
            </div>
        </div>

        <!-- Views -->
        <div class="feature">
            <h2>13. Views & Templating</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Clean template system with data passing and HTML escaping.</p>
                    <ul>
                        <li>Render: <code>view('name', $data)</code></li>
                        <li>Pass data: <code>$data = ['user' => $user]</code></li>
                        <li>Escape output: <code>h($userInput)</code></li>
                        <li>Include partials: <code>include 'partials/header.php'</code></li>
                        <li>PHP native syntax</li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> All pages on this site are views</p>
                </div>
                <div class="feature-code"><code>// In controller
return view('blog.post', [
    'post' => $post,
    'comments' => $comments,
]);

// In view
&lt;h1>&lt;?php echo h($post->title); ?>&lt;/h1>

&lt;?php foreach($comments as $comment): ?>
    &lt;p>&lt;?php echo h($comment->content); ?>&lt;/p>
&lt;?php endforeach; ?></code></div>
            </div>
        </div>

        <!-- Migrations -->
        <div class="feature">
            <h2>14. Database Migrations</h2>
            <div class="feature-grid">
                <div class="feature-desc">
                    <p>Version control for your database schema.</p>
                    <ul>
                        <li>Create tables: <code>$table->increments('id')</code></li>
                        <li>Column types: string, text, integer, etc.</li>
                        <li>Modifiers: nullable(), unique(), default()</li>
                        <li>Indexes: <code>$table->index('column')</code></li>
                        <li>Run: <code>php artisan migrate</code></li>
                    </ul>
                    <p style="margin-top: 15px;"><strong>See in action:</strong> Database structure in <code>database/migrations/</code></p>
                </div>
                <div class="feature-code"><code>class CreateBlogPostsTable extends Migration {
    public function up() {
        $this->schema()->create('blog_posts', function($table) {
            $table->increments('id');
            $table->string('title');
            $table->longText('content');
            $table->integer('views')->default(0);
            $table->enum('status', ['draft', 'published']);
            $table->timestamps();
            $table->index('status');
        });
    }
    
    public function down() {
        $this->schema()->dropIfExists('blog_posts');
    }
}</code></div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 FF Framework. Learn by doing with real examples.</p>
    </footer>
</body>
</html>
