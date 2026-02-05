<?php
require 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_active_template') {
    try {
        $stmt = $pdo->query("SELECT * FROM poster_templates WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        $template = $stmt->fetch();
        
        if ($template) {
            echo json_encode(['status' => 'success', 'data' => $template]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No active template found']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'upload_generated') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $imageData = $data['image'] ?? '';

    if (!$imageData) {
        echo json_encode(['status' => 'error', 'message' => 'No image data provided']);
        exit;
    }

    // Remove header definition (e.g., "data:image/jpeg;base64,")
    $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);
    $imageBytes = base64_decode($imageData);

    $filename = 'poster_' . time() . '_' . uniqid() . '.jpg';
    $filepath = 'uploads/generated/' . $filename;

    if (file_put_contents($filepath, $imageBytes)) {
        // Save metadata to DB
        $userName = $data['user_name'] ?? '';
        $templateId = $data['template_id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO generated_posters (filename, user_name, template_id) VALUES (?, ?, ?)");
        $stmt->execute([$filename, $userName, $templateId]);

        echo json_encode(['status' => 'success', 'filename' => $filename]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save file']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>
