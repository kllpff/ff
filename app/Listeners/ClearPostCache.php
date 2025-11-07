<?php

namespace App\Listeners;

use App\Events\PostCreated;
use App\Events\PostUpdated;
use App\Events\PostDeleted;
use FF\Cache\Cache;

/**
 * ClearPostCache Listener
 * 
 * Clears post cache when posts are modified.
 */
class ClearPostCache
{
    /**
     * The cache instance
     * 
     * @var Cache
     */
    protected Cache $cache;

    /**
     * Create a new listener instance
     * 
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle the event
     * 
     * @param PostCreated|PostUpdated|PostDeleted $event The event
     * @return void
     */
    public function handle($event): void
    {
        // Clear all post-related cache
        $this->cache->forget('posts.all');
        $this->cache->forget('posts.published');
        $this->cache->forget('posts.recent');
        
        // Clear specific post cache if it's an update or delete
        if ($event instanceof PostUpdated || $event instanceof PostDeleted) {
            $this->cache->forget("posts.{$event->post->id}");
            $this->cache->forget("posts.slug.{$event->post->slug}");
        }

        logger()->debug('Post cache cleared', [
            'event' => class_basename($event),
            'post_id' => $event->post->id ?? null
        ]);
    }
}
