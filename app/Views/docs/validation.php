<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Validation</h1>
            <p class="text-lg text-secondary mb-8">Powerful form validation with custom rules</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Basic Validation</h2>
                <pre class="code-block">use FF\Framework\Validation\Validator;

$validator = new Validator($request->input(), [
    'email' => ['required', 'email'],
    'password' => ['required', 'min:8'],
    'name' => ['required', 'max:100']
]);

if (!$validator->passes()) {
    $errors = $validator->errors();
    // Handle errors
}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Available Rules</h2>
                <ul class="space-y-2">
                    <li>• <code class="code-inline">required</code> - Field must be present</li>
                    <li>• <code class="code-inline">email</code> - Valid email address</li>
                    <li>• <code class="code-inline">min:n</code> - Minimum length</li>
                    <li>• <code class="code-inline">max:n</code> - Maximum length</li>
                    <li>• <code class="code-inline">numeric</code> - Must be numeric</li>
                    <li>• <code class="code-inline">alpha</code> - Letters only</li>
                    <li>• <code class="code-inline">alphanumeric</code> - Letters and numbers</li>
                    <li>• <code class="code-inline">url</code> - Valid URL</li>
                    <li>• <code class="code-inline">unique:table,column</code> - Unique in database</li>
                </ul>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Custom Messages</h2>
                <pre class="code-block">$validator = new Validator($data, $rules, [
    'email.required' => 'Email is required!',
    'email.email' => 'Please provide valid email',
    'password.min' => 'Password must be at least 8 characters'
]);</pre>
            </div>

            <div class="mt-8">
                <a href="/docs/models" class="btn btn-secondary">← Previous: Models</a>
                <a href="/docs/authentication" class="btn btn-primary">Next: Authentication →</a>
            </div>
        </div>
    </div>
</div>
