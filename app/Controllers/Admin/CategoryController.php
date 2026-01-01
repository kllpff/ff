<?php

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Models\Post;
use FF\Http\Request;
use FF\Http\Response;
use FF\Validation\Validator;
use FF\Log\Logger;
use FF\Cache\Cache;

/**
 * Admin CategoryController
 *
 * Handles category management in the admin panel.
 */
class CategoryController extends AdminController
{
    protected Logger $logger;
    protected Cache $cache;

    public function __construct(Logger $logger, Cache $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * List all categories
     */
    public function index(Request $request): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $this->logger->debug('Loading categories list in admin panel');

        $query = Category::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
            $this->logger->debug('Searching categories', ['search' => $search]);
        }

        $categories = $query->orderBy('name', 'ASC')->paginate(20);

        // Get post counts for each category
        foreach ($categories->items() as $category) {
            $category->posts_count = Post::where('category_id', '=', $category->id)->count();
        }

        $this->logger->info('Categories loaded', ['count' => $categories->total()]);

        return response(view('admin.categories.index', [
            '__layout' => 'admin/layouts/app',
            'title' => 'Manage Categories',
            'categories' => $categories,
            'filters' => [
                'search' => $search ?? ''
            ]
        ]));
    }

    /**
     * Show create category form
     */
    public function create(): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        return response(view('admin.categories.create', [
            '__layout' => 'admin/layouts/app',
            'title' => 'Create Category'
        ]));
    }

    /**
     * Store new category
     */
    public function store(Request $request): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $this->logger->info('Creating new category');

        // Validate input
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'description' => 'max:1000'
        ]);

        if ($validator->fails()) {
            $this->logger->warning('Category validation failed', [
                'errors' => $validator->errors()
            ]);
            session()->flash('errors', $validator->errors());
            return response()->redirect('/admin/categories/create');
        }

        $data = $request->all();

        // Generate unique slug
        $slug = $this->generateSlug($data['name']);
        $originalSlug = $slug;
        $counter = 1;

        while (Category::where('slug', '=', $slug)->first()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Create category
        $category = Category::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null
        ]);

        $this->logger->info('Category created successfully', [
            'category_id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug
        ]);

        // Invalidate categories cache
        $this->cache->forget('blog_categories');
        $this->logger->debug('Categories cache invalidated');

        session()->flash('success', 'Category created successfully!');
        return response()->redirect('/admin/categories');
    }

    /**
     * Show edit category form
     */
    public function edit(Request $request, int $id): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $category = Category::find($id);

        if (!$category) {
            session()->flash('error', 'Category not found');
            return response()->redirect('/admin/categories');
        }

        // Get posts count
        $category->posts_count = Post::where('category_id', '=', $category->id)->count();

        return response(view('admin.categories.edit', [
            '__layout' => 'admin/layouts/app',
            'title' => 'Edit Category',
            'category' => $category
        ]));
    }

    /**
     * Update category
     */
    public function update(Request $request, int $id): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $this->logger->info('Updating category', ['category_id' => $id]);

        $category = Category::find($id);

        if (!$category) {
            $this->logger->warning('Category not found for update', ['category_id' => $id]);
            session()->flash('error', 'Category not found');
            return response()->redirect('/admin/categories');
        }

        // Validate input
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'description' => 'max:1000'
        ]);

        if ($validator->fails()) {
            $this->logger->warning('Category update validation failed', [
                'category_id' => $id,
                'errors' => $validator->errors()
            ]);
            session()->flash('errors', $validator->errors());
            return response()->redirect('/admin/categories/' . $id . '/edit');
        }

        $data = $request->all();

        // Update slug if name changed
        if ($data['name'] !== $category->name) {
            $slug = $this->generateSlug($data['name']);
            $originalSlug = $slug;
            $counter = 1;

            while (Category::where('slug', '=', $slug)->where('id', '!=', $id)->first()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $category->slug = $slug;
        }

        // Update category
        $category->name = $data['name'];
        $category->description = $data['description'] ?? null;
        $category->save();

        $this->logger->info('Category updated successfully', [
            'category_id' => $category->id,
            'name' => $category->name
        ]);

        // Invalidate categories cache
        $this->cache->forget('blog_categories');
        $this->logger->debug('Categories cache invalidated');

        session()->flash('success', 'Category updated successfully!');
        return response()->redirect('/admin/categories');
    }

    /**
     * Delete category
     */
    public function destroy(Request $request, int $id): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $this->logger->info('Deleting category', ['category_id' => $id]);

        $category = Category::find($id);

        if (!$category) {
            $this->logger->warning('Category not found for deletion', ['category_id' => $id]);
            session()->flash('error', 'Category not found');
            return response()->redirect('/admin/categories');
        }

        // Check if category has posts
        $postsCount = Post::where('category_id', '=', $id)->count();
        if ($postsCount > 0) {
            $this->logger->warning('Attempt to delete category with posts', [
                'category_id' => $id,
                'posts_count' => $postsCount
            ]);
            session()->flash('error', "Cannot delete category with {$postsCount} post(s). Reassign or delete posts first.");
            return response()->redirect('/admin/categories');
        }

        // Delete category
        $category->delete();

        $this->logger->info('Category deleted successfully', ['category_id' => $id]);

        // Invalidate categories cache
        $this->cache->forget('blog_categories');
        $this->logger->debug('Categories cache invalidated');

        session()->flash('success', 'Category deleted successfully!');
        return response()->redirect('/admin/categories');
    }

    /**
     * Generate URL-friendly slug from string
     */
    protected function generateSlug(string $string): string
    {
        $string = mb_strtolower($string, 'UTF-8');
        $string = preg_replace('/[^a-z0-9\s-]/u', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        return trim($string, '-');
    }
}
