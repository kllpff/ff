<?php

namespace App\Models\Blog;

use FF\Database\Model;

/**
 * Comment - Blog post comment
 */
class Comment extends Model
{
    protected string $table = 'comments';
    protected array $fillable = ['post_id', 'author_name', 'email', 'content', 'approved'];
    protected array $hidden = [];

    /**
     * Get parent post
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Approve comment
     */
    public function approve(): void
    {
        $this->update(['approved' => true]);
    }

    /**
     * Get approved comments
     */
    public static function approved()
    {
        return static::where('approved', true)->orderBy('created_at', 'desc');
    }
}
