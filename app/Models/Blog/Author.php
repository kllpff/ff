<?php

namespace App\Models\Blog;

use FF\Database\Model;

/**
 * Author - Blog author model
 */
class Author extends Model
{
    protected string $table = 'authors';
    protected array $fillable = ['name', 'email', 'bio'];
    protected array $hidden = [];

    /**
     * Get author's posts
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * Get published posts
     */
    public function publishedPosts()
    {
        return $this->hasMany(Post::class, 'author_id')
            ->where('published', true);
    }

    /**
     * Get post count
     */
    public function getPostCount(): int
    {
        return $this->posts()->count();
    }
}
