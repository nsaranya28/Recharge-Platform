<?php
require_once 'db.php';

try {
    // Check if Jio 1-day plan already exists to avoid duplicates
    $checkJio = $pdo->prepare("SELECT id FROM plans WHERE operator = 'Jio' AND validity = 1 AND price = 15.00");
    $checkJio->execute();
    
    if (!$checkJio->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO plans (operator, price, validity, data_per_day, is_best_plan, category) VALUES ('Jio', 15.00, 1, 1.0, FALSE, 'Budget')");
        $stmt->execute();
        echo "Jio 1-day plan (₹15) added.<br>";
    } else {
        echo "Jio 1-day plan already exists.<br>";
    }

    // Check if Airtel 1-day plan already exists
    $checkAirtel = $pdo->prepare("SELECT id FROM plans WHERE operator = 'Airtel' AND validity = 1 AND price = 15.00");
    $checkAirtel->execute();
    
    if (!$checkAirtel->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO plans (operator, price, validity, data_per_day, is_best_plan, category) VALUES ('Airtel', 15.00, 1, 1.0, FALSE, 'Budget')");
        $stmt->execute();
        echo "Airtel 1-day plan (₹15) added.<br>";
    } else {
        echo "Airtel 1-day plan already exists.<br>";
    }

    echo "<strong>Success!</strong> You can now go to <a href='index.php'>index.php</a> to see the new plans.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
