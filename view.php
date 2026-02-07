<?php
require 'db.php';
$id = $_GET['id'] ?? '';
$imageUrl = '';
$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']);
$pageTitle = "My Poster";

if ($id) {
    // Sanitize ID to prevent directory traversal
    $id = basename($id);
    if (file_exists("uploads/generated/$id")) {
        $imageUrl = "$siteUrl/uploads/generated/$id";

        // Fetch Metadata
        try {
            $stmt = $pdo->prepare("
                SELECT g.user_name, t.template_name 
                FROM generated_posters g
                LEFT JOIN poster_templates t ON g.template_id = t.id
                WHERE g.filename = ?
            ");
            $stmt->execute([$id]);
            $meta = $stmt->fetch();

            if ($meta) {
                $uName = htmlspecialchars($meta['user_name'] ?: 'User');
                $tName = htmlspecialchars($meta['template_name'] ?: 'Template');
                $pageTitle = "$uName की ओर से $tName";
            }
        } catch (PDOException $e) {
            // Silently fail to default title
        }
    }
}
// ... fallback image logic ...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Open Graph Tags for WhatsApp -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>" />
    <meta property="og:description" content="Click here to create yours now." />
    <!-- ... -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">
    
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full overflow-hidden text-center p-6">
        <h3 class="text-xl font-bold mb-6 text-gray-800"><?php echo $pageTitle; ?></h1>
        
        <?php if ($imageUrl): ?>
            <img src="<?php echo $imageUrl; ?>" alt="Generated Poster" class="w-full h-auto max-w-full rounded-lg shadow-md mb-8">
        <?php else: ?>
            <p class="text-red-500 mb-4">Poster not found.</p>
        <?php endif; ?>

        <a href="index.php" class="block w-full animate-bounce bg-gradient-to-r from-blue-500 to-purple-600 text-white text-xl font-bold py-5 px-8 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-200">
            Create Yours Now ✨
        </a>
    </div>

</body>
</html>
