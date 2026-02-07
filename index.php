<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poster Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="max-w-md mx-auto bg-white min-h-screen shadow-2xl relative">
        <!-- Header -->
        <header class="bg-blue-600 text-white p-4 text-center">
            <h1 class="text-xl font-bold">Poster Generator</h1>
        </header>

        <div class="p-6 pb-24">
            <!-- Form -->
            <div id="inputParams" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Enter Name</label>
                    <input type="text" id="userName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-3" placeholder="Your Name">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Enter Designation (Optional)</label>
                    <input type="text" id="userDesignation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-3" placeholder="e.g. Graphic Designer">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Upload Photo</label>
                    <input type="file" id="userPhoto" accept="image/*" class="mt-1 block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-sm file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100
                    ">

                </div>

                <button onclick="generatePoster()" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg shadow hover:bg-blue-700 transition">
                    Generate Poster
                </button>
            </div>

            <!-- Loader -->
            <div id="loader" class="hidden flex flex-col items-center justify-center py-10">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p id="loaderText" class="mt-3 text-gray-600 text-sm">Initializing background remover... (this may take a moment)</p>
            </div>

            <!-- Preview -->
            <div id="resultArea" class="hidden mt-6 text-center">
                <h2 class="text-lg font-bold mb-2">Preview</h2>
                <div class="relative w-full bg-gray-200 rounded-lg overflow-hidden shadow-lg border">
                    <canvas id="posterCanvas" class="w-full h-auto max-w-full block"></canvas>
                </div>
                
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <button onclick="shareOnWhatsapp()" class="col-span-2 bg-green-500 text-white font-bold py-3 rounded-lg flex items-center justify-center gap-2 hover:bg-green-600">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        Share on WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Imgly BG Removal (Loaded via Module in script.js) -->
    <script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
