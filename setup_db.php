<?php
require 'db.php';

try {
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    echo "Database setup completed successfully (Tables created/verified).";
} catch (PDOException $e) {
    echo "Setup failed: " . $e->getMessage();
}
?>
