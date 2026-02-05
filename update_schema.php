<?php
require 'db.php';

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM poster_templates LIKE 'image_width'");
    $exists = $stmt->fetch();

    if (!$exists) {
        // Add columns
        $pdo->exec("ALTER TABLE poster_templates ADD COLUMN image_width INT DEFAULT 300 AFTER image_size");
        $pdo->exec("ALTER TABLE poster_templates ADD COLUMN image_height INT DEFAULT 300 AFTER image_width");

        // Migrate data (copy size to width/height)
        $pdo->exec("UPDATE poster_templates SET image_width = image_size, image_height = image_size");

        echo "Schema updated successfully: Added image_width and image_height.";
    } else {
        echo "Schema already updated.";
    }

} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage();
}
?>
