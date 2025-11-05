<?php

namespace App\Models;

use FF\Framework\Database\Model;

class BlogComment extends Model
{
    protected string $table = 'blog_comments';
    protected array $fillable = ['content', 'post_id', 'user_id', 'author_name'];

    /**
     * Comment belongs to post
     */
    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    /**
     * Comment belongs to user (if authenticated)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->nullable();
    }
}
