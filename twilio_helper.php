<?php
// twilio_helper.php

// Ensure that you have installed the Twilio SDK via Composer before using this file.
// Run this command in your project directory: composer require twilio/sdk

// Load the Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

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

// ============================================================================
// Example Usage / Function Call (You can comment this out in production)
// ============================================================================

/*
// Example variables coming from a successful recharge flow
$userMobile = "9876543210"; // Replace with your actual verified Twilio number for testing
$rechargeAmount = 499;
$telecomOperator = "Airtel";

// Call the function
$response = sendRechargeSuccessSMS($userMobile, $rechargeAmount, $telecomOperator);

// Handle the response
if ($response['success']) {
    echo "<b>Success:</b> " . $response['message'];
} else {
    echo "<b>Error:</b> " . $response['message'];
}
*/
?>
