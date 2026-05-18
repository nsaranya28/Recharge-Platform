<?php
require_once '../gmail_helper.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing sendGmailOTP...\n";
$result = sendGmailOTP('nsaranya282@gmail.com', '123456');

echo "Result:\n";
print_r($result);
