<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Controllers</h1>
            <p class="text-lg text-secondary mb-8">Handle HTTP requests with elegant controller classes</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Creating Controllers</h2>
                <p class="text-secondary mb-4">Controllers are stored in <code class="code-inline">app/Controllers/</code></p>
                <pre class="code-block">namespace App\Controllers;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;

class UserController
{
    public function index(): Response
    {
        $users = User::all();
        $content = view('users/index', ['users' => $users]);
        return response($content);
    }
    
    public function show(string $id): Response
    {
        $user = User::find($id);
        $content = view('users/show', ['user' => $user]);
        return response($content);
    }
}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Dependency Injection</h2>
                <pre class="code-block">public function __construct(
    private UserRepository $users,
    private Logger $logger
) {}</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Request & Response</h2>
                <pre class="code-block">public function store(Request $request): Response
{
    $data = $request->input();
    $user = User::create($data);
    
    session()->flash('success', 'User created!');
    return redirect('/users');
}</pre>
            </div>

            <div class="mt-8">
                <a href="/docs/routing" class="btn btn-secondary">← Previous: Routing</a>
                <a href="/docs/database" class="btn btn-primary">Next: Database →</a>
            </div>
        </div>
    </div>
</div>
