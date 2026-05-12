<?php
require_once 'db.php';

try {
    // 1. Drop the restrictive unique index
    $pdo->exec("ALTER TABLE recharge_history DROP INDEX uniq_recharge");
    echo "Successfully dropped restrictive unique index.<br>";
    
    // 2. Clear out the duplicates from the history to clean up the dashboard (optional but good for this user's state)
    // Actually, I'll just leave them for now unless asked, but dropping the index is the key fix.
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
