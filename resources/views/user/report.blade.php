@extends('layouts.mobile-user')
@section('title', 'Report Incident | EcoConnect - DENR CENRO Sanchez Mira')
@section('page-title', 'Report Environmental Incident')
@section('content')
    <section id="report" class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <!-- Header -->
                    <div class="mb-4 text-center">
                        <h2 class="mb-2">Report Environmental Incident</h2>
                        <p class="mb-0 text-muted">Help protect our environment by reporting incidents</p>
                    </div>

                    <!-- Incident Form -->
                    <div class="border-0 shadow-sm card">
                        <div class="p-3 card-body">
                            <form method="POST" action="{{ route('incidents.store') }}" enctype="multipart/form-data" id="reportForm">
                                @csrf

                                <!-- Incident Type Cards -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Incident Type</label>
                                    <div class="row g-2" id="incidentTypeCards">
                                        <div class="col-6">
                                            <div class="card incident-type-card" data-value="Illegal Logging">
                                                <div class="p-3 text-center card-body">
                                                    <div class="mb-2">
                                                        <i class="fas fa-tree fa-2x text-success"></i>
                                                    </div>
                                                    <span class="fw-semibold small">Illegal Logging</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="card incident-type-card" data-value="Pollution">
                                                <div class="p-3 text-center card-body">
                                                    <div class="mb-2">
                                                        <i class="fas fa-smog fa-2x text-warning"></i>
                                                    </div>
                                                    <span class="fw-semibold small">Pollution</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="card incident-type-card" data-value="Wildlife Crime">
                                                <div class="p-3 text-center card-body">
                                                    <div class="mb-2">
                                                        <i class="fas fa-paw fa-2x text-danger"></i>
                                                    </div>
                                                    <span class="fw-semibold small">Wildlife Crime</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="card incident-type-card" data-value="Illegal Waste Disposal">
                                                <div class="p-3 text-center card-body">
                                                    <div class="mb-2">
                                                        <i class="fas fa-trash fa-2x text-secondary"></i>
                                                    </div>
                                                    <span class="fw-semibold small">Illegal Waste</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="card incident-type-card" data-value="Other">
                                                <div class="p-3 text-center card-body">
                                                    <div class="mb-2">
                                                        <i class="fas fa-exclamation-triangle fa-2x text-info"></i>
                                                    </div>
                                                    <span class="fw-semibold">Other Incident</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="incident_type" id="selectedIncidentType" required>
                                    <div class="mt-2 text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-hand-pointer me-1"></i>
                                            Tap to select incident type
                                        </small>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Description</label>
                                    <textarea class="form-control" name="description" rows="4"
                                              placeholder="Describe what you observed..."
                                              style="min-height: 120px;"
                                              required></textarea>
                                </div>

                                <!-- Media Evidence -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Media Evidence</label>

                                    <!-- Camera Buttons -->
                                    <div class="mb-3 row g-2">
                                        <div class="col-6">
                                            <button type="button" class="py-3 btn btn-outline-success w-100" id="openCamera">
                                                <i class="fas fa-camera me-2"></i>Photo
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="py-3 btn btn-outline-primary w-100" id="openVideoCamera">
                                                <i class="fas fa-video me-2"></i>Video
                                            </button>
                                        </div>
                                    </div>

                                    <!-- File Upload -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small">Or upload files:</label>
                                        <input type="file" class="form-control" id="fileUpload" accept="image/*,video/*" multiple>
                                    </div>

                                    <!-- Media Preview -->
                                    <div id="mediaPreview" class="flex-wrap gap-2 d-flex"></div>

                                    <!-- Camera Modal -->
                                    <div class="modal fade" id="mediaModal" tabindex="-1">
                                        <div class="modal-dialog modal-fullscreen">
                                            <div class="modal-content">
                                                <div class="text-white modal-header bg-dark">
                                                    <h6 class="modal-title" id="modalTitle">Take Photo</h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="p-0 modal-body d-flex flex-column">
                                                    <div class="flex-grow-1 position-relative">
                                                        <video id="mediaFeed" class="w-100 h-100" autoplay playsinline
                                                               style="object-fit: cover;"></video>
                                                        <canvas id="photoCanvas" class="d-none"></canvas>
                                                    </div>
                                                    <div id="videoControls" class="p-3 d-none bg-dark">
                                                        <div class="text-center">
                                                            <button type="button" class="p-3 btn btn-danger btn-lg rounded-circle" id="startRecording">
                                                                <i class="fas fa-circle fa-lg"></i>
                                                            </button>
                                                            <button type="button" class="p-3 btn btn-secondary btn-lg rounded-circle d-none" id="stopRecording">
                                                                <i class="fas fa-square fa-lg"></i>
                                                            </button>
                                                            <div class="mt-2">
                                                                <span id="recordingTimer" class="text-white fw-bold d-none">00:00</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-dark">
                                                    <div class="w-100">
                                                        <button type="button" class="mb-2 btn btn-secondary w-100" data-bs-dismiss="modal">
                                                            Cancel
                                                        </button>
                                                        <button type="button" class="btn btn-success w-100 d-none" id="captureButton">
                                                            <i class="fas fa-camera me-2"></i>Capture Photo
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Location</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="fas fa-map-marker-alt text-muted"></i>
                                                </span>
                                                <input type="number"
                                                       class="form-control"
                                                       name="latitude"
                                                       id="latitude"
                                                       placeholder="Latitude"
                                                       step="any"
                                                       required
                                                       readonly>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="fas fa-map-marker-alt text-muted"></i>
                                                </span>
                                                <input type="number"
                                                       class="form-control"
                                                       name="longitude"
                                                       id="longitude"
                                                       placeholder="Longitude"
                                                       step="any"
                                                       required
                                                       readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Map Container -->
                                    <div id="mapContainer" class="mb-3" style="border-radius: 8px; overflow: hidden; border: 2px solid #e9ecef;">
                                        <div id="incidentMap" style="height: 400px; width: 100%;"></div>
                                    </div>

                                    <small class="mt-1 text-muted d-block">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Click on the map to select a location or coordinates will auto-fill when capturing media
                                    </small>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="py-3 btn btn-success w-100 fw-semibold">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Report
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Global Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="border-0 modal-header">
                    <h6 class="text-white modal-title" id="imagePreviewTitle"></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center">
                    <img id="previewImage" src="" class="img-fluid" style="max-height: 80vh; object-fit: contain;">
                </div>
                <div class="border-0 modal-footer justify-content-center">
                    <a id="downloadImage" href="#" class="btn btn-primary" download>
                        <i class="fas fa-download me-2"></i>Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Processing Modal -->
    <div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="border-0 shadow modal-content">
                <div class="p-4 text-center modal-body">
                    <div class="mb-3">
                        <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Processing...</span>
                        </div>
                    </div>
                    <h5 class="mb-2" id="processingTitle">Processing Media</h5>
                    <p class="mb-0 text-muted" id="processingMessage">Please wait while we process your media and get your location...</p>
                    <div class="mt-3 progress" style="height: 4px;">
                        <div id="processingProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                             role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet Map Library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>

    <style>
        /* Mobile-first responsive design */
        @media (max-width: 768px) {
            .container {
                padding-left: 12px;
                padding-right: 12px;
            }

            .card-body {
                padding: 1rem !important;
            }

            h2 {
                font-size: 1.5rem;
            }

            .btn {
                font-size: 0.9rem;
            }

            .form-select-lg {
                font-size: 1rem;
                padding: 0.75rem 1rem;
            }

            .form-control {
                font-size: 1rem;
                padding: 0.75rem 1rem;
            }

            textarea.form-control {
                min-height: 100px;
            }
        }

        /* Improved touch targets */
        .btn, .form-control, .form-select {
            min-height: 48px;
        }

        /* Better modal for mobile */
        .modal-fullscreen {
            padding: 0 !important;
        }

        /* Media preview styling */
        .media-preview-item {
            position: relative;
            width: 80px;
            height: 80px;
        }

        .media-preview-item img,
        .media-preview-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .delete-preview {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        /* Incident Type Cards Styling */
        .incident-type-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #e9ecef;
            min-height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .incident-type-card:hover {
            border-color: #0d6efd;
            transform: translateY(-2px);
        }

        .incident-type-card.active {
            border-color: #198754;
            background-color: rgba(25, 135, 84, 0.05);
            box-shadow: 0 4px 8px rgba(25, 135, 84, 0.2);
        }

        .incident-type-card .card-body {
            padding: 0.75rem !important;
        }

        .incident-type-card i {
            transition: transform 0.3s ease;
        }

        .incident-type-card.active i {
            transform: scale(1.1);
        }

        /* Processing Modal Styling */
        #processingModal .modal-content {
            border-radius: 16px;
        }

        #processingModal .spinner-border {
            border-width: 3px;
        }

        .progress-bar-striped {
            background-image: linear-gradient(
                45deg,
                rgba(255, 255, 255, 0.15) 25%,
                transparent 25%,
                transparent 50%,
                rgba(255, 255, 255, 0.15) 50%,
                rgba(255, 255, 255, 0.15) 75%,
                transparent 75%,
                transparent
            );
            background-size: 1rem 1rem;
        }

        /* Loading states */
        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-right-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Improved form styling */
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            min-width: 45px;
            justify-content: center;
        }

        /* Camera modal improvements */
        #mediaFeed {
            background: #000;
        }

        #videoControls .btn {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Better spacing for mobile */
        .mb-3 {
            margin-bottom: 1rem !important;
        }

        .mb-4 {
            margin-bottom: 1.5rem !important;
        }

        .py-4 {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }

        /* Card grid improvements */
        #incidentTypeCards .col-6,
        #incidentTypeCards .col-12 {
            margin-bottom: 0.5rem;
        }

        /* Shake animation for validation */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Leaflet Map Styles */
        #mapContainer {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 0.75rem;
        }

        #incidentMap .leaflet-marker-icon {
            filter: hue-rotate(0deg) brightness(1) contrast(1);
        }

        #incidentMap .leaflet-control-zoom {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .map-marker-selected {
            animation: pulse 0.6s ease-in-out;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.3/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Incident Type Cards Selection
            const incidentTypeCards = document.querySelectorAll('.incident-type-card');
            const selectedIncidentTypeInput = document.getElementById('selectedIncidentType');

            incidentTypeCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove active class from all cards
                    incidentTypeCards.forEach(c => c.classList.remove('active'));

                    // Add active class to clicked card
                    this.classList.add('active');

                    // Set the hidden input value
                    selectedIncidentTypeInput.value = this.dataset.value;

                    // Visual feedback
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });

            // Form validation for incident type
            document.getElementById('reportForm').addEventListener('submit', function(e) {
                if (!selectedIncidentTypeInput.value) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incident Type Required',
                        text: 'Please select an incident type to continue.',
                        confirmButtonText: 'OK'
                    });

                    // Add shake animation to cards
                    incidentTypeCards.forEach(card => {
                        card.style.animation = 'shake 0.5s ease-in-out';
                        setTimeout(() => {
                            card.style.animation = '';
                        }, 500);
                    });
                    return;
                }
            });

            // Elements
            const mediaModal = new bootstrap.Modal(document.getElementById('mediaModal'));
            const processingModal = new bootstrap.Modal(document.getElementById('processingModal'));
            const openCameraBtn = document.getElementById('openCamera');
            const openVideoCameraBtn = document.getElementById('openVideoCamera');
            const captureBtn = document.getElementById('captureButton');
            const startRecordingBtn = document.getElementById('startRecording');
            const stopRecordingBtn = document.getElementById('stopRecording');
            const recordingTimer = document.getElementById('recordingTimer');
            const videoControls = document.getElementById('videoControls');
            const modalTitle = document.getElementById('modalTitle');
            const fileUpload = document.getElementById('fileUpload');
            const video = document.getElementById('mediaFeed');
            const canvas = document.getElementById('photoCanvas');
            const previewContainer = document.getElementById('mediaPreview');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const processingTitle = document.getElementById('processingTitle');
            const processingMessage = document.getElementById('processingMessage');
            const processingProgress = document.getElementById('processingProgress');

            let stream = null;
            let currentPosition = null;
            let mediaRecorder = null;
            let recordedChunks = [];
            let recordingStartTime = null;
            let timerInterval = null;
            let isVideoMode = false;
            let mapInstance = null;
            let mapMarker = null;

            // Initialize map
            function initializeMap() {
                // Default center (Philippines center)
                let mapCenter = [12.8797, 121.7740];

                // Initialize map
                mapInstance = L.map('incidentMap').setView(mapCenter, 13);

                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                    timeout: 8000, // 8 second timeout for tiles
                    errorTileUrl: '',
                }).addTo(mapInstance);

                // Get user's geolocation to center map - with timeout protection
                if ('geolocation' in navigator) {
                    const geoTimeout = setTimeout(() => {
                        console.log('Geolocation request timed out, using default center');
                    }, 8000);

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            clearTimeout(geoTimeout);
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            mapCenter = [lat, lng];
                            mapInstance.setView([lat, lng], 16);

                            // Add marker at current position
                            if (mapMarker) {
                                mapMarker.remove();
                            }
                            mapMarker = L.marker([lat, lng]).addTo(mapInstance)
                                .bindPopup('Your current location')
                                .openPopup();
                        },
                        (error) => {
                            clearTimeout(geoTimeout);
                            console.log('Geolocation error:', error);
                        },
                        {
                            enableHighAccuracy: false,
                            timeout: 8000,
                            maximumAge: 0
                        }
                    );
                }

                // Handle map clicks
                mapInstance.on('click', function(e) {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;

                    // Update input fields
                    latitudeInput.value = lat.toFixed(6);
                    longitudeInput.value = lng.toFixed(6);

                    // Update map marker
                    if (mapMarker) {
                        mapMarker.remove();
                    }

                    mapMarker = L.marker([lat, lng]).addTo(mapInstance)
                        .bindPopup(`Location selected<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`)
                        .openPopup();

                    // Add a brief pulse animation to the marker
                    mapMarker._icon.classList.add('map-marker-selected');
                    setTimeout(() => {
                        mapMarker._icon.classList.remove('map-marker-selected');
                    }, 600);

                    // Visual feedback on input fields
                    latitudeInput.classList.add('border-success', 'bg-success', 'bg-opacity-10');
                    longitudeInput.classList.add('border-success', 'bg-success', 'bg-opacity-10');

                    setTimeout(() => {
                        latitudeInput.classList.remove('border-success', 'bg-success', 'bg-opacity-10');
                        longitudeInput.classList.remove('border-success', 'bg-success', 'bg-opacity-10');
                    }, 2000);

                    // Update current position for media capture
                    currentPosition = {
                        latitude: lat,
                        longitude: lng
                    };
                });
            }

            // Initialize map when document is ready
            setTimeout(() => {
                initializeMap();
            }, 300);

            // Open camera for photos
            openCameraBtn.addEventListener('click', async () => {
                isVideoMode = false;
                modalTitle.textContent = 'Take Photo';
                captureBtn.classList.remove('d-none');
                videoControls.classList.add('d-none');
                await openCamera();
            });

            // Open camera for videos
            openVideoCameraBtn.addEventListener('click', async () => {
                isVideoMode = true;
                modalTitle.textContent = 'Record Video';
                captureBtn.classList.add('d-none');
                videoControls.classList.remove('d-none');
                await openCamera();
            });

            // Function to open camera
            async function openCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment' },
                        audio: isVideoMode
                    });
                    video.srcObject = stream;
                    mediaModal.show();

                    // Prevent screen from sleeping during camera use
                    if ('wakeLock' in navigator) {
                        try {
                            const wakeLock = await navigator.wakeLock.request('screen');
                        } catch (err) {
                            console.log('Wake Lock not supported');
                        }
                    }
                } catch (err) {
                    console.error('Error accessing camera:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Camera Access',
                        text: 'Unable to access camera. Please make sure you have granted camera permissions.',
                        confirmButtonText: 'OK'
                    });
                }
            }

            // Start video recording
            startRecordingBtn.addEventListener('click', () => {
                if (!stream) return;

                recordedChunks = [];
                const options = { mimeType: 'video/webm; codecs=vp9,opus' };
                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options.mimeType = 'video/webm; codecs=vp8,opus';
                }
                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options.mimeType = 'video/webm';
                }
                if (!MediaRecorder.isTypeSupported(options.mimeType)) {
                    options.mimeType = '';
                }

                try {
                    mediaRecorder = new MediaRecorder(stream, options);

                    mediaRecorder.ondataavailable = (event) => {
                        if (event.data.size > 0) {
                            recordedChunks.push(event.data);
                        }
                    };

                    mediaRecorder.onstop = () => {
                        const blob = new Blob(recordedChunks, { type: 'video/webm' });
                        showProcessingModal('video');
                        getLocationAndAddMedia(blob, 'video');
                    };

                    mediaRecorder.start(1000); // Collect data every second
                    startRecordingBtn.classList.add('d-none');
                    stopRecordingBtn.classList.remove('d-none');
                    recordingTimer.classList.remove('d-none');

                    // Start timer
                    recordingStartTime = Date.now();
                    timerInterval = setInterval(updateRecordingTimer, 1000);
                } catch (e) {
                    console.error('MediaRecorder error:', e);
                    Swal.fire({
                        icon: 'error',
                        title: 'Recording Failed',
                        text: 'Unable to start video recording on this device.'
                    });
                }
            });

            // Stop video recording
            stopRecordingBtn.addEventListener('click', () => {
                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    mediaRecorder.stop();
                    clearInterval(timerInterval);
                    startRecordingBtn.classList.remove('d-none');
                    stopRecordingBtn.classList.add('d-none');
                    recordingTimer.classList.add('d-none');
                    recordingTimer.textContent = '00:00';
                }
            });

            // Update recording timer
            function updateRecordingTimer() {
                const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
                const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
                const seconds = (elapsed % 60).toString().padStart(2, '0');
                recordingTimer.textContent = `${minutes}:${seconds}`;

                // Auto-stop after 5 minutes
                if (elapsed >= 300) {
                    stopRecordingBtn.click();
                }
            }

            // Show processing modal
            function showProcessingModal(mediaType) {
                const isPhoto = mediaType === 'photo';
                processingTitle.textContent = isPhoto ? 'Processing Photo' : 'Processing Video';
                processingMessage.textContent = 'Please wait while we process your media and get your location...';
                processingProgress.style.width = '0%';
                processingModal.show();

                // Simulate progress for better UX
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) {
                        progress = 90; // Hold at 90% until actual processing completes
                        clearInterval(progressInterval);
                    }
                    processingProgress.style.width = `${progress}%`;
                }, 200);
            }

            // Hide processing modal
            function hideProcessingModal() {
                processingProgress.style.width = '100%';
                setTimeout(() => {
                    processingModal.hide();
                    // Reset progress bar
                    setTimeout(() => {
                        processingProgress.style.width = '0%';
                    }, 300);
                }, 500);
            }

            // Update location inputs
            function updateLocationInputs() {
                if (currentPosition) {
                    const { latitude, longitude } = currentPosition;
                    latitudeInput.value = latitude.toFixed(6);
                    longitudeInput.value = longitude.toFixed(6);

                    // Visual feedback
                    latitudeInput.classList.add('border-success', 'bg-success', 'bg-opacity-10');
                    longitudeInput.classList.add('border-success', 'bg-success', 'bg-opacity-10');

                    setTimeout(() => {
                        latitudeInput.classList.remove('border-success', 'bg-success', 'bg-opacity-10');
                        longitudeInput.classList.remove('border-success', 'bg-success', 'bg-opacity-10');
                    }, 3000);
                }
            }

            // Capture photo
            captureBtn.addEventListener('click', async () => {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                showProcessingModal('photo');

                // Small delay to ensure modal is shown
                setTimeout(async () => {
                    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.8));
                    getLocationAndAddMedia(blob, 'photo');
                }, 100);
            });

            // Handle file uploads
            fileUpload.addEventListener('change', function() {
                const files = Array.from(this.files);
                if (files.length > 10) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Too Many Files',
                        text: 'Please select up to 10 files maximum.',
                        confirmButtonText: 'OK'
                    });
                    this.value = '';
                    return;
                }

                files.forEach(file => {
                    if (file.size > 100 * 1024 * 1024) { // 100MB limit
                        Swal.fire({
                            icon: 'error',
                            title: 'File Too Large',
                            text: `${file.name} exceeds 100MB limit.`,
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    showProcessingModal(file.type.startsWith('video/') ? 'video' : 'photo');
                    getLocationAndAddMedia(file, file.type.startsWith('video/') ? 'video' : 'photo');
                });
                this.value = '';
            });

            // Get location and add media
            function getLocationAndAddMedia(blob, mediaType) {
                if ('geolocation' in navigator) {
                    const geoTimeout = setTimeout(() => {
                        console.log('Location request timed out, adding media without location');
                        addMediaPreview(blob, mediaType, null, null);
                        hideProcessingModal();
                    }, 5000); // 5 second timeout for location

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            clearTimeout(geoTimeout);
                            currentPosition = {
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude
                            };
                            updateLocationInputs();
                            addMediaPreview(blob, mediaType, currentPosition.latitude, currentPosition.longitude);
                            hideProcessingModal();
                        },
                        (error) => {
                            clearTimeout(geoTimeout);
                            console.error('Location error:', error);
                            let message = 'Unable to get location.';
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    message = 'Location access denied. Please enable location services.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    message = 'Location information unavailable.';
                                    break;
                                case error.TIMEOUT:
                                    message = 'Location request timed out.';
                                    break;
                            }

                            hideProcessingModal();

                            Swal.fire({
                                icon: 'warning',
                                title: 'Location',
                                text: message,
                                confirmButtonText: 'Continue Anyway'
                            }).then(() => {
                                addMediaPreview(blob, mediaType, null, null);
                            });
                        },
                        {
                            enableHighAccuracy: false,
                            timeout: 5000,
                            maximumAge: 0
                        }
                    );
                } else {
                    addMediaPreview(blob, mediaType, null, null);
                    hideProcessingModal();
                }

                mediaModal.hide();
            }

            function addMediaPreview(blob, mediaType, latitude, longitude) {
                const preview = document.createElement('div');
                preview.className = 'media-preview-item position-relative';

                let mediaElement = '';
                const blobUrl = URL.createObjectURL(blob);

                if (mediaType === 'photo') {
                    mediaElement = `<img src="${blobUrl}" class="img-thumbnail">`;
                } else {
                    mediaElement = `
                        <video class="img-thumbnail" controls>
                            <source src="${blobUrl}" type="video/webm">
                        </video>
                    `;
                }

                let photoDataUrl = '';
                if (mediaType === 'photo') {
                    // For photos taken with camera, use canvas.toDataURL
                    // For uploaded files, we'll handle via video blob
                    photoDataUrl = (blob instanceof Blob && blob.type.startsWith('image/')) ?
                        blobUrl : canvas.toDataURL('image/jpeg');
                }

                preview.innerHTML = `
                    ${mediaElement}
                    <button type="button" class="btn btn-danger btn-sm delete-preview">
                        <i class="fas fa-times"></i>
                    </button>
                    <input type="hidden" name="${mediaType === 'photo' ? 'photos' : 'videos'}[]" value="">
                    <input type="hidden" name="${mediaType === 'photo' ? 'photos_latitude' : 'videos_latitude'}[]" value="${latitude !== null ? latitude.toFixed(6) : ''}">
                    <input type="hidden" name="${mediaType === 'photo' ? 'photos_longitude' : 'videos_longitude'}[]" value="${longitude !== null ? longitude.toFixed(6) : ''}">
                `;

                // Store blob data for submission
                if (mediaType === 'photo') {
                    const photoInput = preview.querySelector('input[name*="photos"]');
                    const fileReader = new FileReader();
                    fileReader.onload = function() {
                        photoInput.value = fileReader.result;
                    };
                    fileReader.readAsDataURL(blob);
                } else if (mediaType === 'video') {
                    const videoInput = document.createElement('input');
                    videoInput.type = 'hidden';
                    videoInput.name = 'video_blobs[]';

                    const reader = new FileReader();
                    reader.onload = function() {
                        videoInput.value = reader.result;
                        preview.appendChild(videoInput);
                    };
                    reader.readAsDataURL(blob);
                }

                preview.querySelector('.delete-preview').addEventListener('click', () => {
                    URL.revokeObjectURL(blobUrl);
                    preview.remove();
                });

                previewContainer.appendChild(preview);
            }

            // Clean up when modal is closed
            mediaModal._element.addEventListener('hidden.bs.modal', stopCamera);

            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    video.srcObject = null;
                }

                if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                    mediaRecorder.stop();
                }

                if (timerInterval) {
                    clearInterval(timerInterval);
                }
            }

            // Form submission handler
            document.getElementById('reportForm').addEventListener('submit', function(e) {
                e.preventDefault();

                let form = e.target;
                let formData = new FormData(form);

                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
                submitBtn.disabled = true;

                fetch("{{ route('incidents.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    }
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Report Submitted!',
                            text: `Reference Number: ${data.data.reference_number}`,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            form.reset();
                            previewContainer.innerHTML = '';
                            // Reset incident type cards
                            incidentTypeCards.forEach(card => card.classList.remove('active'));
                            selectedIncidentTypeInput.value = '';
                        });
                    } else if (data.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Submission Failed',
                            text: data.error,
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Please check your connection and try again.',
                        confirmButtonText: 'OK'
                    });
                })
                .finally(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });

            // Prevent form resubmission on page refresh
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        });
    </script>
@endsection
```
