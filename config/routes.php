<?php

/**
 * Routes Configuration
 * 
 * Define all application routes here.
 * 
 * Example route definitions:
 * $router->get('/path', 'Controller@method')->name('route.name');
 * $router->post('/path', 'Controller@method')->middleware($middleware);
 * $router->group(['middleware' => [$middleware]], function($router) { ... });
 */

use FF\Framework\Http\Router;
use FF\Framework\Http\Middleware\RateLimitMiddleware;
use FF\Framework\Http\Middleware\AuthMiddleware;

return function(Router $router) {
    // ===== PUBLIC ROUTES =====
    
    // Home & Documentation
    $router->get('/', 'App\\Controllers\\HomeController@index')->name('home');
    $router->get('/docs', 'App\\Controllers\\DocsController@index')->name('docs');
    $router->get('/docs/{section}', 'App\\Controllers\\DocsController@show')->name('docs.show');
    
    // Public Blog
    $router->get('/blog', 'App\\Controllers\\BlogController@index')->name('blog.index');
    $router->get('/blog/{slug}', 'App\\Controllers\\BlogController@show')->name('blog.show');
    
    // ===== API ROUTES =====
    
    // API Posts (JSON responses)
    $router->get('/api/posts', 'App\\Controllers\\Api\\PostController@index')->name('api.posts.index');
    $router->get('/api/posts/{id}', 'App\\Controllers\\Api\\PostController@show')->name('api.posts.show');
    $router->post('/api/posts', 'App\\Controllers\\Api\\PostController@store')
        ->name('api.posts.store')
        ->middleware(new RateLimitMiddleware(10, 60)); // 10 posts per hour
    $router->put('/api/posts/{id}', 'App\\Controllers\\Api\\PostController@update')->name('api.posts.update');
    $router->delete('/api/posts/{id}', 'App\\Controllers\\Api\\PostController@destroy')->name('api.posts.destroy');
    
    // ===== AUTHENTICATION ROUTES =====
    
    // Registration
    $router->get('/register', 'App\\Controllers\\AuthController@showRegisterForm')->name('register');
    $router->post('/register', 'App\\Controllers\\AuthController@register')
        ->name('register.post')
        ->middleware(new RateLimitMiddleware(5, 15)); // 5 requests per 15 minutes
    
    // Email Verification
    $router->get('/verify-email/{token}', 'App\\Controllers\\AuthController@verifyEmail')->name('verify.email');
    
    // Login
    $router->get('/login', 'App\\Controllers\\AuthController@showLoginForm')->name('login');
    $router->post('/login', 'App\\Controllers\\AuthController@login')
        ->name('login.post')
        ->middleware(new RateLimitMiddleware(5, 15)); // 5 requests per 15 minutes
    
    // Password Reset
    $router->get('/forgot-password', 'App\\Controllers\\AuthController@showForgotPasswordForm')->name('password.request');
    $router->post('/forgot-password', 'App\\Controllers\\AuthController@sendPasswordResetLink')
        ->name('password.email')
        ->middleware(new RateLimitMiddleware(3, 60)); // 3 requests per hour
    $router->get('/reset-password/{token}', 'App\\Controllers\\AuthController@showResetPasswordForm')->name('password.reset');
    $router->post('/reset-password', 'App\\Controllers\\AuthController@resetPassword')->name('password.update');
    
    // Logout (GET for simplicity in demo)
    $router->get('/logout', 'App\\Controllers\\AuthController@logout')->name('logout');
    
    // ===== PROTECTED ROUTES (require authentication) =====
    
    $router->group(['middleware' => [new AuthMiddleware()]], function($router) {
        // Dashboard
        $router->get('/dashboard', 'App\\Controllers\\DashboardController@index')->name('dashboard');
        $router->get('/dashboard/profile', 'App\\Controllers\\DashboardController@profile')->name('dashboard.profile');
        $router->post('/dashboard/profile', 'App\\Controllers\\DashboardController@updateProfile')->name('dashboard.profile.update');
        
        // Posts Management
        $router->get('/dashboard/posts', 'App\\Controllers\\PostController@index')->name('posts.index');
        $router->get('/dashboard/posts/create', 'App\\Controllers\\PostController@create')->name('posts.create');
        $router->post('/dashboard/posts', 'App\\Controllers\\PostController@store')
            ->name('posts.store')
            ->middleware(new RateLimitMiddleware(10, 60)); // 10 posts per hour
        $router->get('/dashboard/posts/{id}/edit', 'App\\Controllers\\PostController@edit')->name('posts.edit');
        $router->put('/dashboard/posts/{id}', 'App\\Controllers\\PostController@update')->name('posts.update');
        $router->delete('/dashboard/posts/{id}', 'App\\Controllers\\PostController@destroy')->name('posts.destroy');
        
        // Categories Management
        $router->get('/dashboard/categories', 'App\\Controllers\\CategoryController@index')->name('categories.index');
        $router->get('/dashboard/categories/create', 'App\\Controllers\\CategoryController@create')->name('categories.create');
        $router->post('/dashboard/categories', 'App\\Controllers\\CategoryController@store')->name('categories.store');
        $router->get('/dashboard/categories/{id}/edit', 'App\\Controllers\\CategoryController@edit')->name('categories.edit');
        $router->put('/dashboard/categories/{id}', 'App\\Controllers\\CategoryController@update')->name('categories.update');
        $router->delete('/dashboard/categories/{id}', 'App\\Controllers\\CategoryController@destroy')->name('categories.destroy');
    });
};
