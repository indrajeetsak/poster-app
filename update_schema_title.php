<?php
require 'db.php';

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM poster_templates LIKE 'template_name'");
    $exists = $stmt->fetch();

    if (!$exists) {
        // Add column
        $pdo->exec("ALTER TABLE poster_templates ADD COLUMN template_name VARCHAR(255) DEFAULT 'Untitled Template' AFTER id");
        echo "Schema updated successfully: Added template_name.";
    } else {
        echo "Schema already updated.";
    }

} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage();
}
?>
