<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Sessions</h1>
            <p class="text-lg text-secondary mb-8">Manage user sessions and flash messages</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Storing Data</h2>
                <pre class="code-block">session()->put('key', 'value');
session()->put('user_preferences', [
    'theme' => 'dark',
    'language' => 'en'
]);</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Retrieving Data</h2>
                <pre class="code-block">$value = session()->get('key');
$value = session()->get('key', 'default');

// Check if exists
if (session()->has('key')) {
    // ...
}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Flash Messages</h2>
                <pre class="code-block">// Store flash message (available for next request only)
session()->flash('success', 'Item saved successfully!');
session()->flash('error', 'Something went wrong!');

// In view
$message = session()->getFlash('success');
if ($message) {
    echo $message;
}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Removing Data</h2>
                <pre class="code-block">session()->forget('key');
session()->flush(); // Remove all data</pre>
            </div>

            <div class="mt-8">
                <a href="/docs/authentication" class="btn btn-secondary">← Previous: Authentication</a>
                <a href="/docs/caching" class="btn btn-primary">Next: Caching →</a>
            </div>
        </div>
    </div>
</div>
