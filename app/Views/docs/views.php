<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Views</h1>
            <p class="text-lg text-secondary mb-8">PHP template system with layouts</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Creating Views</h2>
                <p class="text-secondary mb-3">Views are stored in <code class="code-inline">app/Views/</code></p>
                <pre class="code-block">
&lt;div class="container"&gt;
    &lt;h1&gt;&lt;?php echo h($title); ?&gt;&lt;/h1&gt;
    &lt;p&gt;&lt;?php echo h($content); ?&gt;&lt;/p&gt;
&lt;/div&gt;</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Rendering Views</h2>
                <pre class="code-block">public function index(): Response
{
    $content = view('home', [
        'title' => 'Welcome',
        'content' => 'Hello World'
    ]);
    
    return response($content);
}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Layouts</h2>
                <p class="text-secondary mb-3">FF Framework supports flexible layout management:</p>

                <h3 class="text-xl font-bold mb-2 mt-4">Method 1: Pass in Controller Data</h3>
                <pre class="code-block">public function index()
{
    return view('home', [
        '__layout' => 'main',  // Uses app/Views/layouts/main.php
        'title' => 'Home'
    ]);
}</pre>

                <h3 class="text-xl font-bold mb-2 mt-4">Method 2: Use Nested Paths</h3>
                <pre class="code-block">public function adminDashboard()
{
    return view('admin/dashboard', [
        '__layout' => 'admin/layouts/app',  // Uses app/Views/admin/layouts/app.php
        'stats' => $stats
    ]);
}</pre>

                <h3 class="text-xl font-bold mb-2 mt-4">Method 3: Disable Layout</h3>
                <pre class="code-block">public function api()
{
    return view('api/data', [
        '__layout' => null,  // No layout
        'data' => $data
    ]);
}</pre>

                <h3 class="text-xl font-bold mb-2 mt-4">Default Layout</h3>
                <p class="text-secondary mb-2">Configure in <code class="code-inline">config/view.php</code>:</p>
                <pre class="code-block">return [
    'default_layout' => 'app',  // Default: app/Views/layouts/app.php
    'layout_paths' => [
        'layouts',
        'admin/layouts',
    ],
];</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Escaping Output</h2>
                <p class="text-secondary mb-3">Always use <code class="code-inline">h()</code> helper to prevent XSS:</p>
                <pre class="code-block">&lt;h1&gt;&lt;?php echo h($user->name); ?&gt;&lt;/h1&gt;
&lt;p&gt;&lt;?php echo h($post->content); ?&gt;&lt;/p&gt;</pre>
            </div>

            <div class="mt-8">
                <a href="/docs/security" class="btn btn-secondary">← Previous: Security</a>
                <a href="/docs/helpers" class="btn btn-primary">Next: Helpers →</a>
            </div>
        </div>
    </div>
</div>
