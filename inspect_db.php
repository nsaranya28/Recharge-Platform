<?php
require 'db.php';
$stmt = $pdo->query("SHOW CREATE TABLE recharge_history");
$row = $stmt->fetch();
print_r($row);
echo "\n\nIndexes:\n";
$stmt2 = $pdo->query("SHOW INDEXES FROM recharge_history");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
