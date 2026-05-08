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
    // Replace these with your actual Twilio Account SID, Auth Token, and Twilio Phone Number.
    // You can find these in your Twilio Console (https://console.twilio.com/).
    $account_sid   = 'YOUR_TWILIO_ACCOUNT_SID';
    $auth_token    = 'YOUR_TWILIO_AUTH_TOKEN';
    $twilio_number = 'YOUR_TWILIO_PHONE_NUMBER'; // e.g., +12345678901

    // --- Input Formatting ---
    // Clean the mobile number by removing any non-numeric characters
    $cleanNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
    
    // Basic validation: ensure it's exactly 10 digits (for India)
    if (strlen($cleanNumber) != 10) {
        return [
            'success' => false,
            'message' => 'Invalid mobile number. Please provide a 10-digit number.'
        ];
    }

    // Format mobile number with country code (+91 for India)
    $formattedNumber = '+91' . $cleanNumber;

    // --- Message Construction ---
    // Format: "Recharge successful Rs.{amount} for {operator}. Thank you!"
    $messageBody = "Recharge successful Rs.{$amount} for {$operator}. Thank you!";

    // --- Sending SMS ---
    try {
        // Initialize the Twilio client using your credentials
        $client = new Client($account_sid, $auth_token);

        // Attempt to send the message
        $message = $client->messages->create(
            $formattedNumber, // The recipient's phone number
            [
                'from' => $twilio_number, // Your Twilio phone number
                'body' => $messageBody    // The text message
            ]
        );

        // If we reach here, the API call was successful
        return [
            'success' => true,
            'message' => 'SMS sent successfully. Message SID: ' . $message->sid
        ];

    } catch (Exception $e) {
        // Catch any errors from the Twilio API or network issues
        return [
            'success' => false,
            'message' => 'Failed to send SMS. Error: ' . $e->getMessage()
        ];
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
