<?php
require 'db.php';

echo "Recent Notifications Logged:\n";
$stmt = $pdo->query("SELECT * FROM notifications ORDER BY sent_at DESC LIMIT 6");
while ($row = $stmt->fetch()) {
    echo "[" . $row['sent_at'] . "] To User ID " . $row['user_id'] . " (" . $row['type'] . "): " . substr($row['message'], 0, 50) . "...\n";
}

echo "\nReminder Status:\n";
$stmt = $pdo->query("SELECT id, is_sent FROM reminders WHERE scheduled_date = CURDATE()");
while ($row = $stmt->fetch()) {
    echo "Reminder ID " . $row['id'] . " - Sent: " . ($row['is_sent'] ? 'YES' : 'NO') . "\n";
}
?>
