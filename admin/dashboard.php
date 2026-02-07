<?php
require '../db.php';
require 'auth.php';
checkLogin();

$templates = $pdo->query("SELECT * FROM poster_templates ORDER BY id DESC")->fetchAll();

// Analytics: Daily (Last 30 Days)
$dailyStats = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM generated_posters 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
    GROUP BY DATE(created_at) 
    ORDER BY date ASC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$dates = [];
$dailyCounts = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('d M', strtotime($date));
    $dailyCounts[] = $dailyStats[$date] ?? 0;
}

// Analytics: Monthly (Current Year)
$monthlyStats = $pdo->query("
    SELECT MONTH(created_at) as month, COUNT(*) as count 
    FROM generated_posters 
    WHERE YEAR(created_at) = YEAR(CURDATE()) 
    GROUP BY MONTH(created_at) 
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_KEY_PAIR);

$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$monthlyCounts = [];
for ($i = 1; $i <= 12; $i++) {
    $monthlyCounts[] = $monthlyStats[$i] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow mb-8">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold">Poster Admin</h1>
            <a href="auth.php?logout=1" class="text-red-500 hover:text-red-700">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto px-6">
        
        <!-- Analytics Section -->
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Analytics</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <!-- Daily Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-bold mb-4 text-gray-700">Daily Creations (Last 30 Days)</h3>
                <canvas id="dailyChart"></canvas>
            </div>
            <!-- Monthly Chart -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-bold mb-4 text-gray-700">Monthly Creations (This Year)</h3>
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        
        <script>
            // Daily Chart
            const ctxDaily = document.getElementById('dailyChart').getContext('2d');
            new Chart(ctxDaily, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($dates); ?>,
                    datasets: [{
                        label: 'Posters Created',
                        data: <?php echo json_encode($dailyCounts); ?>,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });

            // Monthly Chart
            const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
            new Chart(ctxMonthly, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                        label: 'Posters Created',
                        data: <?php echo json_encode($monthlyCounts); ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    }]
                },
                options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        </script>

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
