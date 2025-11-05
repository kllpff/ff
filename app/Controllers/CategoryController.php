<?php

namespace App\Controllers;

use App\Models\Category;
use FF\Framework\Http\Request;
use FF\Framework\Http\Response;
use FF\Framework\Validation\Validator;
use FF\Framework\Log\Logger;
use FF\Framework\Cache\Cache;

/**
 * CategoryController
 * 
 * Handles CRUD operations for categories.
 * Demonstrates: validation, logging, cache management.
 */
class CategoryController
{
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
     * Create a new CategoryController instance
     * 
     * @param Logger $logger
     * @param Cache $cache
     */
    public function __construct(Logger $logger, Cache $cache)
    {
        $this->logger = $logger;
        $this->cache = $cache;
    }
    /**
     * List all categories
     * 
     * @return Response
     */
    public function index(): Response
    {
        $this->logger->debug('Loading categories list');
        
        $categories = Category::all();

        $this->logger->info('Categories loaded', ['count' => count($categories)]);

        $content = view('categories/index', [
            'title' => 'Categories - FF Framework',
            'categories' => $categories
        ]);
        
        return response($content);
    }

    /**
     * Show create category form
     * 
     * @return Response
     */
    public function create(): Response
    {
        $content = view('categories/create', [
            'title' => 'Create Category - FF Framework'
        ]);
        
        return response($content);
    }

    /**
     * Store new category
     * 
     * Demonstrates validation, logging, and cache invalidation.
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $this->logger->info('Creating new category');

        // Validate input
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'description' => 'max:1000'
        ]);

        if (!$validator->validate()) {
            $this->logger->warning('Category validation failed', [
                'errors' => $validator->getErrors()
            ]);
            session()->flash('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            return redirect('/dashboard/categories/create');
        }

        $data = $request->all();

        // Generate unique slug
        $slug = Category::generateSlug($data['name']);
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
        return redirect('/dashboard/categories');
    }

    /**
     * Show edit category form
     * 
     * @param Request $request
     * @param int $id The category ID
     * @return Response
     */
    public function edit(Request $request, int $id): Response
    {
        $category = Category::find($id);

        if (!$category) {
            session()->flash('error', 'Category not found');
            return redirect('/dashboard/categories');
        }

        $content = view('categories/edit', [
            'title' => 'Edit Category - FF Framework',
            'category' => $category
        ]);
        
        return response($content);
    }

    /**
     * Update category
     * 
     * Demonstrates validation, logging, and cache invalidation.
     * 
     * @param Request $request
     * @param int $id The category ID
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        $this->logger->info('Updating category', ['category_id' => $id]);

        $category = Category::find($id);

        if (!$category) {
            $this->logger->warning('Category not found for update', ['category_id' => $id]);
            session()->flash('error', 'Category not found');
            return redirect('/dashboard/categories');
        }

        // Validate input
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'description' => 'max:1000'
        ]);

        if (!$validator->validate()) {
            $this->logger->warning('Category update validation failed', [
                'category_id' => $id,
                'errors' => $validator->getErrors()
            ]);
            session()->flash('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            return redirect('/dashboard/categories/' . $id . '/edit');
        }

        $data = $request->all();

        // Update slug if name changed
        if ($data['name'] !== $category->name) {
            $slug = Category::generateSlug($data['name']);
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
        return redirect('/dashboard/categories');
    }

    /**
     * Delete category
     * 
     * Demonstrates logging and cache invalidation.
     * 
     * @param Request $request
     * @param int $id The category ID
     * @return Response
     */
    public function destroy(Request $request, int $id): Response
    {
        $this->logger->info('Deleting category', ['category_id' => $id]);

        $category = Category::find($id);

        if (!$category) {
            $this->logger->warning('Category not found for deletion', ['category_id' => $id]);
            session()->flash('error', 'Category not found');
            return redirect('/dashboard/categories');
        }

        // Check if category has posts
        $postsCount = \App\Models\Post::where('category_id', '=', $id)->count();
        if ($postsCount > 0) {
            $this->logger->warning('Attempt to delete category with posts', [
                'category_id' => $id,
                'posts_count' => $postsCount
            ]);
            session()->flash('error', 'Cannot delete category with posts');
            return redirect('/dashboard/categories');
        }

        // Delete category
        $category->delete();

        $this->logger->info('Category deleted successfully', ['category_id' => $id]);

        // Invalidate categories cache
        $this->cache->forget('blog_categories');
        $this->logger->debug('Categories cache invalidated');

        session()->flash('success', 'Category deleted successfully!');
        return redirect('/dashboard/categories');
    }
}
