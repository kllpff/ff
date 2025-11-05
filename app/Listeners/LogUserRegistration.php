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
            $logMessage = "[User Registered] ID: {$event->userId}, Name: {$event->name}, Email: {$event->email}, Time: " . date('Y-m-d H:i:s');
            error_log($logMessage);
            
            // Could write to database, analytics service, etc.
            // Example: Database::table('events')->insert([...]);
        } catch (\Exception $e) {
            error_log("[Event Error] Failed to handle UserRegistered: " . $e->getMessage());
        }
    }
}
