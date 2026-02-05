let activeTemplate = null;
let processedImageBitmap = null;
let preloadedBgImg = null;

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

// Helper to load script dynamically


let posterUploadPromise = null;

// ... (existing code) ...

// Expose functions to global scope
window.generatePoster = async function () {
    // ... (existing helper vars) ...
    // ... (existing validation) ...

    // Reset previous upload promise
    posterUploadPromise = null;

    // ... (existing loader logic) ...

    try {
        // ... (existing resize and bitmap logic) ...

        // 2. Draw Canvas
        loaderText.innerText = "Composing poster...";
        await drawCanvas(name, designation, processedImageBitmap);

        // 3. Start Background Upload immediately
        startBackgroundUpload();

        // Show Result
        loader.classList.add('hidden');
        resultArea.classList.remove('hidden');

    } catch (error) {
        // ... (existing error handling) ...
    }
};

function startBackgroundUpload() {
    const canvas = document.getElementById('posterCanvas');
    const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
    const name = document.getElementById('userName').value.trim();

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

// ... (resizeImage and drawCanvas functions remain same) ...

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

            const message = `${name} ने आपको ${templateTitle} भेजा है. देखने के लिए क्लिक करें 👉 ${shareLink}`;

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
