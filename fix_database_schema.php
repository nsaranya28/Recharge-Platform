<?php
require_once 'db.php';

try {
    echo "Updating database schema...<br>";

    // 1. Add 'category' column to plans table
    try {
        $pdo->exec("ALTER TABLE plans ADD COLUMN category ENUM('Budget', 'Unlimited', 'Long Validity', 'OTT Bundled') DEFAULT 'Unlimited' AFTER is_best_plan");
        echo "Column 'category' added to 'plans' table.<br>";
    } catch (PDOException $e) {
        if ($e->getCode() == '42S21') {
            echo "Column 'category' already exists.<br>";
        } else {
            throw $e;
        }
    }

    // 2. Add 'ott_subscription' column to plans table
    try {
        $pdo->exec("ALTER TABLE plans ADD COLUMN ott_subscription VARCHAR(100) DEFAULT NULL AFTER category");
        echo "Column 'ott_subscription' added to 'plans' table.<br>";
    } catch (PDOException $e) {
        if ($e->getCode() == '42S21') {
            echo "Column 'ott_subscription' already exists.<br>";
        } else {
            throw $e;
        }
    }

    // 3. Add 'cost_per_day' generated column to plans table
    try {
        $pdo->exec("ALTER TABLE plans ADD COLUMN cost_per_day DECIMAL(10, 2) GENERATED ALWAYS AS (price / validity) STORED AFTER ott_subscription");
        echo "Column 'cost_per_day' added to 'plans' table.<br>";
    } catch (PDOException $e) {
        if ($e->getCode() == '42S21') {
            echo "Column 'cost_per_day' already exists.<br>";
        } else {
            throw $e;
        }
    }

    // 4. Update existing plans with categories to avoid empty results
    $pdo->exec("UPDATE plans SET category = 'Unlimited' WHERE category IS NULL");
    $pdo->exec("UPDATE plans SET category = 'Budget' WHERE price < 100");
    $pdo->exec("UPDATE plans SET category = 'Long Validity' WHERE validity >= 84");
    echo "Existing plans updated with categories.<br>";

    echo "<strong>Success!</strong> Your database schema is now up to date.<br>";
    echo "<a href='dashboard.php'>Go to Dashboard</a>";

} catch (PDOException $e) {
    echo "Error fixing database: " . $e->getMessage();
}
?>
