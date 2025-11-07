<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Logging</h1>
            <p class="text-lg text-secondary mb-8">Application logging with multiple severity levels</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Log Levels</h2>
                <pre class="code-block">logger()->debug('Debug message');
logger()->info('Information message');
logger()->warning('Warning message');
logger()->error('Error message');
logger()->critical('Critical error');</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">With Context</h2>
                <pre class="code-block">logger()->error('User login failed', [
    'email' => $email,
    'ip' => $request->ip(),
    'timestamp' => time()
]);</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Correlation ID</h2>
                <p class="text-secondary mb-3">Each request gets an ID for tracing across logs and services.</p>
                <ul class="space-y-1 mb-3">
                    <li>• Incoming <code class="code-inline">X-Request-ID</code> is respected if valid</li>
                    <li>• ID available via <code class="code-inline">request_id()</code> helper</li>
                    <li>• Response includes <code class="code-inline">X-Request-ID</code> header</li>
                </ul>
                <pre class="code-block">// In middleware pipeline (enabled globally)
// logger context example
logger()->info('Order created', [
    'order_id' => $order->id,
    'request_id' => request_id(),
    'method' => $_SERVER['REQUEST_METHOD'] ?? null,
    'uri' => $_SERVER['REQUEST_URI'] ?? null,
]);

// Client can send X-Request-ID; otherwise framework generates one
// Response will always include X-Request-ID</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Log File Location</h2>
                <p class="text-secondary">Logs are stored in <code class="code-inline">storage/logs/app.log</code></p>
            </div>

            <div class="mt-8">
                <a href="/docs/caching" class="btn btn-secondary">← Previous: Caching</a>
                <a href="/docs/events" class="btn btn-primary">Next: Events →</a>
            </div>
        </div>
    </div>
</div>
