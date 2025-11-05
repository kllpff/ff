<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Helpers</h1>
            <p class="text-lg text-secondary mb-8">Useful helper functions available globally</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Application Helpers</h2>
                <ul class="space-y-2">
                    <li>• <code class="code-inline">app($service)</code> - Resolve service from container</li>
                    <li>• <code class="code-inline">config($key, $default)</code> - Get configuration value</li>
                    <li>• <code class="code-inline">env($key, $default)</code> - Get environment variable</li>
                    <li>• <code class="code-inline">base_path($path)</code> - Get absolute path</li>
                    <li>• <code class="code-inline">storage_path($path)</code> - Get storage path</li>
                </ul>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">View Helpers</h2>
                <ul class="space-y-2">
                    <li>• <code class="code-inline">view($name, $data)</code> - Render view</li>
                    <li>• <code class="code-inline">response($content, $status)</code> - Create response</li>
                    <li>• <code class="code-inline">redirect($url)</code> - Create redirect</li>
                    <li>• <code class="code-inline">h($string)</code> - Escape HTML (XSS prevention)</li>
                </ul>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Database Helpers</h2>
                <ul class="space-y-2">
                    <li>• <code class="code-inline">db()</code> - Get database connection</li>
                </ul>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Session Helpers</h2>
                <ul class="space-y-2">
                    <li>• <code class="code-inline">session($key, $default)</code> - Get/set session data</li>
                    <li>• <code class="code-inline">csrf_token()</code> - Get CSRF token</li>
                </ul>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Cache Helpers</h2>
                <ul class="space-y-2">
                    <li>• <code class="code-inline">cache()</code> - Get cache instance</li>
                </ul>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Other Helpers</h2>
                <ul class="space-y-2">
                    <li>• <code class="code-inline">logger()</code> - Get logger instance</li>
                    <li>• <code class="code-inline">dd($var)</code> - Dump and die</li>
                    <li>• <code class="code-inline">dump($var)</code> - Dump variable</li>
                </ul>
            </div>

            <div class="mt-8">
                <a href="/docs/views" class="btn btn-secondary">← Previous: Views</a>
                <a href="/docs/deployment" class="btn btn-primary">Next: Deployment →</a>
            </div>
        </div>
    </div>
</div>
