<?php
/**
 * errors/404.php - 404 Not Found View
 */
?>

<h1>404 - Page Not Found</h1>

<p><?= htmlspecialchars($message ?? 'The page you are looking for does not exist.') ?></p>

<div style="margin-top: 30px;">
    <a href="/" style="color: #007acc; text-decoration: none;">← Back to home</a>
</div>
