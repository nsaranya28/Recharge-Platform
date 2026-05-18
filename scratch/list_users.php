<?php
require_once '../db.php';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Listing all users in 'users' table:\n";
$result = $conn->query("SELECT id, name, email, mobile, password FROM users");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | Email: " . $row['email'] . " | Mobile: " . $row['mobile'] . " | Pass Hash: " . substr($row['password'], 0, 15) . "...\n";
    }
} else {
    echo "No users found in database!\n";
}

$conn->close();
