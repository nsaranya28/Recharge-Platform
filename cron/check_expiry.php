<?php
// This script should be run daily via a Cron Job
// Example: 0 9 * * * /usr/bin/php /var/www/html/recharge/cron/check_expiry.php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../fast2sms_helper.php';

try {
    // 1. Get all reminders for today
    $stmt = $pdo->prepare("
        SELECT r.id as reminder_id, r.reminder_type, rh.mobile_number, rh.operator, rh.expiry_date, u.id as user_id, u.name, u.email, u.whatsapp, u.language
        FROM reminders r
        JOIN recharge_history rh ON r.recharge_id = rh.id
        JOIN users u ON rh.user_id = u.id
        WHERE r.scheduled_date = CURDATE() AND r.is_sent = FALSE
    ");
    $stmt->execute();
    $reminders = $stmt->fetchAll();

    foreach ($reminders as $rem) {
        $message = "";
        $days = ($rem['reminder_type'] == '3_days_before') ? 3 : (($rem['reminder_type'] == '1_day_before') ? 1 : 0);
        
        if ($rem['language'] == 'Tamil') {
            if ($days > 0) {
                $message = "வணக்கம் {$rem['name']}, உங்கள் {$rem['operator']} ரீசார்ஜ் ($days நாட்களில்) {$rem['expiry_date']} அன்று முடிவடைகிறது. தயவுசெய்து ரீசார்ஜ் செய்யவும்.";
            } else {
                $message = "எச்சரிக்கை: உங்கள் {$rem['operator']} ரீசார்ஜ் இன்றுடன் முடிவடைகிறது! தடையற்ற சேவையைப் பெற உடனே ரீசார்ஜ் செய்யுங்கள்.";
            }
        } else {
            if ($days > 0) {
                $message = "Hi {$rem['name']}, your {$rem['operator']} recharge for {$rem['mobile_number']} expires in $days days ({$rem['expiry_date']}). Recharge now to continue services!";
            } else {
                $message = "Alert: Your {$rem['operator']} recharge for {$rem['mobile_number']} expires today! Recharge now to avoid interruption.";
            }
        }

        // --- Send SMS ---
        // sendFast2SMS($rem['mobile_number'], 0, $rem['operator'], $message); // Adjusted helper needed
        
        // --- Mock Sending (For Log) ---
        $logStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, status) VALUES (?, ?, ?, ?)");
        
        // Log Email
        $logStmt->execute([$rem['user_id'], 'Email', $message, 'Sent']);
        
        // Log SMS
        $logStmt->execute([$rem['user_id'], 'SMS', $message, 'Sent']);
        
        // Log WhatsApp
        $logStmt->execute([$rem['user_id'], 'WhatsApp', $message, 'Sent']);

        // Mark as sent
        $updateStmt = $pdo->prepare("UPDATE reminders SET is_sent = TRUE WHERE id = ?");
        $updateStmt->execute([$rem['reminder_id']]);

        echo "Processed reminder for User ID: {$rem['user_id']} ({$rem['reminder_type']})\n";
    }

    echo "Cron Job completed successfully.\n";

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/cron_error.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
    echo "Error occurred: " . $e->getMessage();
}
?>
