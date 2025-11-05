<?php

namespace App\Events;

/**
 * CommentAdded - Event fired when a comment is added
 */
class CommentAdded
{
    /**
     * Comment ID
     */
    public int $commentId;

    /**
     * Post ID
     */
    public int $postId;

    /**
     * Commenter name
     */
    public string $authorName;

    /**
     * Comment text
     */
    public string $comment;

    /**
     * Create a new event instance
     */
    public function __construct(int $commentId, int $postId, string $authorName, string $comment)
    {
        $this->commentId = $commentId;
        $this->postId = $postId;
        $this->authorName = $authorName;
        $this->comment = $comment;
    }
}
