<?php
// twilio_helper.php

// Ensure that you have installed the Twilio SDK via Composer before using this file.
// Run this command in your project directory: composer require twilio/sdk

$sdk_loaded = false;
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    $sdk_loaded = true;
}

use Twilio\Rest\Client;

/**
 * Sends a recharge success SMS using the Twilio API.
 *
 * @param string $mobileNumber The recipient's 10-digit mobile number.
 * @param float|string $amount The recharge amount.
 * @param string $operator The telecom operator name (e.g., Jio, Airtel).
 * @return array An array containing 'success' (boolean) and 'message' (string).
 */
function sendRechargeSuccessSMS($mobileNumber, $amount, $operator) {
    global $sdk_loaded;
    if (!$sdk_loaded) return ['success' => false, 'message' => 'Twilio SDK missing'];

    // --- Twilio Configuration ---
    $account_sid   = getenv('TWILIO_ACCOUNT_SID');
    $auth_token    = getenv('TWILIO_AUTH_TOKEN');
    $twilio_number = getenv('TWILIO_PHONE_NUMBER');

    // --- Input Formatting ---
    $cleanNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
    if (strlen($cleanNumber) != 10) {
        return ['success'=>false,'message'=>'Invalid mobile number'];
    }
    $to = '+91' . $cleanNumber;

    // Simple message
    $msg = "Recharge successful Rs.{$amount} for {$operator}. Thank you!";

    try {
        $client = new Client($account_sid, $auth_token);
        $client->messages->create($to, ['from'=>$twilio_number,'body'=>$msg]);
        return ['success'=>true,'message'=>'SMS sent'];
    } catch (\Exception $e) {
        return ['success'=>false,'message'=>$e->getMessage()];
    }
}

/**
 * Sends a detailed recharge SMS with plan info.
 *
 * @param string $mobileNumber 10‑digit number.
 * @param float $amount
 * @param string $operator
 * @param float $dataPerDay  (GB per day)
 * @param int $validityDays
 * @return array
 */
function sendDetailedRechargeSMS($mobileNumber, $amount, $operator, $dataPerDay, $validityDays) {
    global $sdk_loaded;
    if (!$sdk_loaded) return ['success' => false, 'message' => 'Twilio SDK missing'];

    $account_sid   = getenv('TWILIO_ACCOUNT_SID');
    $auth_token    = getenv('TWILIO_AUTH_TOKEN');
    $twilio_number = getenv('TWILIO_PHONE_NUMBER');
    $cleanNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
    if (strlen($cleanNumber) != 10) {
        return ['success'=>false,'message'=>'Invalid mobile number'];
    }
    $to = '+91' . $cleanNumber;
    $msg = "Successfully recharged ₹{$amount} for {$operator}. Your plan of {$dataPerDay}GB/Day for {$validityDays} Days is now active. Thank you for using Smart Recharge!";
    try {
        $client = new Client($account_sid, $auth_token);
        $client->messages->create($to, ['from'=>$twilio_number,'body'=>$msg]);
        return ['success'=>true,'message'=>'SMS sent'];
    } catch (\Exception $e) {
        return ['success'=>false,'message'=>$e->getMessage()];
    }
}

/**
 * Sends a WhatsApp notification using Twilio WhatsApp API.
 *
 * @param string $mobileNumber The recipient's 10-digit mobile number.
 * @param string $message The message body.
 * @param string|null $mediaUrl Optional URL to a PDF or Image.
 * @return array
 */
function sendWhatsAppNotification($mobileNumber, $message, $mediaUrl = null) {
    $account_sid   = getenv('TWILIO_ACCOUNT_SID') ?: "ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
    $auth_token    = getenv('TWILIO_AUTH_TOKEN') ?: "your_auth_token";
    $from_whatsapp = getenv('TWILIO_WHATSAPP_NUMBER') ?: "whatsapp:+14155238886"; // Default Twilio Sandbox number

    $cleanNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
    if (strlen($cleanNumber) != 10) {
        return ['success' => false, 'message' => 'Invalid mobile number'];
    }
    $to = 'whatsapp:+91' . $cleanNumber;

    try {
        $client = new Client($account_sid, $auth_token);
        $options = ['from' => $from_whatsapp, 'body' => $message];
        
        if ($mediaUrl) {
            $options['mediaUrl'] = [$mediaUrl];
        }

        $client->messages->create($to, $options);
        return ['success' => true, 'message' => 'WhatsApp message sent'];
    } catch (\Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Sends a stylized WhatsApp recharge success notification.
 */
function sendWhatsAppRechargeSuccess($mobile, $userName, $operator, $amount, $validity, $txId) {
    $dateTime = date('d-M-Y H:i:s');
    $msg = "━━━━━━━━━━━━━━━━\n";
    $msg .= "📲 *RECHARGE SUCCESSFUL*\n";
    $msg .= "━━━━━━━━━━━━━━━━\n\n";
    $msg .= "Hello *{$userName}*,\n\n";
    $msg .= "Your recharge is successful! 🎉\n\n";
    $msg .= "🔹 *Operator:* {$operator}\n";
    $msg .= "🔹 *Amount:* ₹{$amount}\n";
    $msg .= "🔹 *Validity:* {$validity} Days\n";
    $msg .= "🔹 *TXN ID:* {$txId}\n";
    $msg .= "🔹 *Date:* {$dateTime}\n\n";
    $msg .= "━━━━━━━━━━━━━━━━\n";
    $msg .= "Thank you for using Smart Recharge! ✨";

    return sendWhatsAppNotification($mobile, $msg);
}

/**
 * Sends a WhatsApp reminder notification.
 */
function sendWhatsAppReminder($mobile, $userName, $operator, $expiryDate, $daysLeft) {
    $msg = "━━━━━━━━━━━━━━━━\n";
    $msg .= "🔔 *RECHARGE REMINDER*\n";
    $msg .= "━━━━━━━━━━━━━━━━\n\n";
    $msg .= "Hello *{$userName}*,\n\n";
    
    if ($daysLeft > 0) {
        $msg .= "Your *{$operator}* plan expires in *{$daysLeft} days* ({$expiryDate}). ⏳\n\n";
    } else {
        $msg .= "Your *{$operator}* plan *expires today*! ⚠️\n\n";
    }
    
    $msg .= "Recharge now to enjoy uninterrupted services. ⚡\n\n";
    $msg .= "━━━━━━━━━━━━━━━━\n";
    $msg .= "Click here to recharge: http://localhost/recharge";

    return sendWhatsAppNotification($mobile, $msg);
}
?>
