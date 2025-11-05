<?php $__layout = 'main'; ?>

<div class="section">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold mb-4">Routing</h1>
            <p class="text-lg text-secondary mb-8">Define application routes with powerful routing system</p>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Basic Routing</h2>
                <p class="text-secondary mb-4">Routes are defined in <code class="code-inline">config/routes.php</code></p>
                
                <h3 class="text-xl font-semibold mb-3 mt-6">GET Route</h3>
                <pre class="code-block">$router->get('/users', 'UserController@index');</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">POST Route</h3>
                <pre class="code-block">$router->post('/users', 'UserController@store');</pre>

                <h3 class="text-xl font-semibold mb-3 mt-6">Multiple Methods</h3>
                <pre class="code-block">$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@destroy');</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Route Parameters</h2>
                <pre class="code-block">$router->get('/users/{id}', 'UserController@show');
$router->get('/posts/{slug}', 'PostController@show');</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Named Routes</h2>
                <pre class="code-block">$router->get('/profile', 'ProfileController@show')->name('profile');

// Generate URL
$url = route('profile'); // /profile</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Route Groups</h2>
                <pre class="code-block">$router->group(['prefix' => '/admin'], function($router) {
    $router->get('/users', 'Admin\\UserController@index');
    $router->get('/posts', 'Admin\\PostController@index');
});</pre>
            </div>

            <div class="card mb-6">
                <h2 class="text-2xl font-bold mb-4">Middleware</h2>
                <pre class="code-block">$router->get('/dashboard', 'DashboardController@index')
    ->middleware('auth');</pre>
            </div>

            <div class="mt-8">
                <a href="/docs/installation" class="btn btn-secondary">← Previous: Installation</a>
                <a href="/docs/controllers" class="btn btn-primary">Next: Controllers →</a>
            </div>
        </div>
    </div>
</div>
