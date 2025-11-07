<?php

namespace App\Listeners;

use App\Events\CommentAdded;

/**
 * LogCommentActivity - Listen to CommentAdded event
 * 
 * Logs comment activity
 */
class LogCommentActivity
{
    /**
     * Handle the event
     */
    public function handle(CommentAdded $event): void
    {
        try {
            // PII-safe logging: avoid author name and comment text
            \logger()->info('Comment added', [
                'event' => 'CommentAdded',
                'comment_id' => $event->commentId,
                'post_id' => $event->postId,
                'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'uri' => $_SERVER['REQUEST_URI'] ?? null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Exception $e) {
            \logger()->error('Failed to handle CommentAdded', [
                'event' => 'CommentAdded',
                'comment_id' => $event->commentId,
                'post_id' => $event->postId,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'uri' => $_SERVER['REQUEST_URI'] ?? null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        }
    }
}
