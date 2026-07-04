document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const mediaModal = new bootstrap.Modal(document.getElementById('mediaModal'));
    const openCameraBtn = document.getElementById('openCamera');
    const captureBtn = document.getElementById('captureButton');
    const closeBtn = document.querySelector('#mediaModal .btn-close');
    const video = document.getElementById('mediaFeed');
    const canvas = document.getElementById('photoCanvas');
    const previewContainer = document.getElementById('mediaPreview');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    
    let stream = null;
    let currentPosition = null;
    
    // Open camera when button is clicked
    openCameraBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' }, 
                audio: false 
            });
            video.srcObject = stream;
            mediaModal.show();
        } catch (err) {
            console.error('Error accessing camera:', err);
            alert('Unable to access camera. Please make sure you have granted camera permissions.');
        }
    });

    // Function to update location inputs
    function updateLocationInputs() {
        if (currentPosition) {
            const { latitude, longitude } = currentPosition;
            latitudeInput.value = latitude.toFixed(6);
            longitudeInput.value = longitude.toFixed(6);
            latitudeInput.dispatchEvent(new Event('input'));
            longitudeInput.dispatchEvent(new Event('input'));
            
            [latitudeInput, longitudeInput].forEach(input => {
                input.classList.add('bg-light');
                const indicator = document.createElement('small');
                indicator.className = 'text-success d-block';
                indicator.innerHTML = '<i class="fas fa-check-circle"></i> Location captured';
                input.parentNode.appendChild(indicator);
                setTimeout(() => indicator.remove(), 3000);
            });
        }
    }

    // Capture photo when capture button is clicked
    captureBtn.addEventListener('click', async () => {
        // Set canvas dimensions to match video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video frame to canvas
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convert canvas to blob
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg'));
        
        // Get location if available
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition((position) => {
                currentPosition = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                updateLocationInputs();
                addPhotoPreview(blob, currentPosition.latitude, currentPosition.longitude);
            }, (error) => {
                console.error('Error getting location:', error);
                alert('Unable to get location. Please ensure location services are enabled.');
                addPhotoPreview(blob, null, null);
            });
        } else {
            addPhotoPreview(blob, null, null);
        }

        // Close modal and stop camera
        mediaModal.hide();
    });

    function addPhotoPreview(blob, latitude, longitude) {
        const preview = document.createElement('div');
        preview.className = 'position-relative';
        preview.innerHTML = `
            <img src="${URL.createObjectURL(blob)}" class="img-thumbnail" style="height: 100px; object-fit: cover;">
            <div class="position-absolute top-0 end-0 m-1 d-flex gap-1">
                <button type="button" class="btn btn-danger btn-sm delete-preview">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <input type="hidden" name="photos[]" value="${canvas.toDataURL('image/jpeg')}">
            <input type="hidden" name="photos_latitude[]" value="${latitude !== null ? latitude.toFixed(6) : ''}">
            <input type="hidden" name="photos_longitude[]" value="${longitude !== null ? longitude.toFixed(6) : ''}">
        `;

        // Add delete functionality
        preview.querySelector('.delete-preview').addEventListener('click', () => {
            preview.remove();
        });

        // Add to preview container
        previewContainer.appendChild(preview);
    }

    // Clean up when modal is closed
    closeBtn.addEventListener('click', stopCamera);
    mediaModal._element.addEventListener('hidden.bs.modal', stopCamera);

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
    }

    // Log form submission and errors for debugging
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(event) {
            console.log('Form submitted');
        });
    }
    window.addEventListener('error', function(event) {
        console.error('JavaScript error:', event.error);
    });
});
