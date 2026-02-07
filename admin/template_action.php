<?php
require '../db.php';
require 'auth.php';
checkLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add_template') {
    if (isset($_FILES['template_image']) && $_FILES['template_image']['error'] === 0) {
        $uploadDir = '../uploads/templates/';
        $filename = time() . '_' . basename($_FILES['template_image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['template_image']['tmp_name'], $targetPath)) {
            // Auto-detect dimensions
            list($width, $height) = getimagesize($targetPath);

            $stmt = $pdo->prepare("INSERT INTO poster_templates (
                template_name, template_path, canvas_width, canvas_height, 
                image_x, image_y, image_size, image_width, image_height,
                name_x, name_y, name_font_size, name_color, 
                designation_x, designation_y, designation_font_size, designation_color
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $_POST['template_name'],
                'uploads/templates/' . $filename,
                $width, $height,
                100, 100, 300, 300, 300, // Default User Image (X, Y, Size, W, H)
                540, 900, 60, '#000000', // Default Name
                540, 980, 40, '#333333'  // Default Designation
            ]);

            // If it's the first template, activate it
            $lastId = $pdo->lastInsertId();
            $count = $pdo->query("SELECT COUNT(*) FROM poster_templates")->fetchColumn();
            if ($count == 1) {
                 $pdo->exec("UPDATE poster_templates SET is_active = 1 WHERE id = $lastId");
            }

            header("Location: dashboard.php?success=Template added");
        } else {
            header("Location: dashboard.php?error=Upload failed");
        }
    }
    exit;
}

if ($action === 'activate') {
    $id = $_GET['id'];
    $pdo->exec("UPDATE poster_templates SET is_active = 0");
    $stmt = $pdo->prepare("UPDATE poster_templates SET is_active = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: dashboard.php");
    exit;
}

if ($action === 'delete') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT template_path FROM poster_templates WHERE id = ?");
    $stmt->execute([$id]);
    $path = $stmt->fetchColumn();

    if ($path && file_exists('../' . $path)) {
        unlink('../' . $path);
    }

    $stmt = $pdo->prepare("DELETE FROM poster_templates WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: dashboard.php");
    exit;
}

if ($action === 'update_template') {
    $id = $_POST['id'];

    // 1. Handle Image Replacement if provided
    if (isset($_FILES['template_image']) && $_FILES['template_image']['error'] === 0) {
        $uploadDir = '../uploads/templates/';
        $filename = time() . '_' . basename($_FILES['template_image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['template_image']['tmp_name'], $targetPath)) {
            // Get new dimensions
            list($width, $height) = getimagesize($targetPath);

            // Fetch old image to delete
            $stmt = $pdo->prepare("SELECT template_path FROM poster_templates WHERE id = ?");
            $stmt->execute([$id]);
            $oldPath = $stmt->fetchColumn();
            if ($oldPath && file_exists('../' . $oldPath)) {
                @unlink('../' . $oldPath);
            }

            // Update Path & Dimensions
            $stmt = $pdo->prepare("UPDATE poster_templates SET template_path = ?, canvas_width = ?, canvas_height = ? WHERE id = ?");
            $stmt->execute(['uploads/templates/' . $filename, $width, $height, $id]);
        }
    }

    // 2. Update config fields
    $stmt = $pdo->prepare("UPDATE poster_templates SET 
        template_name = ?,
        image_x = ?, image_y = ?, image_size = ?, image_width = ?, image_height = ?,
        name_x = ?, name_y = ?, name_font_size = ?, name_color = ?,
        designation_x = ?, designation_y = ?, designation_font_size = ?, designation_color = ?
        WHERE id = ?");

    $stmt->execute([
        $_POST['template_name'],
        $_POST['image_x'], $_POST['image_y'], $_POST['image_size'], $_POST['image_width'], $_POST['image_height'],
        $_POST['name_x'], $_POST['name_y'], $_POST['name_font_size'], $_POST['name_color'],
        $_POST['designation_x'], $_POST['designation_y'], $_POST['designation_font_size'], $_POST['designation_color'],
        $id
    ]);

    header("Location: edit_template.php?id=$id&success=Template updated");
    exit;
}
?>
