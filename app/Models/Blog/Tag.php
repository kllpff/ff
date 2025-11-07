<?php

namespace App\Models\Blog;

use FF\Database\Model;

/**
 * Tag - Blog tag/category
 */
class Tag extends Model
{
    protected string $table = 'tags';
    protected array $fillable = ['name', 'slug'];
    protected array $hidden = [];

    /**
     * Get posts with this tag
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tags', 'tag_id', 'post_id');
    }

    /**
     * Get published posts count
     */
    public function getPublishedPostsCount(): int
    {
        return $this->posts()->where('published', true)->count();
    }
}
