<?php

namespace App\Controllers;

use App\Models\Post;
use App\Models\Category;
use FF\Framework\Http\Request;
use FF\Framework\Http\Response;
use FF\Framework\Validation\Validator;
use FF\Framework\Events\EventDispatcher;
use FF\Framework\Log\Logger;
use FF\Framework\Cache\Cache;

/**
 * PostController
 * 
 * Handles CRUD operations for blog posts.
 * Demonstrates: events, validation, logging, cache invalidation.
 */
class PostController
{
    /**
     * The event dispatcher
     * 
     * @var EventDispatcher
     */
    protected EventDispatcher $dispatcher;

    /**
     * The logger instance
     * 
     * @var Logger
     */
    protected Logger $logger;

    /**
     * The cache instance
     * 
     * @var Cache
     */
    protected Cache $cache;

    /**
     * Create a new PostController instance
     * 
     * @param EventDispatcher $dispatcher
     * @param Logger $logger
     * @param Cache $cache
     */
    public function __construct(EventDispatcher $dispatcher, Logger $logger, Cache $cache)
    {
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * List user's posts
     * 
     * @return Response
     */
    public function index(): Response
    {
        $userId = session('auth_user_id');

        $this->logger->debug('Loading user posts', ['user_id' => $userId]);

        // Get user's posts
        $posts = Post::where('user_id', '=', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->get();

        $this->logger->info('User posts loaded', [
            'user_id' => $userId,
            'count' => count($posts)
        ]);

        $content = view('posts/index', [
            'title' => 'My Posts - FF Framework',
            'posts' => $posts
        ]);
        
        return response($content);
    }

    /**
     * Show create post form
     * 
     * @return Response
     */
    public function create(): Response
    {
        // Get all categories
        $categories = Category::all();

        $content = view('posts/create', [
            'title' => 'Create Post - FF Framework',
            'categories' => $categories
        ]);
        
        return response($content);
    }

    /**
     * Store new post
     * 
     * Demonstrates validation, logging, events, and cache invalidation.
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $userId = session('auth_user_id');

        $this->logger->info('Creating new post', ['user_id' => $userId]);

        // Validate input
        $validator = new Validator($request->all(), [
            'category_id' => 'required|integer',
            'title' => 'required|min:3|max:255',
            'content' => 'required|min:10',
            'status' => 'required|in:draft,published'
        ]);

        if (!$validator->validate()) {
            $this->logger->warning('Post validation failed', [
                'user_id' => $userId,
                'errors' => $validator->getErrors()
            ]);
            session()->flash('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            return redirect('/dashboard/posts/create');
        }

        $data = $request->all();

        // Check if category exists
        $category = Category::find($data['category_id']);
        if (!$category) {
            $this->logger->error('Invalid category during post creation', [
                'user_id' => $userId,
                'category_id' => $data['category_id']
            ]);
            session()->flash('error', 'Invalid category');
            return redirect('/dashboard/posts/create');
        }

        // Generate unique slug
        $slug = Post::generateSlug($data['title']);
        $originalSlug = $slug;
        $counter = 1;
        
        while (Post::where('slug', '=', $slug)->first()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Create post
        $post = Post::create([
            'user_id' => $userId,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published' ? date('Y-m-d H:i:s') : null
        ]);

        $this->logger->info('Post created successfully', [
            'post_id' => $post->id,
            'user_id' => $userId,
            'title' => $post->title,
            'status' => $post->status
        ]);

        // Invalidate blog cache when new post is published
        if ($post->status === 'published') {
            $this->cache->forget('blog_index_posts');
            $this->logger->debug('Blog cache invalidated after post creation');
        }

        // Dispatch PostCreated event
        $this->dispatcher->dispatch('post.created', $post);

        session()->flash('success', 'Post created successfully!');
        return redirect('/dashboard/posts');
    }

    /**
     * Show edit post form
     * 
     * @param Request $request
     * @param int $id The post ID
     * @return Response
     */
    public function edit(Request $request, int $id): Response
    {
        $userId = session('auth_user_id');

        // Find post
        $post = Post::find($id);

        if (!$post || $post->user_id !== $userId) {
            session()->flash('error', 'Post not found');
            return redirect('/dashboard/posts');
        }

        // Get all categories
        $categories = Category::all();

        $content = view('posts/edit', [
            'title' => 'Edit Post - FF Framework',
            'post' => $post,
            'categories' => $categories
        ]);
        
        return response($content);
    }

    /**
     * Update post
     * 
     * Demonstrates validation, logging, events, and cache invalidation.
     * 
     * @param Request $request
     * @param int $id The post ID
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        $userId = session('auth_user_id');

        $this->logger->info('Updating post', ['post_id' => $id, 'user_id' => $userId]);

        // Find post
        $post = Post::find($id);

        if (!$post || $post->user_id !== $userId) {
            $this->logger->warning('Unauthorized post update attempt', [
                'post_id' => $id,
                'user_id' => $userId
            ]);
            session()->flash('error', 'Post not found');
            return redirect('/dashboard/posts');
        }

        // Validate input
        $validator = new Validator($request->all(), [
            'category_id' => 'required|integer',
            'title' => 'required|min:3|max:255',
            'content' => 'required|min:10',
            'status' => 'required|in:draft,published'
        ]);

        if (!$validator->validate()) {
            $this->logger->warning('Post update validation failed', [
                'post_id' => $id,
                'errors' => $validator->getErrors()
            ]);
            session()->flash('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            return redirect('/dashboard/posts/' . $id . '/edit');
        }

        $data = $request->all();

        // Check if category exists
        $category = Category::find($data['category_id']);
        if (!$category) {
            $this->logger->error('Invalid category during post update', [
                'post_id' => $id,
                'category_id' => $data['category_id']
            ]);
            session()->flash('error', 'Invalid category');
            return redirect('/dashboard/posts/' . $id . '/edit');
        }

        // Update slug if title changed
        if ($data['title'] !== $post->title) {
            $slug = Post::generateSlug($data['title']);
            $originalSlug = $slug;
            $counter = 1;
            
            while (Post::where('slug', '=', $slug)->where('id', '!=', $id)->first()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $post->slug = $slug;
        }

        // Track if status changed
        $statusChanged = $post->status !== $data['status'];

        // Update post
        $post->category_id = $data['category_id'];
        $post->title = $data['title'];
        $post->content = $data['content'];
        $post->status = $data['status'];
        
        // Set published_at if status changed to published
        if ($data['status'] === 'published' && $post->published_at === null) {
            $post->published_at = date('Y-m-d H:i:s');
        }
        
        $post->save();

        $this->logger->info('Post updated successfully', [
            'post_id' => $post->id,
            'title' => $post->title,
            'status' => $post->status,
            'status_changed' => $statusChanged
        ]);

        // Invalidate cache
        if ($post->status === 'published') {
            $this->cache->forget('blog_index_posts');
            $this->cache->forget('blog_post_' . $post->slug);
            $this->logger->debug('Blog cache invalidated after post update');
        }

        // Dispatch PostUpdated event
        $this->dispatcher->dispatch('post.updated', $post);

        session()->flash('success', 'Post updated successfully!');
        return redirect('/dashboard/posts');
    }

    /**
     * Delete post
     * 
     * Demonstrates events, logging, and cache invalidation.
     * 
     * @param Request $request
     * @param int $id The post ID
     * @return Response
     */
    public function destroy(Request $request, int $id): Response
    {
        $userId = session('auth_user_id');

        $this->logger->info('Deleting post', ['post_id' => $id, 'user_id' => $userId]);

        // Find post
        $post = Post::find($id);

        if (!$post || $post->user_id !== $userId) {
            $this->logger->warning('Unauthorized post deletion attempt', [
                'post_id' => $id,
                'user_id' => $userId
            ]);
            session()->flash('error', 'Post not found');
            return redirect('/dashboard/posts');
        }

        $postSlug = $post->slug;
        $wasPublished = $post->status === 'published';

        // Dispatch PostDeleted event before deletion
        $this->dispatcher->dispatch('post.deleted', $post);

        // Delete post
        $post->delete();

        $this->logger->info('Post deleted successfully', [
            'post_id' => $id,
            'was_published' => $wasPublished
        ]);

        // Invalidate cache if post was published
        if ($wasPublished) {
            $this->cache->forget('blog_index_posts');
            $this->cache->forget('blog_post_' . $postSlug);
            $this->logger->debug('Blog cache invalidated after post deletion');
        }

        session()->flash('success', 'Post deleted successfully!');
        return redirect('/dashboard/posts');
    }
}
