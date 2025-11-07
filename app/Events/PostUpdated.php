<?php

namespace App\Events;

use App\Models\Post;
use FF\Events\Event;

/**
 * PostUpdated Event
 * 
 * Fired when a post is updated.
 */
class PostUpdated extends Event
{
    /**
     * The updated post
     * 
     * @var Post
     */
    public Post $post;

    /**
     * Create a new PostUpdated event instance
     * 
     * @param Post $post The updated post
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }
}
