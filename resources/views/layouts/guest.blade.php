<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EcoConnect - DENR Environmental Incident Reporting')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    @PwaHead
    <!-- Styles -->
    <style>
        :root {
            --denr-green: #1a472a;
            --denr-light-green: #2e7d32;
            --denr-gold: #d4af37;
            --denr-light: #f5f9f7;
            --denr-white: #ffffff;
            --denr-gray: #6b7280;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            color: #333;
            background-color: var(--denr-light);
            line-height: 1.6;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            color: var(--denr-green);
            margin-bottom: 1rem;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--denr-green);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background-color: var(--denr-light-green);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--denr-green);
            color: var(--denr-green);
        }
        
        .btn-outline:hover {
            background-color: var(--denr-green);
            color: white;
        }
        
        .section {
            padding: 80px 0;
        }
        
        .section-light {
            background-color: var(--denr-white);
        }
        
        .section-green {
            background-color: var(--denr-green);
            color: white;
        }
        
        .section-green h2, .section-green h3 {
            color: white;
        }
        
        .text-center {
            text-align: center;
        }
        
        .hero {
            background: linear-gradient(rgba(26, 71, 42, 0.8), rgba(26, 71, 42, 0.9)), url('/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 150px 0 100px;
            text-align: center;
        }
        
        .hero h1 {
            color: white;
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 2rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--denr-green);
            margin-bottom: 1rem;
        }
        
        .leaf-decoration {
            position: relative;
        }
        
        .leaf-decoration::after {
            content: "❦";
            color: var(--denr-gold);
            font-size: 1.5rem;
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    @include('components.header')
    
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('components.footer')
    <!-- Add this inside <body> -->
    <!-- Enhanced PWA Install Button -->
    <div id="pwa-install-container">
        <button id="pwa-install-btn" class="pulse-animation">
            <span class="btn-icon"><i class="fas fa-download"></i></span>
            <span class="btn-text">Install EcoConnect</span>
            <span class="btn-badge">!</span>
        </button>
    </div>

    <!-- PWA Install Prompt Modal -->
    <div class="modal fade" id="pwa-prompt-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Install EcoConnect App</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="app-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4>Get the Full Experience</h4>
                    <p>Install EcoConnect on your device for faster access, offline functionality, and push notifications.</p>
                    <div class="mt-4">
                        <div class="mb-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-bolt text-warning me-2"></i>
                            <span>Faster loading times</span>
                        </div>
                        <div class="mb-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-wifi-slash text-info me-2"></i>
                            <span>Works offline</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-bell text-success me-2"></i>
                            <span>Receive updates</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-later" data-bs-dismiss="modal">Maybe Later</button>
                    <button type="button" class="btn btn-install" id="modal-install-btn">Install Now</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Enhanced PWA Install Button Styles */
        #pwa-install-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
            animation: slideInUp 0.5s ease-out;
        }
        
        #pwa-install-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            background: linear-gradient(135deg, var(--denr-green), var(--denr-light-green));
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 8px 20px rgba(26, 71, 42, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        #pwa-install-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        #pwa-install-btn:hover::before {
            left: 100%;
        }
        
        #pwa-install-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(26, 71, 42, 0.4);
        }
        
        #pwa-install-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(26, 71, 42, 0.4);
        }
        
        #pwa-install-btn .btn-icon {
            font-size: 20px;
            transition: transform 0.3s ease;
        }
        
        #pwa-install-btn:hover .btn-icon {
            transform: scale(1.1);
        }
        
        #pwa-install-btn .btn-text {
            white-space: nowrap;
        }
        
        #pwa-install-btn .btn-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--denr-gold);
            color: var(--denr-green);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        /* PWA Install Prompt Modal */
        #pwa-prompt-modal {
            backdrop-filter: blur(5px);
        }
        
        #pwa-prompt-modal .modal-content {
            border-radius: 20px;
            overflow: hidden;
            border: none;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        #pwa-prompt-modal .modal-header {
            background: linear-gradient(135deg, var(--denr-green), var(--denr-light-green));
            color: white;
            border-bottom: none;
            padding: 20px 25px;
        }
        
        #pwa-prompt-modal .modal-body {
            padding: 25px;
            text-align: center;
        }
        
        #pwa-prompt-modal .modal-body .app-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--denr-green), var(--denr-light-green));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
            box-shadow: 0 8px 15px rgba(26, 71, 42, 0.2);
        }
        
        #pwa-prompt-modal .modal-footer {
            border-top: none;
            padding: 20px 25px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        
        #pwa-prompt-modal .btn-install {
            background: linear-gradient(135deg, var(--denr-green), var(--denr-light-green));
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            flex: 1;
        }
        
        #pwa-prompt-modal .btn-later {
            background: transparent;
            color: var(--denr-gray);
            border: 1px solid #e0e0e0;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            flex: 1;
        }
        
        #pwa-prompt-modal .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 71, 42, 0.3);
        }
        
        /* Animation for the button */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(26, 71, 42, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(26, 71, 42, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(26, 71, 42, 0);
            }
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        /* Mobile Responsive Adjustments */
        @media (max-width: 768px) {
            #pwa-install-container {
                bottom: 15px;
                right: 15px;
                left: 15px;
            }
            
            #pwa-install-btn {
                width: 100%;
                justify-content: center;
                padding: 18px 24px;
                border-radius: 16px;
            }
            
            #pwa-prompt-modal .modal-dialog {
                margin: 20px;
            }
        }
        
        @media (max-width: 480px) {
            #pwa-install-btn .btn-text {
                font-size: 14px;
            }
            
            #pwa-prompt-modal .modal-body {
                padding: 20px 15px;
            }
            
            #pwa-prompt-modal .modal-footer {
                flex-direction: column;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const installContainer = document.getElementById('pwa-install-container');
            const installBtn = document.getElementById('pwa-install-btn');
            const promptModal = new bootstrap.Modal(document.getElementById('pwa-prompt-modal'));
            const modalInstallBtn = document.getElementById('modal-install-btn');
            
            // Check if PWA install is available
            let deferredPrompt;
            
            window.addEventListener('beforeinstallprompt', (e) => {
                // Prevent the mini-infobar from appearing on mobile
                e.preventDefault();
                // Stash the event so it can be triggered later
                deferredPrompt = e;
                // Show the install button
                installContainer.style.display = 'block';
                
                // Auto-show the modal after 5 seconds on first visit
                if (!localStorage.getItem('pwaPromptShown')) {
                    setTimeout(() => {
                        promptModal.show();
                        localStorage.setItem('pwaPromptShown', 'true');
                    }, 5000);
                }
            });
            
            // Install button click handler
            installBtn.addEventListener('click', () => {
                promptModal.show();
            });
            
            // Modal install button click handler
            modalInstallBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    // Show the install prompt
                    deferredPrompt.prompt();
                    // Wait for the user to respond to the prompt
                    const { outcome } = await deferredPrompt.userChoice;
                    
                    if (outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                        installContainer.style.display = 'none';
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    
                    // Clear the saved prompt since it can't be used again
                    deferredPrompt = null;
                    
                    // Hide the modal
                    promptModal.hide();
                }
            });
            
            // Track when the PWA is successfully installed
            window.addEventListener('appinstalled', () => {
                console.log('EcoConnect was installed');
                installContainer.style.display = 'none';
                deferredPrompt = null;
            });
            
            // Check if the app is already installed
            if (window.matchMedia('(display-mode: standalone)').matches) {
                installContainer.style.display = 'none';
            }
            
            // Add interactive effects
            installBtn.addEventListener('mouseenter', function() {
                this.classList.remove('pulse-animation');
            });
            
            installBtn.addEventListener('mouseleave', function() {
                if (installContainer.style.display !== 'none') {
                    this.classList.add('pulse-animation');
                }
            });
            
            // Add touch feedback for mobile
            installBtn.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.95)';
            });
            
            installBtn.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('sw.js') }}"></script>
    <script>
        if ("serviceWorker" in navigator) {
            // Register a service worker hosted at the root of the
            // site using the default scope.
            navigator.serviceWorker.register("/sw.js").then(
            (registration) => {
                console.log("Service worker registration succeeded:", registration);
            },
            (error) => {
                console.error(`Service worker registration failed: ${error}`);
            },
            );
        } else {
            console.error("Service workers are not supported.");
        }
    </script>
    {{-- <script src="{{ asset('pwa-install.js') }}"></script> --}}
    @stack('scripts')
</body>
</html>