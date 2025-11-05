<?php

namespace App\Models\Blog;

use FF\Framework\Database\Model;

/**
 * Post - Blog post model
 */
class Post extends Model
{
    protected string $table = 'posts';
    protected array $fillable = ['title', 'slug', 'content', 'author_id', 'published'];
    protected array $hidden = [];

    /**
     * Get post author
     */
    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    /**
     * Get post comments
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    /**
     * Get post tags
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags', 'post_id', 'tag_id');
    }

    /**
     * Publish post
     */
    public function publish(): void
    {
        $this->update(['published' => true]);
    }

    /**
     * Unpublish post
     */
    public function unpublish(): void
    {
        $this->update(['published' => false]);
    }

    /**
     * Check if published
     */
    public function isPublished(): bool
    {
        return (bool) $this->published;
    }

    /**
     * Get published posts
     */
    public static function published()
    {
        return static::where('published', true)->orderBy('created_at', 'desc');
    }
}
