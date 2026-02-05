<?php
require 'db.php';

try {
    // 1. Create generated_posters table
    $pdo->exec("CREATE TABLE IF NOT EXISTS generated_posters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        user_name VARCHAR(255) DEFAULT '',
        template_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'generated_posters' created/verified successfully.<br>";

    // 2. Add template_id if missing (optional step if we wanted foreign keys, but keep simple)
    
} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage();
}
?>
