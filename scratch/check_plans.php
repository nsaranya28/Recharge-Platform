<?php
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT * FROM plans ORDER BY operator, price, validity");
    $plans = $stmt->fetchAll();
    
    echo "Current Plans in Database:\n";
    echo str_pad("ID", 5) . " | " . str_pad("Operator", 10) . " | " . str_pad("Price", 8) . " | " . str_pad("Validity", 10) . " | " . str_pad("Data/Day", 10) . " | " . str_pad("Category", 15) . "\n";
    echo str_repeat("-", 70) . "\n";
    
    foreach ($plans as $plan) {
        echo str_pad($plan['id'], 5) . " | " . 
             str_pad($plan['operator'], 10) . " | " . 
             str_pad($plan['price'], 8) . " | " . 
             str_pad($plan['validity'], 10) . " | " . 
             str_pad($plan['data_per_day'], 10) . " | " . 
             str_pad($plan['category'], 15) . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
