<?php

namespace App\Controllers\Admin;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use FF\Http\Response;

/**
 * Admin Dashboard Controller
 *
 * Main dashboard for admin panel
 */
class DashboardController extends AdminController
{
    /**
     * Display admin dashboard
     */
    public function index(): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        // Get statistics
        $stats = [
            'total_posts' => Post::count(),
            'published_posts' => Post::where('status', '=', 'published')->count(),
            'draft_posts' => Post::where('status', '=', 'draft')->count(),
            'total_categories' => Category::count(),
            'total_users' => User::count(),
        ];

        // Get recent posts (manually hydrate relations)
        $recentPosts = Post::query()
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        foreach ($recentPosts as $post) {
            // Attach related user and category for display
            $user = User::find($post->getAttribute('user_id'));
            $category = Category::find($post->getAttribute('category_id'));
            $post->forceFill(['user' => $user, 'category' => $category]);
        }

        return response(view('admin.dashboard', [
            '__layout' => 'admin/layouts/app',
            'stats' => $stats,
            'recentPosts' => $recentPosts,
            'title' => 'Admin Dashboard'
        ]));
    }
}
