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
            $logMessage = "[Comment Added] ID: {$event->commentId}, Post: {$event->postId}, Author: {$event->authorName}";
            error_log($logMessage);
        } catch (\Exception $e) {
            error_log("[Event Error] Failed to handle CommentAdded: " . $e->getMessage());
        }
    }
}
