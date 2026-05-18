<?php
require_once '../gmail_helper.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing sendRechargeSuccessEmail...\n";
$result = sendRechargeSuccessEmail('nsaranya282@gmail.com', 'Saranya', 'Airtel', '299.00', '28', '1.50', 'TX12345678');

echo "Result:\n";
print_r($result);
