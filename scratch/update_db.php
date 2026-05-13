<?php
require 'db.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN password VARCHAR(255) AFTER email");
    echo "Password column added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Password column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
