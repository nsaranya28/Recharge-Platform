<?php
require 'db.php';

echo "Checking for recharge history...\n";
$stmt = $pdo->query("SELECT id, mobile_number FROM recharge_history ORDER BY id DESC LIMIT 1");
$recharge = $stmt->fetch();

if (!$recharge) {
    echo "No recharge history found. Creating a dummy one for testing...\n";
    $pdo->exec("INSERT INTO users (name, email, mobile, whatsapp, language) VALUES ('Test User', 'test@example.com', '9876543210', '9876543210', 'English')");
    $user_id = $pdo->lastInsertId();
    $pdo->exec("INSERT INTO recharge_history (user_id, mobile_number, operator, plan_id, amount, expiry_date) VALUES ($user_id, '9876543210', 'Jio', 1, 149, DATE_ADD(CURDATE(), INTERVAL 28 DAY))");
    $recharge_id = $pdo->lastInsertId();
    $recharge = ['id' => $recharge_id, 'mobile_number' => '9876543210'];
}

$recharge_id = $recharge['id'];
echo "Using Recharge ID: $recharge_id\n";

echo "Creating a test reminder for today...\n";
// Delete any existing reminders for this ID to avoid clutter
$pdo->exec("DELETE FROM reminders WHERE recharge_id = $recharge_id");

// Insert a reminder for today
$pdo->exec("INSERT INTO reminders (recharge_id, reminder_type, scheduled_date, is_sent) VALUES ($recharge_id, '3_days_before', CURDATE(), 0)");

echo "Test reminder created successfully for today's date.\n";
echo "Now run the cron script to see it in action.\n";
?>
