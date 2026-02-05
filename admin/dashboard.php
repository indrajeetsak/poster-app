<?php
require '../db.php';
require 'auth.php';
checkLogin();

$templates = $pdo->query("SELECT * FROM poster_templates ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow mb-8">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold">Poster Admin</h1>
            <a href="auth.php?logout=1" class="text-red-500 hover:text-red-700">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto px-6">
        <!-- Add Template Form -->
        <div class="bg-white rounded shadow p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Add New Template</h2>
            <form action="template_action.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_template">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold mb-2">Template Title</label>
                        <input type="text" name="template_name" class="w-full border p-2 rounded" placeholder="e.g. Happy Holi 2026" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2">Template Image</label>
                        <input type="file" name="template_image" class="w-full border p-2 rounded" required accept="image/*">
                    </div>
                </div>



                <div class="mt-6 text-right">
                    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">Save Template</button>
                </div>
            </form>
        </div>

        <!-- Existing Templates -->
        <h2 class="text-xl font-bold mb-4">Existing Templates</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <?php foreach ($templates as $tpl): ?>
                <div class="bg-white rounded h-64 shadow flow-root p-4 <?php echo $tpl['is_active'] ? 'ring-2 ring-blue-500' : ''; ?>">
                    <div class="w-4/5 mx-auto h-32 bg-gray-100 mb-4 rounded flex items-center justify-center p-2 border">
                        <img src="../<?php echo $tpl['template_path']; ?>" class="w-auto h-full max-h-full max-w-full object-contain">
                    </div>
                    <div class="flex flex-col justify-between items-start h-20">
                        <h3 class="font-bold text-gray-800 truncate w-full mb-2" title="<?php echo htmlspecialchars($tpl['template_name']); ?>"><?php echo htmlspecialchars($tpl['template_name']); ?></h3>
                        <div class="flex justify-between items-center w-full">
                            <?php if ($tpl['is_active']): ?>
                            <span class="text-blue-500 font-bold text-sm">Active</span>
                        <?php else: ?>
                            <a href="template_action.php?action=activate&id=<?php echo $tpl['id']; ?>" class="text-gray-500 hover:text-blue-500 text-sm">Activate</a>
                        <?php endif; ?>
                        <div class="flex gap-3">
                            <a href="edit_template.php?id=<?php echo $tpl['id']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm font-semibold transition">Edit</a>
                            <a href="template_action.php?action=delete&id=<?php echo $tpl['id']; ?>" class="text-red-500 hover:text-red-700 text-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
