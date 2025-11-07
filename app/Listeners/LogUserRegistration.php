<?php

namespace App\Listeners;

use App\Events\UserRegistered;

/**
 * LogUserRegistration - Listen to UserRegistered event
 * 
 * Logs user registration for analytics
 */
class LogUserRegistration
{
    /**
     * Handle the event
     */
    public function handle(UserRegistered $event): void
    {
        try {
            // PII-safe logging: avoid name/email
            \logger()->info('User registered', [
                'event' => 'UserRegistered',
                'user_id' => $event->userId,
                'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'uri' => $_SERVER['REQUEST_URI'] ?? null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);

            // Could write to database, analytics service, etc.
            // Example: Database::table('events')->insert([...]);
        } catch (\Exception $e) {
            \logger()->error('Failed to handle UserRegistered', [
                'event' => 'UserRegistered',
                'user_id' => $event->userId,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'uri' => $_SERVER['REQUEST_URI'] ?? null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        }
    }
}
