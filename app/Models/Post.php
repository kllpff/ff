<?php

namespace App\Models;

use FF\Database\Model;

/**
 * Post Model
 * 
 * Represents a blog post.
 */
class Post extends Model
{
    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected string $table = 'posts';

    /**
     * The attributes that are mass assignable
     * 
     * @var array
     */
    protected array $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
        'status',
        'views'
    ];

    /**
     * Get post's author relationship
     * 
     * @return \FF\Database\QueryBuilder
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get post's category relationship
     * 
     * @return \FF\Database\QueryBuilder
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Check if post is published
     * 
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Publish the post
     * 
     * @return bool
     */
    public function publish(): bool
    {
        $this->status = 'published';
        return $this->save();
    }

    /**
     * Unpublish the post
     * 
     * @return bool
     */
    public function unpublish(): bool
    {
        $this->status = 'draft';
        return $this->save();
    }

    /**
     * Generate slug from title
     * 
     * @param string $title The title
     * @return string The slug
     */
    public static function generateSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    /**
     * Scope to get only published posts
     * 
     * @return \FF\Database\QueryBuilder
     */
    public static function published()
    {
        return static::where('status', '=', 'published');
    }
}
