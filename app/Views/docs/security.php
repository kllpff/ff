<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Security</h1>
            <p class="text-lg text-secondary mb-8">Built-in security features to protect your application</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Password Hashing</h2>
                <pre class="code-block">use FF\Framework\Security\Hash;

$hashed = Hash::make('password');

if (Hash::check('password', $hashed)) {
    // Password matches
}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">CSRF Protection</h2>
                <p class="text-secondary mb-3">Add CSRF token to forms:</p>
                <pre class="code-block">&lt;form method="POST" action="/submit"&gt;
    &lt;input type="hidden" name="csrf_token" value="&lt;?php echo csrf_token(); ?&gt;"&gt;
    &lt;!-- form fields --&gt;
&lt;/form&gt;</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Data Encryption</h2>
                <pre class="code-block">use FF\Framework\Security\Encrypt;

$encrypted = Encrypt::encrypt('sensitive data');
$decrypted = Encrypt::decrypt($encrypted);</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Rate Limiting</h2>
                <pre class="code-block">use FF\Framework\Http\RateLimiter;

$limiter = new RateLimiter();
if ($limiter->tooManyAttempts('login:' . $ip, 5, 60)) {
    // Too many attempts
}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">XSS Prevention</h2>
                <p class="text-secondary mb-3">Always escape output in views:</p>
                <pre class="code-block">&lt;?php echo h($user->name); ?&gt;</pre>
            </div>

            <div class="mt-8">
                <a href="/docs/events" class="btn btn-secondary">← Previous: Events</a>
                <a href="/docs/views" class="btn btn-primary">Next: Views →</a>
            </div>
        </div>
    </div>
</div>
