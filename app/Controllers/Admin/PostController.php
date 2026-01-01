<?php

namespace App\Controllers\Admin;

use App\Models\Post;
use App\Models\Category;
use FF\Http\Request;
use FF\Http\Response;
use FF\Http\UploadedFile;
use FF\Validation\Validator;
use FF\Events\EventDispatcher;
use FF\Log\Logger;
use FF\Cache\Cache;

/**
 * Admin Post Controller
 *
 * Handles post management in admin panel
 */
class PostController extends AdminController
{
    protected EventDispatcher $dispatcher;
    protected Logger $logger;
    protected Cache $cache;

    public function __construct(EventDispatcher $dispatcher, Logger $logger, Cache $cache)
    {
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * Display list of all posts
     */
    public function index(Request $request): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $query = Post::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where('title', 'LIKE', "%{$search}%");
            $this->logger->debug('Searching posts', ['search' => $search]);
        }

        // Status filter
        if ($status = $request->get('status')) {
            $query->where('status', '=', $status);
            $this->logger->debug('Filtering posts by status', ['status' => $status]);
        }

        // Category filter
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', '=', (int)$categoryId);
            $this->logger->debug('Filtering posts by category', ['category_id' => $categoryId]);
        }

        // Get paginated posts
        $posts = $query->orderBy('created_at', 'DESC')->paginate(20);

        // Get categories for filter dropdown
        $categories = \App\Models\Category::query()->orderBy('name', 'ASC')->get();

        return response(view('admin.posts.index', [
            '__layout' => 'admin/layouts/app',
            'posts' => $posts,
            'categories' => $categories,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
                'category_id' => $categoryId ?? ''
            ],
            'title' => 'Manage Posts'
        ]));
    }

    /**
     * Show form to create new post
     */
    public function create(): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $categories = Category::all();

        return response(view('admin.posts.create', [
            '__layout' => 'admin/layouts/app',
            'categories' => $categories,
            'title' => 'Create New Post'
        ]));
    }

    /**
     * Store new post
     */
    public function store(Request $request): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        // Validate input
        $validator = new Validator($request->all(), [
            'title' => 'required|min:3|max:200',
            'content' => 'required|min:10',
            'category_id' => 'required|integer',
            'status' => 'required|in:draft,published'
        ]);

        if ($validator->fails()) {
            session()->flash('error', 'Validation failed');
            session()->flash('errors', $validator->errors());
            session()->flash('old', $request->all());
            return response()->redirect('/admin/posts/create');
        }

        try {
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $this->uploadImage($request->file('image'));
            }

            // Create post
            $post = Post::create([
                'title' => $request->input('title'),
                'slug' => $this->generateSlug($request->input('title')),
                'content' => $request->input('content'),
                'excerpt' => $request->input('excerpt', ''),
                'category_id' => $request->input('category_id'),
                'user_id' => $this->getAdminId(),
                'status' => $request->input('status', 'draft'),
                'image' => $imagePath,
                'published_at' => $request->input('status') === 'published' ? date('Y-m-d H:i:s') : null
            ]);

            $this->logger->info('Post created via admin panel', [
                'post_id' => $post->id,
                'title' => $post->title,
                'admin_id' => $this->getAdminId()
            ]);

            // Clear cache
            $this->cache->forget('posts_all');
            $this->cache->forget('posts_published');

            session()->flash('success', 'Post created successfully!');
            return response()->redirect('/admin/posts');
        } catch (\Exception $e) {
            $this->logger->error('Failed to create post', [
                'error' => $e->getMessage(),
                'admin_id' => $this->getAdminId()
            ]);

            session()->flash('error', 'Failed to create post. Please try again.');
            return response()->redirect('/admin/posts/create');
        }
    }

    /**
     * Show form to edit post
     */
    public function edit(int $id): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $post = Post::findOrFail($id);
        $categories = Category::all();

        return response(view('admin.posts.edit', [
            '__layout' => 'admin/layouts/app',
            'post' => $post,
            'categories' => $categories,
            'title' => 'Edit Post: ' . $post->title
        ]));
    }

    /**
     * Update post
     */
    public function update(Request $request, int $id): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $post = Post::findOrFail($id);

        // Validate input
        $validator = new Validator($request->all(), [
            'title' => 'required|min:3|max:200',
            'content' => 'required|min:10',
            'category_id' => 'required|integer',
            'status' => 'required|in:draft,published'
        ]);

        if ($validator->fails()) {
            session()->flash('error', 'Validation failed');
            session()->flash('errors', $validator->errors());
            return response()->redirect('/admin/posts/' . $id . '/edit');
        }

        try {
            // Handle image upload or deletion
            $updateData = [
                'title' => $request->input('title'),
                'slug' => $this->generateSlug($request->input('title')),
                'content' => $request->input('content'),
                'excerpt' => $request->input('excerpt', ''),
                'category_id' => $request->input('category_id'),
                'status' => $request->input('status'),
                'published_at' => $request->input('status') === 'published' && !$post->published_at
                    ? date('Y-m-d H:i:s')
                    : $post->published_at
            ];

            // Handle new image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($post->image) {
                    $this->deleteImage($post->image);
                }
                $imagePath = $this->uploadImage($request->file('image'));
                if ($imagePath) {
                    $updateData['image'] = $imagePath;
                }
            }
            // Handle image removal checkbox
            elseif ($request->input('remove_image') === '1' && $post->image) {
                $this->deleteImage($post->image);
                $updateData['image'] = null;
            }

            $post->update($updateData);

            $this->logger->info('Post updated via admin panel', [
                'post_id' => $post->id,
                'admin_id' => $this->getAdminId()
            ]);

            // Clear cache
            $this->cache->forget('posts_all');
            $this->cache->forget('posts_published');
            $this->cache->forget('post_' . $post->id);

            session()->flash('success', 'Post updated successfully!');
            return response()->redirect('/admin/posts');
        } catch (\Exception $e) {
            $this->logger->error('Failed to update post', [
                'post_id' => $id,
                'error' => $e->getMessage()
            ]);

            session()->flash('error', 'Failed to update post. Please try again.');
            return response()->redirect('/admin/posts/' . $id . '/edit');
        }
    }

    /**
     * Delete post
     */
    public function destroy(int $id): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        try {
            $post = Post::findOrFail($id);
            $title = $post->title;

            // Delete image if exists
            if ($post->image) {
                $this->deleteImage($post->image);
            }

            $post->delete();

            $this->logger->warning('Post deleted via admin panel', [
                'post_id' => $id,
                'title' => $title,
                'admin_id' => $this->getAdminId()
            ]);

            // Clear cache
            $this->cache->forget('posts_all');
            $this->cache->forget('posts_published');
            $this->cache->forget('post_' . $id);

            session()->flash('success', 'Post deleted successfully!');
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete post', [
                'post_id' => $id,
                'error' => $e->getMessage()
            ]);

            session()->flash('error', 'Failed to delete post.');
        }

        return response()->redirect('/admin/posts');
    }

    /**
     * Generate URL-friendly slug from title
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Check if slug exists and add number if needed
        $originalSlug = $slug;
        $counter = 1;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * Upload post image
     *
     * @param UploadedFile|null $file Upload file object
     * @return string|null Path to uploaded image or null on failure
     */
    private function uploadImage(?UploadedFile $file): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return null;
        }

        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, $allowedTypes)) {
            return null;
        }

        try {
            // Generate unique filename
            $extension = $file->getClientExtension();
            $filename = uniqid('post_', true) . '.' . $extension;

            // Move file to public/uploads/posts
            $targetDir = base_path('public/uploads/posts');
            $file->move($targetDir, $filename);

            // Return relative path for storage in database
            return '/uploads/posts/' . $filename;
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload image', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Delete post image file
     *
     * @param string $imagePath Path to image
     */
    private function deleteImage(string $imagePath): void
    {
        if ($imagePath) {
            $fullPath = __DIR__ . '/../../../public' . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
