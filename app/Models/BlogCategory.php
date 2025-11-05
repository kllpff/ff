<?php

namespace App\Models;

use FF\Framework\Database\Model;

class BlogCategory extends Model
{
    protected string $table = 'blog_categories';
    protected array $fillable = ['name', 'slug', 'description'];

    /**
     * Category has many posts
     */
    public function posts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }
}
