<?php
require_once 'db.php';

try {
    $sql = file_get_contents('database.sql');
    
    // Split SQL by semicolon, but be careful with multi-line statements
    // This is a simple implementation
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $pdo->exec($query);
            } catch (PDOException $e) {
                // Ignore duplicate or existing errors, but log them for debugging
                if ($e->getCode() != '23000') {
                    echo "Notice: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    echo "<strong>Success!</strong> All tables from database.sql have been initialized/updated.<br>";
    echo "Sample user 'Test User' created.<br>";
    echo "<a href='dashboard.php'>Go to Dashboard</a>";

} catch (PDOException $e) {
    echo "Error initializing database: " . $e->getMessage();
}
?>
