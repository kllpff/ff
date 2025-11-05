<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Views</h1>
            <p class="text-lg text-secondary mb-8">PHP template system with layouts</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Creating Views</h2>
                <p class="text-secondary mb-3">Views are stored in <code class="code-inline">app/Views/</code></p>
                <pre class="code-block">&lt;?php $__layout = 'main'; ?&gt;

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
                <p class="text-secondary mb-3">Set layout at the top of your view:</p>
                <pre class="code-block">&lt;?php $__layout = 'main'; ?&gt;  // Uses app/Views/layouts/main.php
&lt;?php $__layout = 'admin'; ?&gt; // Uses app/Views/layouts/admin.php

// No layout
// Just omit $__layout variable</pre>
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
