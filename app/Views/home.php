<!-- Hero Section -->
<div class="py-16 bg-zinc-50">
    <div class="container text-center">
        <h1 class="text-5xl font-bold mb-4">FF Framework</h1>
        <p class="text-2xl text-secondary mb-2">Modern PHP MVC Framework</p>
        <p class="text-lg text-muted mb-8">Fast, Secure & Flexible</p>
        <div class="flex gap-3 justify-center flex-wrap">
            <a href="/docs" class="btn btn-primary btn-lg">📖 Documentation</a>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="section">
    <div class="container">
        <h2 class="text-3xl font-bold text-center mb-12">Framework Features</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">💾 Caching</h3>
                <p class="text-secondary">Multiple drivers (file, array, Redis support). Cache decorators and invalidation strategies.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">📋 Logging</h3>
                <p class="text-secondary">PSR-3 compatible logger with multiple levels and handlers. Full stack traces and context.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">✅ Validation</h3>
                <p class="text-secondary">Comprehensive rules: required, email, min/max, regex, custom validators, conditional validation.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">🚦 Rate Limiting</h3>
                <p class="text-secondary">IP-based throttling, attempt tracking, configurable windows. Protect against abuse.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">🔐 Security</h3>
                <p class="text-secondary">BCrypt hashing, AES-256 encryption, CSRF protection, input sanitization, XSS prevention.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">📡 Events & Listeners</h3>
                <p class="text-secondary">Event dispatcher system for decoupled architecture. Multiple listeners per event.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">🎫 Sessions</h3>
                <p class="text-secondary">Secure session management with flash messages, auto-expiration, CSRF token handling.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">📧 Email</h3>
                <p class="text-secondary">SMTP mail service with HTML templates, attachments, CC/BCC, auto-reply support.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">🗄️ Database ORM</h3>
                <p class="text-secondary">Query builder, migrations, seeders, relationships. Elegant database interactions.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">🛣️ Advanced Routing</h3>
                <p class="text-secondary">Named routes, route parameters, middleware support, route groups with prefixes.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">🔧 Dependency Injection</h3>
                <p class="text-secondary">Service container with auto-wiring, singleton services, closure bindings, lazy loading.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">🛡️ Middleware Pipeline</h3>
                <p class="text-secondary">Request/response middleware stack. Auth, CORS, compression, HTTPS redirect built-in.</p>
            </div>
        </div>
    </div>
</div>

<!-- Why FF Framework Section -->
<div class="section bg-zinc-50">
    <div class="container">
        <h2 class="text-3xl font-bold text-center mb-12">Why FF Framework?</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">⚡ High Performance</h3>
                <p class="text-secondary">Built for speed with minimal overhead. Clean architecture and efficient routing.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">🔒 Secure by Default</h3>
                <p class="text-secondary">CSRF protection, password hashing, encryption, rate limiting, and XSS prevention built-in.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">🎯 Simple & Elegant</h3>
                <p class="text-secondary">Clean, intuitive API that makes development a joy. Easy to learn, powerful to use.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">🏗️ Clean Architecture</h3>
                <p class="text-secondary">MVC pattern, dependency injection, service layer - all the tools for maintainable code.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">📦 Zero Dependencies</h3>
                <p class="text-secondary">Lightweight core with optional packages. Use only what you need.</p>
            </div>
            <div class="card card-hover">
                <h3 class="text-xl font-semibold mb-3">📚 Full Documentation</h3>
                <p class="text-secondary">Comprehensive guides, examples, and best practices to get you up to speed quickly.</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Start Section -->
<div class="section">
    <div class="container">
        <h2 class="text-3xl font-bold text-center mb-12">Quick Start</h2>
        <div class="max-w-4xl mx-auto">
            <div class="card mb-6">
                <h3 class="text-xl font-semibold mb-3">Define a Route</h3>
                <pre class="code-block">// config/routes.php
$router->get('/hello/{name}', 'HomeController@hello');</pre>
            </div>
            <div class="card mb-6">
                <h3 class="text-xl font-semibold mb-3">Create a Controller</h3>
                <pre class="code-block">// app/Controllers/HomeController.php
class HomeController
{
    public function hello(Request $request, string $name): Response
    {
        $content = view('hello', ['name' => $name]);
        return response($content);
    }
}</pre>
            </div>
            <div class="card mb-8">
                <h3 class="text-xl font-semibold mb-3">Create a View</h3>
                <pre class="code-block">// app/Views/hello.php

&lt;h1&gt;Hello, &lt;?php echo h($name); ?&gt;!&lt;/h1&gt;</pre>
            </div>
            <div class="text-center">
                <a href="/docs" class="btn btn-primary btn-lg">📖 Read Full Documentation</a>
            </div>
        </div>
    </div>
</div>
