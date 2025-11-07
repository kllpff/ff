<?php

namespace App\Providers;

use FF\Core\ServiceProvider;
use FF\Events\EventDispatcher;
use App\Events\PostCreated;
use App\Events\PostUpdated;
use App\Events\PostDeleted;
use App\Listeners\SendPostCreatedNotification;
use App\Listeners\ClearPostCache;

/**
 * Event Service Provider
 * 
 * Registers event listeners for the application.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings
     * 
     * @var array
     */
    protected array $listen = [
        PostCreated::class => [
            SendPostCreatedNotification::class,
            ClearPostCache::class,
        ],
        PostUpdated::class => [
            ClearPostCache::class,
        ],
        PostDeleted::class => [
            ClearPostCache::class,
        ],
    ];

    /**
     * Register the service provider
     * 
     * @return void
     */
    public function register(): void
    {
        // Register EventDispatcher in container
        $this->app->singleton(EventDispatcher::class, function ($app) {
            return new EventDispatcher($app);
        });
    }

    /**
     * Boot the service provider
     * 
     * @return void
     */
    public function boot(): void
    {
        $dispatcher = $this->app->resolve(EventDispatcher::class);

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $dispatcher->listen($event, function ($event) use ($listener) {
                    $listenerInstance = $this->app->resolve($listener);
                    $listenerInstance->handle($event);
                });
            }
        }
    }
}

