<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Database - QueryBuilder</h1>
            <p class="text-lg text-secondary mb-8">Powerful and intuitive database query builder for FF Framework</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Connection</h2>
                <p class="text-secondary mb-4">Database connection is configured in <code class="code-inline">.env</code> file:</p>
                <pre class="code-block">DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ff_framework
DB_USERNAME=root
DB_PASSWORD=secret</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">SELECT Queries</h2>
                
                <h3 class="text-xl font-semibold mb-3 mt-6">Basic Query</h3>
                <pre class="code-block">$users = db()->table('users')->get();</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">WHERE Clause</h3>
                <pre class="code-block">$user = db()->table('users')
    ->where('email', '=', 'john@example.com')
    ->first();

// Multiple conditions
$users = db()->table('users')
    ->where('status', '=', 'active')
    ->where('role', '=', 'admin')
    ->get();</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">WHERE IN</h3>
                <pre class="code-block">$users = db()->table('users')
    ->whereIn('id', [1, 2, 3, 4, 5])
    ->get();</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">WHERE BETWEEN</h3>
                <pre class="code-block">$users = db()->table('users')
    ->whereBetween('created_at', ['2024-01-01', '2024-12-31'])
    ->get();</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">ORDER BY</h3>
                <pre class="code-block">$users = db()->table('users')
    ->orderBy('created_at', 'DESC')
    ->get();</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">LIMIT & OFFSET</h3>
                <pre class="code-block">$users = db()->table('users')
    ->limit(10)
    ->offset(20)
    ->get();</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">JOIN Operations</h2>

                <h3 class="text-xl font-semibold mb-3 mt-6">INNER JOIN</h3>
                <pre class="code-block">$posts = db()->table('posts')
    ->join('categories', 'posts.category_id', '=', 'categories.id')
    ->select('posts.*', 'categories.name as category_name')
    ->get();</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">LEFT JOIN</h3>
                <pre class="code-block">$posts = db()->table('posts')
    ->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
    ->get();</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Aggregate Functions</h2>

                <h3 class="text-xl font-semibold mb-3 mt-6">COUNT</h3>
                <pre class="code-block">$count = db()->table('users')->count();</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">SUM & AVG</h3>
                <pre class="code-block">$total = db()->table('orders')->sum('amount');
$average = db()->table('orders')->avg('amount');</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">MIN & MAX</h3>
                <pre class="code-block">$min = db()->table('products')->min('price');
$max = db()->table('products')->max('price');</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">INSERT</h2>

                <h3 class="text-xl font-semibold mb-3 mt-6">Insert Single Record</h3>
                <pre class="code-block">db()->table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('secret')
]);</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">Insert and Get ID</h3>
                <pre class="code-block">$id = db()->table('users')->insertGetId([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com'
]);</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">UPDATE</h2>
                <pre class="code-block">db()->table('users')
    ->where('id', '=', 1)
    ->update([
        'name' => 'John Updated',
        'updated_at' => date('Y-m-d H:i:s')
    ]);</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">DELETE</h2>
                <pre class="code-block">db()->table('users')
    ->where('id', '=', 1)
    ->delete();</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Transactions</h2>
                <pre class="code-block">db()->transaction(function($db) {
    $db->table('users')->insert(['name' => 'User 1']);
    $db->table('profiles')->insert(['user_id' => 1]);
    
    // If exception is thrown, both will rollback
});</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Raw SQL</h2>
                <pre class="code-block">$users = db()->raw('SELECT * FROM users WHERE status = ?', ['active']);</pre>
            </div>

            <div class="mt-8">
                <a href="/docs" class="btn btn-secondary">← Back to Documentation</a>
            </div>
        </div>
    </div>
</div>
