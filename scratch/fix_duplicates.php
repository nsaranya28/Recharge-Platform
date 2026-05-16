<?php
require_once 'db.php';

try {
    // Delete duplicates keeping only the one with the smallest ID
    $sql = "DELETE p1 FROM plans p1
            INNER JOIN plans p2 
            WHERE p1.id > p2.id 
              AND p1.operator = p2.operator 
              AND p1.price = p2.price 
              AND p1.validity = p2.validity 
              AND p1.data_per_day = p2.data_per_day";
              
    $affected = $pdo->exec($sql);
    echo "Deleted $affected duplicate plans.\n";
    
    // Specifically for ₹15 plans as requested
    $stmt = $pdo->query("SELECT * FROM plans WHERE price = 15");
    $plans = $stmt->fetchAll();
    echo "Remaining ₹15 plans:\n";
    foreach ($plans as $plan) {
        echo "ID: {$plan['id']}, Operator: {$plan['operator']}, Price: {$plan['price']}\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
