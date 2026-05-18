<?php
require_once '../db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing MySQLi Connection...\n";
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("MySQLi Connection Failed: " . $conn->connect_error . "\n");
}
echo "MySQLi Connected Successfully!\n";

// Generate a random email/mobile to test a clean registration
$test_email = "test_" . time() . "@example.com";
$test_mobile = "9" . substr(time(), 1, 9);
$test_pass = "Password123";
$hashed_pass = password_hash($test_pass, PASSWORD_DEFAULT);

echo "Attempting to insert test user: $test_email / $test_mobile\n";
$stmt = $conn->prepare("INSERT INTO users (name, email, mobile, password) VALUES (?, ?, ?, ?)");
$name = "Test User";
$stmt->bind_param("ssss", $name, $test_email, $test_mobile, $hashed_pass);

if ($stmt->execute()) {
    echo "User registered successfully in database!\n";
    $stmt->close();
    
    // Now test login authentication
    echo "Attempting to authenticate the user...\n";
    $login_stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $login_stmt->bind_param("s", $test_email);
    $login_stmt->execute();
    $result = $login_stmt->get_result();
    
    $auth = false;
    if ($user = $result->fetch_assoc()) {
        if (password_verify($test_pass, $user['password'])) {
            $auth = true;
        }
    }
    
    if ($auth) {
        echo "Authentication Successful! Password verified successfully.\n";
    } else {
        echo "Authentication FAILED!\n";
    }
    $login_stmt->close();
    
    // Clean up test user
    echo "Cleaning up test user...\n";
    $conn->query("DELETE FROM users WHERE email = '$test_email'");
} else {
    echo "Registration execution FAILED: " . $stmt->error . "\n";
    $stmt->close();
}

$conn->close();
