<?php

namespace App\Events;

use App\Models\Post;
use FF\Framework\Events\Event;

/**
 * PostDeleted Event
 * 
 * Fired when a post is deleted.
 */
class PostDeleted extends Event
{
    /**
     * The deleted post
     * 
     * @var Post
     */
    public Post $post;

    /**
     * Create a new PostDeleted event instance
     * 
     * @param Post $post The deleted post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
