<?php
require 'db.php';
$hashed = password_hash('123456', PASSWORD_DEFAULT);
$count = $pdo->exec("UPDATE users SET password = '$hashed' WHERE password IS NULL OR password = ''");
echo "Updated $count users with the default password '123456'.";
?>
