<?php
require_once 'db.php';

try {
    // Add unique index to prevent future duplicates
    $sql = "ALTER TABLE plans ADD UNIQUE INDEX uniq_plan (operator, price, validity, data_per_day)";
    $pdo->exec($sql);
    echo "Unique index 'uniq_plan' added to 'plans' table.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42000' && strpos($e->getMessage(), 'Duplicate key name') !== false) {
        echo "Unique index already exists.\n";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
