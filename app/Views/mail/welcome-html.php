<?php
/**
 * mail/welcome-html.php - Welcome email template (HTML)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #d32f2f;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #d32f2f;
            margin: 0;
            font-size: 28px;
        }
        .content {
            margin: 20px 0;
        }
        .content p {
            margin: 15px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            background-color: #d32f2f;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #b71c1c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Welcome to FF Framework</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong><?= htmlspecialchars($name ?? 'User') ?></strong>,</p>
            
            <p>Welcome to FF Framework! We're thrilled to have you as part of our community.</p>
            
            <p>Your account has been successfully created. Here's what you can do now:</p>
            
            <ul style="color: #666;">
                <li>Explore our comprehensive documentation</li>
                <li>Start building amazing web applications</li>
                <li>Join our developer community</li>
                <li>Get support and share your feedback</li>
            </ul>
            
            <p style="text-align: center;">
                <a href="<?= htmlspecialchars($loginUrl ?? 'https://example.com/login') ?>" class="button">
                    Get Started Now
                </a>
            </p>
            
            <p>If you have any questions or need assistance, don't hesitate to reach out to our support team.</p>
            
            <p>Happy coding!<br>
            <strong>The FF Framework Team</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 FF Framework. All rights reserved.</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
