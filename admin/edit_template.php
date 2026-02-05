<?php
require '../db.php';
require 'auth.php';
checkLogin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM poster_templates WHERE id = ?");
$stmt->execute([$id]);
$template = $stmt->fetch();

if (!$template) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Template</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow mb-8">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold">Edit Template</h1>
            <a href="dashboard.php" class="text-blue-500 hover:text-blue-700">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mx-auto px-6">
        <div class="bg-white rounded shadow p-6 mb-8">
            <!-- Success Message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row gap-8">
                <!-- Preview -->
                <div class="w-full md:w-1/2">
                    <h3 class="font-bold mb-4">Live Preview</h3>
                    <div class="border rounded bg-gray-100 p-2 relative overflow-hidden" id="preview-container">
                        <img src="../<?php echo $template['template_path']; ?>" class="w-full h-auto" id="template-img" onload="updatePreview()">
                        
                        <!-- Overlays -->
                        <div id="preview-image" class="absolute rounded-3xl bg-gray-400 border-2 border-dashed border-white opacity-70 flex items-center justify-center text-white text-xs text-center overflow-hidden">
                            <span>Photo</span>
                        </div>
                        <span id="preview-name" class="absolute font-bold text-right whitespace-nowrap opacity-80 border border-dashed border-blue-300 px-1" style="transform: translateX(-100%);">
                            User Name
                        </span>
                        <span id="preview-designation" class="absolute text-right whitespace-nowrap opacity-80 border border-dashed border-blue-300 px-1" style="transform: translateX(-100%);">
                            Designation
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">The overlays show the approximate position relative to the template.</p>
                </div>

                <!-- Form -->
                <div class="w-full md:w-1/2">
                    <form action="template_action.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_template">
                        <input type="hidden" name="id" value="<?php echo $template['id']; ?>">

                        <h2 class="text-lg font-bold mb-4 border-b pb-2">Templates Details</h2>
                        <div class="mb-6">
                            <label class="block text-sm font-bold mb-1">Template Title</label>
                            <input type="text" name="template_name" value="<?php echo htmlspecialchars($template['template_name']); ?>" class="w-full border p-2 rounded">
                        </div>
                        <!-- Canvas Settings (Auto-detected, Hidden) -->
                        <input type="hidden" name="canvas_width" id="canvas_width" value="<?php echo $template['canvas_width']; ?>">
                        <input type="hidden" name="canvas_height" id="canvas_height" value="<?php echo $template['canvas_height']; ?>">

                        <h2 class="text-lg font-bold mb-4 border-b pb-2">User Image</h2>
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-bold mb-1">X Position</label>
                                <input type="number" name="image_x" id="image_x" value="<?php echo $template['image_x']; ?>" class="w-full border p-2 rounded" title="Horizontal position (pixels from left)">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Y Position</label>
                                <input type="number" name="image_y" id="image_y" value="<?php echo $template['image_y']; ?>" class="w-full border p-2 rounded" title="Vertical position (pixels from top)">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Width</label>
                                <input type="number" name="image_width" id="image_width" value="<?php echo $template['image_width'] ?: $template['image_size']; ?>" class="w-full border p-2 rounded" title="Width in pixels">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Height</label>
                                <input type="number" name="image_height" id="image_height" value="<?php echo $template['image_height'] ?: $template['image_size']; ?>" class="w-full border p-2 rounded" title="Height in pixels">
                            </div>
                            <!-- Hidden Legacy Size -->
                            <input type="hidden" name="image_size" id="image_size" value="<?php echo $template['image_size']; ?>">
                        </div>

                        <h2 class="text-lg font-bold mb-4 border-b pb-2">Name Text</h2>
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-bold mb-1">X</label>
                                <input type="number" name="name_x" id="name_x" value="<?php echo $template['name_x']; ?>" class="w-full border p-2 rounded" title="Horizontal position (pixels from left)">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Y</label>
                                <input type="number" name="name_y" id="name_y" value="<?php echo $template['name_y']; ?>" class="w-full border p-2 rounded" title="Vertical position (pixels from top)">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Size</label>
                                <input type="number" name="name_font_size" id="name_font_size" value="<?php echo $template['name_font_size']; ?>" class="w-full border p-2 rounded" title="Font size in pixels">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Color</label>
                                <input type="color" name="name_color" id="name_color" value="<?php echo $template['name_color']; ?>" class="h-10 w-full rounded cursor-pointer" title="Text Color">
                            </div>
                        </div>

                        <h2 class="text-lg font-bold mb-4 border-b pb-2">Designation Text</h2>
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-bold mb-1">X</label>
                                <input type="number" name="designation_x" id="designation_x" value="<?php echo $template['designation_x']; ?>" class="w-full border p-2 rounded" title="Horizontal position (pixels from left)">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Y</label>
                                <input type="number" name="designation_y" id="designation_y" value="<?php echo $template['designation_y']; ?>" class="w-full border p-2 rounded" title="Vertical position (pixels from top)">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Size</label>
                                <input type="number" name="designation_font_size" id="designation_font_size" value="<?php echo $template['designation_font_size']; ?>" class="w-full border p-2 rounded" title="Font size in pixels">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Color</label>
                                <input type="color" name="designation_color" id="designation_color" value="<?php echo $template['designation_color']; ?>" class="h-10 w-full rounded cursor-pointer" title="Text Color">
                            </div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="dashboard.php" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Close</a>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updatePreview() {
             const img = document.getElementById('template-img');
             const naturalWidth = parseFloat(document.getElementById('canvas_width').value) || img.naturalWidth || 1080;
             const displayWidth = img.width;
             const scale = displayWidth / naturalWidth;

             // Elements
             const previewImage = document.getElementById('preview-image');
             const previewName = document.getElementById('preview-name');
             const previewDesignation = document.getElementById('preview-designation');

             // Inputs
             const imgX = parseFloat(document.getElementById('image_x').value) || 0;
             const imgY = parseFloat(document.getElementById('image_y').value) || 0;
             // Width/Height. Fallback to size if needed or defaults
             const imgW = parseFloat(document.getElementById('image_width').value) || 300;
             const imgH = parseFloat(document.getElementById('image_height').value) || 300;

             const nameX = parseFloat(document.getElementById('name_x').value) || 0;
             const nameY = parseFloat(document.getElementById('name_y').value) || 0;
             const nameSize = parseFloat(document.getElementById('name_font_size').value) || 0;
             const nameColor = document.getElementById('name_color').value;

             const desX = parseFloat(document.getElementById('designation_x').value) || 0;
             const desY = parseFloat(document.getElementById('designation_y').value) || 0;
             const desSize = parseFloat(document.getElementById('designation_font_size').value) || 0;
             const desColor = document.getElementById('designation_color').value;

             // Apply Styles (Scaled)
             
             // Image
             previewImage.style.left = (imgX * scale) + 'px';
             previewImage.style.top = (imgY * scale) + 'px';
             previewImage.style.width = (imgW * scale) + 'px';
             previewImage.style.height = (imgH * scale) + 'px';

             // Name
             // Note: Text aligns usually refer to center or left. 
             // Assuming coordinates are centered based on previous logic (drawCanvas uses ctx.textAlign = 'center' but coordinates might be top-left or center).
             // Let's assume the previous logic was using center for text?
             // Checking script.js snippet or assumptions... 
             // In script.js: ctx.fillText(name, canvas.width / 2, ...) -> It was centered horizontally on canvas!
             // BUT `template_action` stores specific X/Y. 
             // If the user can edit X, it implies it's not fixed to center.
             // Let's assume coordinates denote LEFT or CENTER?
             // Standard HTML absolute positioning is Left/Top. 
             // If the canvas drawing logic uses Center, we need to adjust.
             // Let's assume for now the user provides X=Left, Y=Top/Baseline.
             // Ideally we should sync with script.js drawing logic. 
             // But for now, simple absolute positioning is a good approximation.
             
             previewName.style.left = (nameX * scale) + 'px';
             previewName.style.top = (nameY * scale) + 'px';
             previewName.style.fontSize = (nameSize * scale) + 'px';
             previewName.style.color = nameColor;

             // Designation
             previewDesignation.style.left = (desX * scale) + 'px';
             previewDesignation.style.top = (desY * scale) + 'px';
             previewDesignation.style.fontSize = (desSize * scale) + 'px';
             previewDesignation.style.color = desColor;
        }

        // Listen for changes
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', updatePreview);
        });

        // Initialize on load and resize
        window.addEventListener('resize', updatePreview);
        // Also call periodically in case image loads late
        setTimeout(updatePreview, 500);
    </script>
</body>
</html>
