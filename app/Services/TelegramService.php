<?php

namespace App\Services;

use App\Models\Post;

/**
 * Telegram Service
 * 
 * Handles sending notifications to Telegram.
 */
class TelegramService
{
    /**
     * The Telegram bot token
     * 
     * @var string|null
     */
    protected ?string $botToken;

    /**
     * The default chat ID
     * 
     * @var string|null
     */
    protected ?string $chatId;

    /**
     * Create a new TelegramService instance
     */
    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    /**
     * Send a message to Telegram
     * 
     * @param string $chatId The chat ID
     * @param string $text The message text
     * @return bool
     */
    public function sendMessage(string $chatId, string $text): bool
    {
        if (!$this->isConfigured()) {
            logger()->warning('Telegram is not configured');
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];

        try {
            $response = $this->makeRequest($url, $data);
            return $response['ok'] ?? false;
        } catch (\Exception $e) {
            logger()->error('Telegram send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify about post creation
     * 
     * @param Post $post The post
     * @return bool
     */
    public function notifyPostCreated(Post $post): bool
    {
        if (!$this->isConfigured()) {
            logger()->info('Telegram notification skipped (not configured)');
            return false;
        }

        $postUrl = config('app.url') . '/blog/' . $post->slug;
        
        $message = "📝 <b>New Post Created</b>\n\n";
        $message .= "<b>Title:</b> {$post->title}\n";
        $message .= "<b>Status:</b> {$post->status}\n\n";
        $message .= "<a href=\"{$postUrl}\">View Post</a>";

        return $this->sendMessage($this->chatId, $message);
    }

    /**
     * Check if Telegram is configured
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->chatId);
    }

    /**
     * Make HTTP request to Telegram API
     * 
     * @param string $url The API URL
     * @param array $data The request data
     * @return array
     */
    protected function makeRequest(string $url, array $data): array
    {
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data),
                'timeout' => 5
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            throw new \Exception('Failed to send Telegram request');
        }

        return json_decode($result, true) ?? [];
    }
}
