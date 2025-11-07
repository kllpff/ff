<?php

namespace App\Events;

use App\Models\Post;
use FF\Events\Event;

/**
 * PostCreated Event
 * 
 * Fired when a new post is created.
 */
class PostCreated extends Event
{
    /**
     * The created post
     * 
     * @var Post
     */
    public Post $post;

    /**
     * Create a new PostCreated event instance
     * 
     * @param Post $post The created post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
