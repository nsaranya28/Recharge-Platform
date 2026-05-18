<?php
/**
 * Smart Recharge - Gmail SMTP Helper
 * 
 * This helper provides a robust, self-contained socket SMTP client to send verification OTP
 * emails directly using Google's secure SMTP server (smtp.gmail.com) on port 465 (SSL).
 * 
 * IMPORTANT CONFIGURATION:
 * 1. Ensure you have 2-Step Verification enabled on your Google Account: nsaranya282@gmail.com.
 * 2. Go to https://myaccount.google.com/apppasswords
 * 3. Search/Select "App passwords", create one for your website, copy the 16-character code.
 * 4. Replace the SMTP_PASS placeholder below with your 16-character App Password.
 */

define('SMTP_HOST', 'ssl://smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'nsaranya282@gmail.com');
define('SMTP_PASS', 'tppa woju kfbz xdun'); 

/**
 * Sends a registration OTP verification email
 * 
 * @param string $toEmail Recipient's email address
 * @param string $otp 6-digit verification code
 * @return array Status array with status ('success' or 'error') and message
 */
function sendGmailOTP($toEmail, $otp) {
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $user = SMTP_USER;
    $pass = SMTP_PASS;

    if ($pass === 'YOUR_GMAIL_APP_PASSWORD_HERE' || $pass === 'your-16-character-app-password' || empty($pass)) {
        return [
            'status' => 'error',
            'message' => 'Please generate a real 16-character Google App Password for <strong>nsaranya282@gmail.com</strong> and paste it into <strong>gmail_helper.php</strong>. You cannot use the placeholder text "your-16-character-app-password". <br><br><strong>How to generate one:</strong><br>1. Go to <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: #7c3aed; font-weight: bold;">Google App Passwords</a>.<br>2. Set a name like "Smart Recharge" and click <strong>Create</strong>.<br>3. Copy the <strong>16-digit code</strong> Google shows you and paste it in <strong>gmail_helper.php</strong>!'
        ];
    }

    // Open connection to Gmail SMTP over SSL
    $socket = @fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) {
        return [
            'status' => 'error', 
            'message' => "Could not connect to Gmail SMTP server: $errstr ($errno)"
        ];
    }

    // Inline response checker to verify SMTP status codes
    $readResponse = function($socket, $expectedCode) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        $code = substr($response, 0, 3);
        if ($code != $expectedCode) {
            throw new Exception("SMTP Error: " . trim($response));
        }
        return $response;
    };

    try {
        // Read greeting
        $readResponse($socket, '220');

        // EHLO Handshake
        fwrite($socket, "EHLO localhost\r\n");
        $readResponse($socket, '250');

        // Request login authentication
        fwrite($socket, "AUTH LOGIN\r\n");
        $readResponse($socket, '334');

        // Send base64-encoded username
        fwrite($socket, base64_encode($user) . "\r\n");
        $readResponse($socket, '334');

        // Send base64-encoded App Password
        // Strip any spaces just in case the user kept the spaces Google displays
        $cleanPass = str_replace(' ', '', $pass);
        fwrite($socket, base64_encode($cleanPass) . "\r\n");
        $readResponse($socket, '235');

        // Set Mail Sender
        fwrite($socket, "MAIL FROM:<$user>\r\n");
        $readResponse($socket, '250');

        // Set Recipient
        fwrite($socket, "RCPT TO:<$toEmail>\r\n");
        $readResponse($socket, '250');

        // Start Email Data sending
        fwrite($socket, "DATA\r\n");
        $readResponse($socket, '354');

        // Construct headers and body
        $subject = "Verify your Smart Recharge Account";
        $headers = [
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "From: Smart Recharge <$user>",
            "To: $toEmail",
            "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
            "Date: " . date('r'),
            "X-Mailer: PHP/" . phpversion()
        ];

        $htmlMessage = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Email Verification</title>
        </head>
        <body style=\"font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9fafb; margin: 0; padding: 2rem;\">
            <table align='center' border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.03); overflow: hidden;'>
                <tr>
                    <td style='background: linear-gradient(135deg, #8b5cf6, #7c3aed); padding: 2rem; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;'>Smart Recharge</h1>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 2.5rem 2rem;'>
                        <h2 style='color: #1f2937; margin: 0 0 1rem; font-size: 20px; font-weight: 600;'>Verify Your Email Address</h2>
                        <p style='color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 2rem;'>Thank you for choosing Smart Recharge. Use the 6-digit One-Time Password (OTP) below to verify and complete your registration:</p>
                        
                        <div style='background-color: #f5f3ff; border: 1px dashed #c084fc; border-radius: 12px; padding: 1.5rem; text-align: center; margin-bottom: 2rem;'>
                            <span style='font-size: 32px; font-weight: 800; letter-spacing: 6px; color: #7c3aed; font-family: monospace;'>$otp</span>
                        </div>
                        
                        <p style='color: #9ca3af; font-size: 13px; line-height: 1.5; margin: 0;'>This verification code is valid for 10 minutes. If you did not sign up for a Smart Recharge account, please ignore this email.</p>
                    </td>
                </tr>
                <tr>
                    <td style='background-color: #f9fafb; padding: 1.5rem; text-align: center; border-top: 1px solid #e5e7eb;'>
                        <p style='color: #6b7280; font-size: 12px; margin: 0;'>&copy; " . date('Y') . " Smart Recharge. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        // Prevent body escaping/injection issues with double dots
        $body = str_replace("\n.", "\n..", $htmlMessage);

        // Send headers & body, end DATA block with CRLF.CRLF
        fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
        $readResponse($socket, '250');

        // Quit SMTP session
        fwrite($socket, "QUIT\r\n");
        $readResponse($socket, '221');

        fclose($socket);
        return ['status' => 'success', 'message' => 'OTP sent successfully!'];
    } catch (Exception $e) {
        fclose($socket);
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

/**
 * Sends a recharge success confirmation email
 * 
 * @param string $toEmail Recipient's email address
 * @param string $userName Customer's name
 * @param string $operator Mobile operator name
 * @param string $price Recharge amount paid
 * @param string $validity Recharge validity in days
 * @param string $data Mobile data allowance per day
 * @param string $txId Transaction reference ID
 * @return array Status array with status ('success' or 'error') and message
 */
function sendRechargeSuccessEmail($toEmail, $userName, $operator, $price, $validity, $data, $txId) {
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $user = SMTP_USER;
    $pass = SMTP_PASS;

    if ($pass === 'YOUR_GMAIL_APP_PASSWORD_HERE' || $pass === 'your-16-character-app-password' || empty($pass)) {
        return [
            'status' => 'error',
            'message' => 'Please set your Google App Password in gmail_helper.php.'
        ];
    }

    $socket = @fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) {
        return [
            'status' => 'error', 
            'message' => "Could not connect to Gmail SMTP server: $errstr ($errno)"
        ];
    }

    $readResponse = function($socket, $expectedCode) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        $code = substr($response, 0, 3);
        if ($code != $expectedCode) {
            throw new Exception("SMTP Error: " . trim($response));
        }
        return $response;
    };

    try {
        $readResponse($socket, '220');

        fwrite($socket, "EHLO localhost\r\n");
        $readResponse($socket, '250');

        fwrite($socket, "AUTH LOGIN\r\n");
        $readResponse($socket, '334');

        fwrite($socket, base64_encode($user) . "\r\n");
        $readResponse($socket, '334');

        $cleanPass = str_replace(' ', '', $pass);
        fwrite($socket, base64_encode($cleanPass) . "\r\n");
        $readResponse($socket, '235');

        fwrite($socket, "MAIL FROM:<$user>\r\n");
        $readResponse($socket, '250');

        fwrite($socket, "RCPT TO:<$toEmail>\r\n");
        $readResponse($socket, '250');

        fwrite($socket, "DATA\r\n");
        $readResponse($socket, '354');

        $subject = "Smart Recharge - Recharge Successful!";
        $headers = [
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "From: Smart Recharge <$user>",
            "To: $toEmail",
            "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
            "Date: " . date('r'),
            "X-Mailer: PHP/" . phpversion()
        ];

        $htmlMessage = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Recharge Successful</title>
        </head>
        <body style=\"font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9fafb; margin: 0; padding: 2rem;\">
            <table align='center' border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.03); overflow: hidden;'>
                <tr>
                    <td style='background: linear-gradient(135deg, #8b5cf6, #7c3aed); padding: 2.5rem; text-align: center; color: #ffffff;'>
                        <div style='font-size: 40px; margin-bottom: 10px;'>🎉</div>
                        <h1 style='margin: 0; font-size: 26px; font-weight: 700; letter-spacing: -0.5px;'>Recharge Successful!</h1>
                        <p style='margin: 5px 0 0; opacity: 0.9; font-size: 14px;'>Transaction ID: $txId</p>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 2.5rem 2rem;'>
                        <p style='color: #1f2937; font-size: 16px; font-weight: 600; margin: 0 0 1rem;'>Hi $userName,</p>
                        <p style='color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 1.5rem;'>Your mobile recharge has been completed successfully. Here are your transaction and plan details:</p>
                        
                        <table width='100%' style='border-collapse: collapse; margin-bottom: 2rem; background-color: #f9fafb; border-radius: 12px; border: 1px solid #f3f4f6;'>
                            <tr>
                                <td style='padding: 1rem; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;'>Operator:</td>
                                <td style='padding: 1rem; color: #1f2937; font-size: 14px; font-weight: 600; text-align: right; border-bottom: 1px solid #f3f4f6;'>$operator</td>
                            </tr>
                            <tr>
                                <td style='padding: 1rem; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;'>Recharge Amount:</td>
                                <td style='padding: 1rem; color: #7c3aed; font-size: 16px; font-weight: 700; text-align: right; border-bottom: 1px solid #f3f4f6;'>₹$price</td>
                            </tr>
                            <tr>
                                <td style='padding: 1rem; color: #6b7280; font-size: 14px; border-bottom: 1px solid #f3f4f6;'>Data Allowance:</td>
                                <td style='padding: 1rem; color: #1f2937; font-size: 14px; font-weight: 600; text-align: right; border-bottom: 1px solid #f3f4f6;'>$data GB/Day</td>
                            </tr>
                            <tr>
                                <td style='padding: 1rem; color: #6b7280; font-size: 14px;'>Validity:</td>
                                <td style='padding: 1rem; color: #1f2937; font-size: 14px; font-weight: 600; text-align: right;'>$validity Days</td>
                            </tr>
                        </table>
                        
                        <div style='background-color: #f5f3ff; border-left: 4px solid #7c3aed; border-radius: 4px; padding: 1rem 1.5rem; margin-bottom: 2rem;'>
                            <p style='color: #5b21b6; font-size: 14px; font-style: italic; margin: 0; line-height: 1.6;'>
                                \"Successfully recharged ₹$price for $operator. Your plan of {$data}GB/Day for $validity Days is now active. Thank you for using Smart Recharge!\"
                            </p>
                        </div>
                        
                        <p style='color: #4b5563; font-size: 14px; line-height: 1.5; margin: 0;'>If you have any questions or did not authorize this transaction, please contact our support team immediately.</p>
                    </td>
                </tr>
                <tr>
                    <td style='background-color: #f9fafb; padding: 1.5rem; text-align: center; border-top: 1px solid #e5e7eb;'>
                        <p style='color: #6b7280; font-size: 12px; margin: 0;'>&copy; " . date('Y') . " Smart Recharge. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        $body = str_replace("\n.", "\n..", $htmlMessage);

        fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.\r\n");
        $readResponse($socket, '250');

        fwrite($socket, "QUIT\r\n");
        $readResponse($socket, '221');

        fclose($socket);
        return ['status' => 'success', 'message' => 'Recharge email sent successfully!'];
    } catch (Exception $e) {
        fclose($socket);
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
