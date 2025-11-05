<?php

namespace App\Events;

/**
 * UserRegistered - Event fired when a new user registers
 */
class UserRegistered
{
    /**
     * User ID
     */
    public int $userId;

    /**
     * User name
     */
    public string $name;

    /**
     * User email
     */
    public string $email;

    /**
     * Create a new event instance
     */
    public function __construct(int $userId, string $name, string $email)
    {
        $this->userId = $userId;
        $this->name = $name;
        $this->email = $email;
    }
}
