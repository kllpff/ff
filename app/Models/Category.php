<?php

namespace App\Models;

use FF\Database\Model;

/**
 * Category Model
 * 
 * Represents a blog post category.
 */
class Category extends Model
{
    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected string $table = 'categories';

    /**
     * The attributes that are mass assignable
     * 
     * @var array
     */
    protected array $fillable = [
        'name',
        'slug',
        'description'
    ];

    /**
     * Get category's posts relationship
     * 
     * @return \FF\Database\QueryBuilder
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    /**
     * Generate slug from name
     * 
     * @param string $name The name
     * @return string The slug
     */
    public static function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}
