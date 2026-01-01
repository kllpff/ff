<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Authentication</h1>
            <p class="text-lg text-secondary mb-8">Secure user authentication system</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Registration</h2>
                <pre class="code-block">use FF\Security\Auth;
use FF\Security\Hash;

$user = User::create([
    'name' => $request->input('name'),
    'email' => $request->input('email'),
    'password' => Hash::make($request->input('password'))
]);

Auth::login($user);</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Login</h2>
                <pre class="code-block">$credentials = [
    'email' => $request->input('email'),
    'password' => $request->input('password')
];

if (Auth::attempt($credentials)) {
    return redirect('/dashboard');
}

return redirect('/login')->with('error', 'Invalid credentials');</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Logout</h2>
                <pre class="code-block">Auth::logout();
return redirect('/login');</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Check Authentication</h2>
                <pre class="code-block">if (Auth::check()) {
    // User is logged in
}

if (Auth::guest()) {
    // User is not logged in
}

$user = Auth::user(); // Get current user</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Middleware Protection</h2>
                <pre class="code-block">$router->get('/dashboard', 'DashboardController@index')
    ->middleware('auth');</pre>
            </div>

            <div class="mt-8">
                <a href="/docs/validation" class="btn btn-secondary">← Previous: Validation</a>
                <a href="/docs/sessions" class="btn btn-primary">Next: Sessions →</a>
            </div>
        </div>
    </div>
</div>
