<?php

namespace FF\Console\Commands;

use FF\Console\Command;
use App\Services\EmailService;

class TestEmailCommand extends Command
{
    protected string $signature = 'email:test';
    protected string $description = 'Send a test email to verify SMTP configuration';

    public function handle(): int
    {
        $this->info('Testing email configuration...');
        $this->line('');

        // Get configuration
        $driver = env('MAIL_DRIVER', 'mail');
        $host = env('MAIL_HOST', 'localhost');
        $port = env('MAIL_PORT', 25);
        $username = env('MAIL_USERNAME', '');
        $encryption = env('MAIL_ENCRYPTION', 'tls');
        $to = env('MAIL_TO_ADDRESS', 'info@aurek.ru');
        $from = env('MAIL_FROM_ADDRESS', 'i@aurek.ru');
        $debug = env('APP_DEBUG', false) ? 'true (emails are LOGGED, not sent)' : 'false (emails will be SENT)';

        $this->line('Configuration:');
        $this->line("  Driver:     {$driver}");
        $this->line("  Host:       {$host}");
        $this->line("  Port:       {$port}");
        $this->line("  Encryption: {$encryption}");
        $this->line("  Username:   {$username}");
        $this->line("  From:       {$from}");
        $this->line("  To:         {$to}");
        $this->line("  Debug Mode: {$debug}");
        $this->line('');
        $this->line('Sending test email...');
        $this->line('');

        try {
            $emailService = new EmailService();
            
            $testSubject = 'Тестовое письмо - ' . date('Y-m-d H:i:s');
            $testMessage = $this->getTestEmailTemplate();
            
            $sent = $emailService->sendTestEmail($to, $testSubject, $testMessage);

            if ($sent) {
                $this->line('');
                $this->info('✓ Email sent successfully!');
                $this->line('Check logs: tmp/logs/app.log');
                $this->line("Or check inbox: {$to}");
                return 0; // Success
            } else {
                $this->line('');
                $this->error('✗ Failed to send email.');
                $this->warn('Check logs for details: tmp/logs/app.log');
                return 1; // Failure
            }
        } catch (\Exception $e) {
            $this->line('');
            $this->error('✗ Exception occurred:');
            $this->error($e->getMessage());
            $this->line('');
            $this->warn('Check logs for full trace: tmp/logs/app.log');
            return 1; // Failure
        }
    }

    protected function getTestEmailTemplate(): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $driver = env('MAIL_DRIVER', 'mail');
        $host = env('MAIL_HOST', 'localhost');
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Тестовое письмо</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9;">
        <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="color: #667eea; margin-top: 0;">✉️ Тестовое письмо</h2>
            
            <p>Это тестовое письмо для проверки настройки SMTP.</p>
            
            <div style="margin: 20px 0; padding: 15px; background: #f0f4ff; border-left: 4px solid #667eea; border-radius: 4px;">
                <p style="margin: 0; font-weight: bold; color: #667eea;">Информация о отправке:</p>
                <ul style="margin: 10px 0;">
                    <li>Дата и время: {$timestamp}</li>
                    <li>Driver: {$driver}</li>
                    <li>Host: {$host}</li>
                </ul>
            </div>
            
            <p style="color: #28a745; font-weight: bold;">✓ Если вы получили это письмо, значит настройка SMTP работает корректно!</p>
            
            <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
            
            <p style="font-size: 12px; color: #666; margin: 0;">
                Это письмо отправлено автоматически с сайта АУРЭК для проверки конфигурации email.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
