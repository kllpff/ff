<?php
/**
 * mail/notification.php - Generic notification template (plain text)
 */
?>
Hello <?= htmlspecialchars($name ?? 'User') ?>,

<?= htmlspecialchars($message ?? '') ?>

Title: <?= htmlspecialchars($title ?? 'Notification') ?>
Time: <?= date('Y-m-d H:i:s') ?>

Best regards,
The FF Framework Team
