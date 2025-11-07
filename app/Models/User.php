<?php

namespace App\Models;

use FF\Database\Model;

/**
 * User Model
 * 
 * Represents a user in the system.
 */
class User extends Model
{
    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected string $table = 'users';

    /**
     * The attributes that are mass assignable
     * 
     * @var array
     */
    protected array $fillable = [
        'name',
        'email',
        'password'
    ];

    /**
     * Guarded attributes that cannot be mass assigned
     *
     * @var array
     */
    protected array $guarded = [
        'id',
        'email_verified_at',
        'verification_token',
        'reset_token',
        'reset_token_expires',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden from serialization
     * 
     * @var array
     */
    protected array $hidden = [
        'password',
        'verification_token',
        'reset_token',
        'remember_token'
    ];

    /**
     * Check if user's email is verified
     * 
     * @return bool
     */
    public function isEmailVerified(): bool
    {
        return !empty($this->email_verified_at);
    }

    /**
     * Mark email as verified
     * 
     * @return bool
     */
    public function markEmailAsVerified(): bool
    {
        $this->email_verified_at = date('Y-m-d H:i:s');
        $this->verification_token = null;
        return $this->save();
    }

    /**
     * Generate password reset token
     * 
     * @return string The generated token
     */
    public function generatePasswordResetToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->reset_token = hash('sha256', $token);
        $this->reset_token_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $this->save();
        
        return $token; // Return plain token for email
    }

    /**
     * Check if password reset token is valid
     * 
     * @param string $token The token to check
     * @return bool
     */
    public function isValidPasswordResetToken(string $token): bool
    {
        if (empty($this->reset_token) || empty($this->reset_token_expires)) {
            return false;
        }

        // Check if token matches
        if (hash('sha256', $token) !== $this->reset_token) {
            return false;
        }

        // Check if token is not expired
        return strtotime($this->reset_token_expires) > time();
    }

    /**
     * Clear password reset token
     * 
     * @return bool
     */
    public function clearPasswordResetToken(): bool
    {
        $this->reset_token = null;
        $this->reset_token_expires = null;
        return $this->save();
    }

    /**
     * Get user's posts relationship
     * 
     * @return \FF\Database\QueryBuilder
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}
