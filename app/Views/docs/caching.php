<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Caching</h1>
            <p class="text-lg text-secondary mb-8">File-based caching for improved performance</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Storing in Cache</h2>
                <pre class="code-block">cache()->put('key', 'value', 3600); // TTL in seconds</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Retrieving from Cache</h2>
                <pre class="code-block">$value = cache()->get('key');
$value = cache()->get('key', 'default');</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Remember Pattern</h2>
                <pre class="code-block">$users = cache()->remember('users', 3600, function() {
    return User::all();
});

// Will fetch from cache if exists, otherwise execute callback</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Checking & Removing</h2>
                <pre class="code-block">if (cache()->has('key')) {
    // Key exists
}

cache()->forget('key');
cache()->flush(); // Clear all cache</pre>
            </div>

            <div class="mt-8">
                <a href="/docs/sessions" class="btn btn-secondary">← Previous: Sessions</a>
                <a href="/docs/logging" class="btn btn-primary">Next: Logging →</a>
            </div>
        </div>
    </div>
</div>
