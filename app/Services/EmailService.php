<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;

/**
 * Email Service
 * 
 * Handles sending emails for authentication and notifications.
 */
class EmailService
{
    /**
     * Send email verification email
     * 
     * @param User $user The user
     * @param string $token The verification token
     * @return bool
     */
    public function sendVerificationEmail(User $user, string $token): bool
    {
        $verifyUrl = config('app.url') . '/verify-email/' . $token;
        
        $subject = 'Verify Your Email - ' . config('app.name');
        $message = $this->renderVerificationEmail($user, $verifyUrl);
        
        return $this->send($user->email, $subject, $message);
    }

    /**
     * Send password reset email
     * 
     * @param User $user The user
     * @param string $token The reset token
     * @return bool
     */
    public function sendPasswordResetEmail(User $user, string $token): bool
    {
        $resetUrl = config('app.url') . '/reset-password/' . $token;
        
        $subject = 'Reset Your Password - ' . config('app.name');
        $message = $this->renderPasswordResetEmail($user, $resetUrl);
        
        return $this->send($user->email, $subject, $message);
    }

    /**
     * Send post created notification
     * 
     * @param Post $post The post
     * @return bool
     */
    public function sendPostCreatedNotification(Post $post): bool
    {
        // Get admin email from config or use a default
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        
        $subject = 'New Post Created - ' . $post->title;
        $message = $this->renderPostCreatedEmail($post);
        
        return $this->send($adminEmail, $subject, $message);
    }

    /**
     * Send email using PHP's mail function
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $message Email body (HTML)
     * @return bool
     */
    protected function send(string $to, string $subject, string $message): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . config('app.name') . ' <noreply@' . parse_url(config('app.url'), PHP_URL_HOST) . '>',
            'Reply-To: noreply@' . parse_url(config('app.url'), PHP_URL_HOST),
            'X-Mailer: PHP/' . phpversion()
        ];

        // Log email in development mode instead of sending
        if (env('APP_DEBUG', false)) {
            logger()->info('Email would be sent', [
                'to' => $to,
                'subject' => $subject,
                'message' => $message
            ]);
            return true;
        }

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }

    /**
     * Render verification email HTML
     * 
     * @param User $user The user
     * @param string $verifyUrl The verification URL
     * @return string
     */
    protected function renderVerificationEmail(User $user, string $verifyUrl): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>Hello {$user->name}!</h2>
        <p>Thank you for registering with {$this->escapeHtml(config('app.name'))}.</p>
        <p>Please verify your email address by clicking the button below:</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{$this->escapeHtml($verifyUrl)}" 
               style="display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">
                Verify Email
            </a>
        </p>
        <p>Or copy and paste this URL into your browser:</p>
        <p><a href="{$this->escapeHtml($verifyUrl)}">{$this->escapeHtml($verifyUrl)}</a></p>
        <p>This link will expire in 24 hours.</p>
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #666;">
            If you did not create an account, please ignore this email.
        </p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render password reset email HTML
     * 
     * @param User $user The user
     * @param string $resetUrl The reset URL
     * @return string
     */
    protected function renderPasswordResetEmail(User $user, string $resetUrl): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>Hello {$user->name}!</h2>
        <p>You requested to reset your password for {$this->escapeHtml(config('app.name'))}.</p>
        <p>Click the button below to reset your password:</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{$this->escapeHtml($resetUrl)}" 
               style="display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">
                Reset Password
            </a>
        </p>
        <p>Or copy and paste this URL into your browser:</p>
        <p><a href="{$this->escapeHtml($resetUrl)}">{$this->escapeHtml($resetUrl)}</a></p>
        <p>This link will expire in 1 hour.</p>
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #666;">
            If you did not request a password reset, please ignore this email.
        </p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render post created notification email HTML
     * 
     * @param Post $post The post
     * @return string
     */
    protected function renderPostCreatedEmail(Post $post): string
    {
        $postUrl = config('app.url') . '/blog/' . $post->slug;
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Post Created</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>New Post Created</h2>
        <p>A new post has been created on {$this->escapeHtml(config('app.name'))}.</p>
        <h3>{$this->escapeHtml($post->title)}</h3>
        <p>{$this->escapeHtml(substr($post->content, 0, 200))}...</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{$this->escapeHtml($postUrl)}" 
               style="display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">
                View Post
            </a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Escape HTML entities
     * 
     * @param string $text The text to escape
     * @return string
     */
    protected function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
