<?php
require 'db.php';
try {
    // Drop the unique index on email if it exists
    // Note: In MySQL, the index name is usually 'email' if created as UNIQUE(email)
    $pdo->exec("ALTER TABLE users DROP INDEX email");
    echo "Unique constraint removed from email successfully.";
} catch (PDOException $e) {
    echo "Info: " . $e->getMessage(); // It might fail if index name is different or doesn't exist
}
?>
