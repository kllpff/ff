<?php

namespace App\Controllers\Api;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;
use App\Models\Post;
use App\Models\Category;

/**
 * PostController API
 * 
 * Handles JSON API requests for blog posts
 */
class PostController
{
    /**
     * Get all posts
     * 
     * GET /api/posts
     * 
     * @return Response
     */
    public function index(): Response
    {
        $posts = Post::where('status', '=', 'published')
                    ->orderBy('created_at', 'DESC')
                    ->limit(20)
                    ->get();

        return $this->json($posts);
    }

    /**
     * Get single post
     * 
     * GET /api/posts/{id}
     * 
     * @param string $id Post ID
     * @return Response
     */
    public function show(string $id): Response
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        return $this->json($post);
    }

    /**
     * Create new post
     * 
     * POST /api/posts
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $data = $request->json();

        // Simple validation
        if (!isset($data['title']) || !isset($data['content'])) {
            return $this->json([
                'error' => 'Validation failed',
                'message' => 'Title and content are required'
            ], 422);
        }

        // Generate slug
        $slug = Post::generateSlug($data['title']);

        // Create post
        $post = Post::create([
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'],
            'category_id' => $data['category_id'] ?? null,
            'user_id' => $data['user_id'] ?? 1,
            'status' => $data['status'] ?? 'draft'
        ]);

        return $this->json([
            'message' => 'Post created successfully',
            'data' => $post
        ], 201);
    }

    /**
     * Update post
     * 
     * PUT /api/posts/{id}
     * 
     * @param Request $request
     * @param string $id Post ID
     * @return Response
     */
    public function update(Request $request, string $id): Response
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $data = $request->json();

        // Update fields
        if (isset($data['title'])) {
            $post->title = $data['title'];
            $post->slug = Post::generateSlug($data['title']);
        }
        if (isset($data['content'])) {
            $post->content = $data['content'];
        }
        if (isset($data['category_id'])) {
            $post->category_id = $data['category_id'];
        }
        if (isset($data['status'])) {
            $post->status = $data['status'];
        }

        $post->save();

        return $this->json([
            'message' => 'Post updated successfully',
            'data' => $post
        ]);
    }

    /**
     * Delete post
     * 
     * DELETE /api/posts/{id}
     * 
     * @param string $id Post ID
     * @return Response
     */
    public function destroy(string $id): Response
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        $post->delete();

        return $this->json([
            'message' => 'Post deleted successfully'
        ]);
    }

    /**
     * Helper to return JSON response
     * 
     * @param mixed $data
     * @param int $status
     * @return Response
     */
    private function json($data, int $status = 200): Response
    {
        return new Response(
            json_encode($data),
            $status,
            ['Content-Type' => 'application/json']
        );
    }
}
