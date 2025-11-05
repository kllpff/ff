<?php

namespace App\Listeners;

use App\Events\PostCreated;
use App\Services\EmailService;
use App\Services\TelegramService;

/**
 * SendPostCreatedNotification Listener
 * 
 * Sends notifications when a post is created.
 */
class SendPostCreatedNotification
{
    /**
     * The email service
     * 
     * @var EmailService
     */
    protected EmailService $emailService;

    /**
     * The telegram service
     * 
     * @var TelegramService
     */
    protected TelegramService $telegramService;

    /**
     * Create a new listener instance
     * 
     * @param EmailService $emailService
     * @param TelegramService $telegramService
     */
    public function __construct(EmailService $emailService, TelegramService $telegramService)
    {
        $this->emailService = $emailService;
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the event
     * 
     * @param PostCreated $event The event
     * @return void
     */
    public function handle(PostCreated $event): void
    {
        // Send email notification
        $this->emailService->sendPostCreatedNotification($event->post);

        // Send Telegram notification
        $this->telegramService->notifyPostCreated($event->post);

        // Log the event
        logger()->info('Post created', [
            'post_id' => $event->post->id,
            'title' => $event->post->title,
            'user_id' => $event->post->user_id
        ]);
    }
}
