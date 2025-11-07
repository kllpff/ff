<?php

namespace App\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use FF\Http\Response;
use FF\Cache\Cache;
use FF\Log\Logger;

/**
 * BlogController
 * 
 * Handles public blog pages.
 * Demonstrates framework features: caching, logging.
 */
class BlogController
{
    /**
     * The cache instance
     * 
     * @var Cache
     */
    protected Cache $cache;

    /**
     * The logger instance
     * 
     * @var Logger
     */
    protected Logger $logger;

    /**
     * Create a new BlogController instance
     * 
     * @param Cache $cache
     * @param Logger $logger
     */
    public function __construct(Cache $cache, Logger $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Show blog index (list of published posts)
     * 
     * Demonstrates caching for improved performance.
     * 
     * @return Response
     */
    public function index(): Response
    {
        // Try to get posts from cache
        $posts = $this->cache->get('blog_index_posts');
        
        if (!$posts) {
            $this->logger->info('Cache miss: Loading blog posts from database');
            $posts = Post::where('status', '=', 'published')
                        ->orderBy('created_at', 'DESC')
                        ->limit(20)
                        ->get();
            $this->cache->put('blog_index_posts', $posts, 5);
        }

        // Get categories from cache
        $categories = $this->cache->get('blog_categories');
        
        if (!$categories) {
            $this->logger->info('Cache miss: Loading categories from database');
            $categories = Category::all();
            $this->cache->put('blog_categories', $categories, 10);
        }

        $this->logger->debug('Blog index rendered', [
            'posts_count' => count($posts),
            'categories_count' => count($categories)
        ]);

        $content = view('blog/index', [
            'title' => 'Blog - FF Framework',
            'posts' => $posts,
            'categories' => $categories
        ]);
        
        return response($content);
    }

    /**
     * Show single post
     * 
     * Demonstrates caching for individual posts and logging.
     * 
     * @param string $slug The post slug
     * @return Response
     */
    public function show(string $slug): Response
    {
        // Try to get from cache first
        $cacheKey = 'blog_post_' . $slug;
        $postData = $this->cache->get($cacheKey);
        
        if (!$postData) {
            $this->logger->info('Cache miss: Loading post from database', ['slug' => $slug]);
            
            $post = Post::where('slug', '=', $slug)->first();
            
            if (!$post || !$post->isPublished()) {
                $this->logger->warning('Post not found or not published', ['slug' => $slug]);
                session()->flash('error', 'Post not found');
                return redirect('/blog');
            }
            
            $postData = [
                'post' => $post,
                'category' => Category::find($post->category_id),
                'author' => User::find($post->user_id)
            ];
            
            // Cache for 10 minutes
            $this->cache->put($cacheKey, $postData, 10);
        }

        $this->logger->debug('Post displayed', [
            'slug' => $slug,
            'title' => $postData['post']->title
        ]);

        $content = view('blog/show', [
            'title' => $postData['post']->title . ' - Blog - FF Framework',
            'post' => $postData['post'],
            'category' => $postData['category'],
            'author' => $postData['author']
        ]);
        
        return response($content);
    }
}
