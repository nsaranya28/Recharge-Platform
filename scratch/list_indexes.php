<?php
require_once '../db.php';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Listing indexes on 'users' table:\n";
$result = $conn->query("SHOW INDEX FROM users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "Table: " . $row['Table'] . " | Non_unique: " . $row['Non_unique'] . " | Key_name: " . $row['Key_name'] . " | Column_name: " . $row['Column_name'] . "\n";
    }
}
$conn->close();
