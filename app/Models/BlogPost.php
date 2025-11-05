<?php

namespace App\Models;

use FF\Framework\Database\Model;

class BlogPost extends Model
{
    protected string $table = 'blog_posts';
    protected array $fillable = ['title', 'content', 'category_id', 'user_id', 'views', 'slug', 'status'];

    /**
     * Post belongs to category
     */
    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    /**
     * Post belongs to author (user)
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Post has many comments
     */
    public function comments()
    {
        return $this->hasMany(BlogComment::class, 'post_id');
    }
}
