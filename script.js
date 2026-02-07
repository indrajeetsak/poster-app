let activeTemplate = null;
let processedImageBitmap = null;
let preloadedBgImg = null;
let posterUploadPromise = null;

// Initialize
document.addEventListener('DOMContentLoaded', async () => {
    console.log("Script loaded and DOM ready");
    try {
        const response = await fetch('api.php?action=get_active_template');
        const result = await response.json();

        if (result.status === 'success') {
            activeTemplate = result.data;
            console.log("Template loaded:", activeTemplate);
            // Preload the background image immediately
            preloadTemplateImage(activeTemplate.template_path);
        } else {
            console.warn("No active template found.");
            document.getElementById('inputParams').innerHTML = `
                <div class="text-center p-8 text-red-600 bg-red-50 rounded-lg">
                    <h3 class="font-bold text-lg mb-2">System Not Ready</h3>
                    <p>No poster template has been configured yet.</p>
                    <p class="text-sm mt-2 text-gray-600">Please ask the administrator to upload a template in the admin panel.</p>
                </div>
            `;
        }
    } catch (e) {
        console.error("Error loading template:", e);
    }
});

function preloadTemplateImage(url) {
    preloadedBgImg = new Image();
    preloadedBgImg.crossOrigin = "anonymous";
    preloadedBgImg.src = url;
    console.log("Started preloading template:", url);
}

// Expose functions to global scope
window.generatePoster = async function () {
    console.log("Generate Poster Clicked");

    const name = document.getElementById('userName').value.trim();
    const designation = document.getElementById('userDesignation').value.trim();
    const photoInput = document.getElementById('userPhoto');

    if (!activeTemplate) {
        alert("Template data not loaded yet. Please refresh or contact admin.");
        return;
    }

    if (!name) {
        alert("Please enter your name.");
        return;
    }
    if (photoInput.files.length === 0) {
        alert("Please upload a photo.");
        return;
    }

    // Show Loader
    const loader = document.getElementById('loader');
    const inputParams = document.getElementById('inputParams');
    const resultArea = document.getElementById('resultArea');
    const loaderText = document.getElementById('loaderText');

    loader.classList.remove('hidden');
    inputParams.classList.add('hidden');
    resultArea.classList.add('hidden');

    // Reset previous upload promise
    posterUploadPromise = null;

    try {
        // 1. Resize Image for Performant Canvas Handling
        loaderText.innerText = "Processing image...";
        const file = photoInput.files[0];
        let resizedBlob = file;
        try {
            // Resize to max 800x800 for better performance without losing too much quality on screen
            resizedBlob = await resizeImage(file, 800, 800);
        } catch (e) {
            console.warn("Resize failed, using original", e);
        }

        const bitmap = await createImageBitmap(resizedBlob);
        processedImageBitmap = bitmap;

        // 2. Draw Canvas
        loaderText.innerText = "Composing poster...";
        await drawCanvas(name, designation, processedImageBitmap);

        // 3. Start Background Upload immediately
        startBackgroundUpload();

        // Show Result
        loader.classList.add('hidden');
        resultArea.classList.remove('hidden');

    } catch (error) {
        console.error("Generation failed:", error);
        alert("An error occurred: " + error.message + "\nCheck console for details.");

        // Reset UI
        loader.classList.add('hidden');
        inputParams.classList.remove('hidden');
    }
};

// Helper: Get resized data URL if canvas is too large
function getOutputDataUrl(canvas, quality = 0.92) {
    const MAX_OUTPUT_WIDTH = 1080;

    if (canvas.width > MAX_OUTPUT_WIDTH) {
        const scale = MAX_OUTPUT_WIDTH / canvas.width;
        const newWidth = MAX_OUTPUT_WIDTH;
        const newHeight = Math.round(canvas.height * scale);

        const tmpCanvas = document.createElement('canvas');
        tmpCanvas.width = newWidth;
        tmpCanvas.height = newHeight;
        const ctx = tmpCanvas.getContext('2d');

        // Use standard quality scaling
        ctx.drawImage(canvas, 0, 0, newWidth, newHeight);
        return tmpCanvas.toDataURL('image/jpeg', quality);
    }

    return canvas.toDataURL('image/jpeg', quality);
}

function startBackgroundUpload() {
    const canvas = document.getElementById('posterCanvas');
    const name = document.getElementById('userName').value.trim();

    // Generate optimized data URL
    const dataUrl = getOutputDataUrl(canvas);

    posterUploadPromise = (async () => {
        try {
            const response = await fetch('api.php?action=upload_generated', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    image: dataUrl,
                    user_name: name,
                    template_id: activeTemplate.id
                })
            });
            return await response.json();
        } catch (e) {
            console.error("Background upload failed:", e);
            throw e;
        }
    })();
}

function resizeImage(file, maxWidth, maxHeight) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > maxWidth) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width = Math.round((width * maxHeight) / height);
                        height = maxHeight;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    resolve(blob);
                }, file.type);
            };
            img.onerror = reject;
            img.src = e.target.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

function drawCanvas(name, designation, userImage) {
    return new Promise((resolve, reject) => {
        const canvas = document.getElementById('posterCanvas');
        const ctx = canvas.getContext('2d');

        // Use preloaded image if available
        let bgImg = preloadedBgImg;
        if (!bgImg) {
            // Fallback if somehow execution happens before init
            bgImg = new Image();
            bgImg.src = activeTemplate.template_path;
            bgImg.crossOrigin = "anonymous";
        }

        const render = () => {
            // 1. Set Canvas to match Image Dimensions exactly
            canvas.width = bgImg.naturalWidth;
            canvas.height = bgImg.naturalHeight;

            // Draw Background
            ctx.drawImage(bgImg, 0, 0, canvas.width, canvas.height);

            // 2. Draw User Image (Rounded Rectangle)
            const imgX = parseInt(activeTemplate.image_x);
            const imgY = parseInt(activeTemplate.image_y);
            // Fallback for older templates still using 'image_size' or if specific W/H absent
            const imgW = parseInt(activeTemplate.image_width) || parseInt(activeTemplate.image_size) || 300;
            const imgH = parseInt(activeTemplate.image_height) || parseInt(activeTemplate.image_size) || 300;

            ctx.save();
            ctx.beginPath();

            // Change to Rounded Rectangle
            const radius = Math.min(imgW, imgH) * 0.15; // 15% corner radius of smaller dimension
            ctx.roundRect(imgX, imgY, imgW, imgH, radius);
            ctx.closePath();
            ctx.clip();

            // Draw the user image into the clipped area with object-fit: cover logic
            // (Center and scale to fill)
            const aspect = userImage.width / userImage.height;
            const targetAspect = imgW / imgH;

            let drawWidth, drawHeight, dx, dy;

            if (aspect > targetAspect) {
                // Image is wider than target box (relative to aspect)
                drawHeight = imgH;
                drawWidth = imgH * aspect;
                dx = imgX - (drawWidth - imgW) / 2;
                dy = imgY;
            } else {
                // Image is taller than target box
                drawWidth = imgW;
                drawHeight = imgW / aspect;
                dx = imgX;
                dy = imgY - (drawHeight - imgH) / 2;
            }

            ctx.drawImage(userImage, dx, dy, drawWidth, drawHeight);
            ctx.restore();

            // 3. Draw Name
            const nameSize = parseInt(activeTemplate.name_font_size);
            const nameColor = activeTemplate.name_color;
            ctx.font = `bold ${nameSize}px 'Khand', sans-serif`;
            ctx.fillStyle = nameColor;
            ctx.textAlign = 'right'; // Right aligned
            ctx.textBaseline = 'top';
            ctx.fillText(name, parseInt(activeTemplate.name_x), parseInt(activeTemplate.name_y));

            // 4. Draw Designation
            if (designation) {
                const desSize = parseInt(activeTemplate.designation_font_size);
                const desColor = activeTemplate.designation_color;
                ctx.font = `${desSize}px 'Khand', sans-serif`;
                ctx.fillStyle = desColor;
                ctx.textAlign = 'right'; // Right aligned
                ctx.textBaseline = 'top';
                ctx.fillText(designation, parseInt(activeTemplate.designation_x), parseInt(activeTemplate.designation_y));
            }

            resolve();
        };

        if (bgImg.complete && bgImg.naturalWidth !== 0) {
            render();
        } else {
            bgImg.onload = render;
            bgImg.onerror = (e) => {
                console.error("BG Image Load Error", e);
                reject(new Error("Failed to load template background image."));
            };
        }
    });
}

window.downloadPoster = function () {
    const canvas = document.getElementById('posterCanvas');
    const link = document.createElement('a');
    link.download = `poster_${Date.now()}.jpg`;
    link.href = getOutputDataUrl(canvas);
    link.click();
};

window.shareOnWhatsapp = async function () {
    if (!posterUploadPromise) {
        alert("Please generate a poster first.");
        return;
    }

    const shareBtn = document.querySelector('button[onclick="shareOnWhatsapp()"]');
    const originalText = shareBtn.innerText;
    shareBtn.innerText = "Preparing...";
    shareBtn.disabled = true;

    try {
        const result = await posterUploadPromise;

        if (result.status === 'success') {
            const filename = result.filename;
            const baseUrl = window.location.href.substring(0, window.location.href.lastIndexOf('/'));
            const shareLink = `${baseUrl}/view.php?id=${filename}`;
            const name = document.getElementById('userName').value.trim();
            const templateTitle = activeTemplate.template_name || 'Holi';

            const message = `*${name}* ने आपको *${templateTitle}* भेज है. देखने के लिए क्लिक करें 👉 ${shareLink}`;

            // 1. Try Native Share (Best for Mobile)
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: templateTitle,
                        text: message,
                        // url: shareLink // Optional: WhatsApp often prefers text concatenation
                    });
                    return; // Success, stop here
                } catch (err) {
                    console.log("Navigator share failed or cancelled, falling back.");
                }
            }

            // 2. Fallback: Direct Intent (Mobile)
            // Using window.location.href avoids opening a blank tab
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

            if (isMobile) {
                window.location.href = `whatsapp://send?text=${encodeURIComponent(message)}`;
            } else {
                // Desktop fallback
                window.open(`https://web.whatsapp.com/send?text=${encodeURIComponent(message)}`, '_blank');
            }

        } else {
            alert('Failed to prepare poster link.');
        }
    } catch (e) {
        console.error(e);
        alert('Error sharing poster. Please try again.');
    } finally {
        shareBtn.innerText = originalText;
        shareBtn.disabled = false;
    }
};
