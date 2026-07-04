<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - EcoConnect')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<!-- Pass VAPID key to JavaScript FIRST -->
    <script>
        window.vapidPublicKey = '{{ env('VAPID_PUBLIC_KEY') }}';
        console.log('VAPID Key injected:', window.vapidPublicKey ? 'Yes ✓' : 'No ✗');
        console.log('VAPID Key value:', window.vapidPublicKey);
    </script>

    <!-- Notification Manager Script -->
    <script src="{{ asset('js/notification-manager.js') }}"></script>

    <style>
        :root {
            --denr-green: #1a472a;
            --denr-light-green: #2e7d32;
            --denr-gold: #d4af37;
            --denr-light: #f5f9f7;
            --denr-white: #ffffff;
            --denr-gray: #6b7280;
            --denr-light-gray: #f8fafc;
            --bottom-nav-height: 70px;
            --header-height: 60px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: var(--denr-light);
            color: #333;
            line-height: 1.6;
            padding-bottom: var(--bottom-nav-height); /* Space for bottom nav */
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            color: var(--denr-green);
        }

        /* Mobile Header */
        .mobile-header {
            height: var(--header-height);
            background: var(--denr-white);
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px 16px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .mobile-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mobile-logo {
            color: var(--denr-gold);
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .mobile-brand {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--denr-green);
        }

        .mobile-header-right {
            display: flex;
            align-items: center;
        }

        .user-avatar-mobile {
            margin-left: 16px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--denr-green);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
        }

        /* Content Area */
        .mobile-content {
            padding: calc(var(--header-height) + 16px) 16px 16px 16px;
            min-height: calc(100vh - var(--header-height) - var(--bottom-nav-height));
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--bottom-nav-height);
            background: var(--denr-white);
            border-top: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 0 8px;
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .nav-item-mobile {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--denr-gray);
            font-size: 0.75rem;
            padding: 4px;
            border-radius: 8px;
            transition: all 0.3s ease;
            min-width: 60px;
            min-height: 50px;
        }

        .nav-item-mobile:hover {
            background: var(--denr-light);
            color: var(--denr-green);
        }

        .nav-item-mobile.active {
            color: var(--denr-green);
            background: rgba(26, 71, 42, 0.1);
        }

        .nav-icon-mobile {
            font-size: 1.2rem;
            margin-bottom: 2px;
        }

        /* Cards */
        .card {
            background: var(--denr-white);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 16px;
            margin-bottom: 16px;
            border: none;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--denr-green);
            margin: 0;
        }

        /* Stats Grid - Mobile Optimized */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--denr-white);
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 3px solid var(--denr-green);
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--denr-green);
            margin-bottom: 4px;
        }

        .stat-label {
            color: var(--denr-gray);
            font-size: 0.8rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 16px;
            background: var(--denr-green);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            min-height: 44px; /* Touch target */
        }

        .btn:hover {
            background: var(--denr-light-green);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--denr-green);
            color: var(--denr-green);
        }

        .btn-outline:hover {
            background: var(--denr-green);
            color: white;
        }

        .btn-sm {
            padding: 8px 12px;
            font-size: 0.8rem;
            min-height: 36px;
        }

        /* Loading Animation */
        #page-loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s ease-out;
            pointer-events: none;
            opacity: 0;
        }

        #page-loading.active {
            opacity: 1;
            pointer-events: all;
        }

        .denr-loader {
            width: 50px;
            height: 50px;
            position: relative;
        }

        .denr-loader:before {
            content: '';
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 4px solid var(--denr-light);
            position: absolute;
            top: 0;
            left: 0;
        }

        .denr-loader:after {
            content: '';
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 4px solid transparent;
            border-top-color: var(--denr-green);
            position: absolute;
            top: 0;
            left: 0;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            margin-top: 16px;
            color: var(--denr-green);
            font-weight: 500;
            text-align: center;
            font-size: 0.9rem;
        }

        /* User Menu Modal */
        .user-menu-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: flex-end;
            align-items: flex-start;
            padding-top: calc(var(--header-height) + 8px);
        }

        .user-menu-modal.active {
            display: flex;
        }

        .user-menu-content {
            background: white;
            border-radius: 12px;
            margin-right: 16px;
            min-width: 200px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .user-menu-header {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        .user-menu-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--denr-green);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
            margin: 0 auto 8px;
        }

        .user-menu-name {
            font-weight: 600;
            color: var(--denr-green);
            margin-bottom: 4px;
        }

        .user-menu-email {
            font-size: 0.8rem;
            color: var(--denr-gray);
        }

        .user-menu-body {
            padding: 8px 0;
        }

        .user-menu-item {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: #333;
            transition: background 0.3s ease;
            font-size: 0.9rem;
        }

        .user-menu-item:hover {
            background: var(--denr-light);
        }

        .user-menu-item i {
            margin-right: 8px;
            width: 16px;
        }

        /* Form Elements */
        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 12px 16px;
            font-size: 1rem;
            min-height: 44px;
        }

        .form-control:focus {
            border-color: var(--denr-green);
            box-shadow: 0 0 0 3px rgba(26, 71, 42, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: var(--denr-green);
            margin-bottom: 8px;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px;
        }

        .modal-body {
            padding: 16px;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 16px;
        }

        /* Table Styles for Mobile */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--denr-green);
            color: white;
            font-weight: 600;
            padding: 12px;
            font-size: 0.8rem;
        }

        .table tbody td {
            padding: 12px;
            font-size: 0.8rem;
        }

        /* Badge Styles */
        .badge {
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 12px;
        }

        /* Alert Styles */
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }

        /* Touch-friendly interactions */
        button, .btn, a {
            min-height: 44px;
            min-width: 44px;
        }

        /* Hide scrollbars on mobile */
        ::-webkit-scrollbar {
            display: none;
        }

        /* Safe area for devices with notches */
        @supports (padding: max(0px)) {
            .mobile-header {
                padding-top: max(16px, env(safe-area-inset-top));
            }

            .bottom-nav {
                padding-bottom: max(8px, env(safe-area-inset-bottom));
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Page Loading Overlay -->
    <div id="page-loading">
        <div class="d-flex flex-column align-items-center">
            <div class="denr-loader"></div>
            <div class="loading-text">Loading...</div>
        </div>
    </div>

    <!-- Mobile Header -->
    <header class="mobile-header">
        <div class="mobile-header-left">
            <div class="mobile-logo"><img src="{{ asset('logo.png') }}" alt="" width="30"></div>

            <div class="mobile-brand">EcoConnect</div>
        </div>

        <div class="mobile-header-right">
            @include('components.notification-widget')

            <div class="user-avatar-mobile" id="userMenuToggle">
                {{ strtoupper(substr(Auth::user()->fname, 0, 1)) }}{{ strtoupper(substr(Auth::user()->lname, 0, 1)) }}
            </div>
        </div>
    </header>

    <!-- User Menu Modal -->
    <div class="user-menu-modal" id="userMenuModal">
        <div class="user-menu-content">
            <div class="user-menu-header">
                <div class="user-menu-avatar">
                    {{ strtoupper(substr(Auth::user()->fname, 0, 1)) }}{{ strtoupper(substr(Auth::user()->lname, 0, 1)) }}
                </div>
                <div class="user-menu-name">{{ Auth::user()->fname }} {{ Auth::user()->lname }}</div>
                <div class="user-menu-email">{{ Auth::user()->email }}</div>
            </div>
            <div class="user-menu-body">
                <a href="{{ route('profile.edit') }}" class="user-menu-item">
                    <i class="fas fa-user"></i> Profile
                </a>
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                </form>
                <button type="button" class="bg-transparent border-0 user-menu-item w-100 text-start" id="logoutLink">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 1rem; color: var(--denr-green);">Confirm Logout</h3>
            <p style="margin-bottom: 1.5rem;">Are you sure you want to logout?</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button id="cancelLogout" style="padding: 0.5rem 1rem; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button id="confirmLogout" style="padding: 0.5rem 1rem; background: var(--denr-green); color: white; border: none; border-radius: 4px; cursor: pointer;">Logout</button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="mobile-content">
        @yield('content')
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="{{ route('dashboard') }}" class="nav-item-mobile {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home nav-icon-mobile"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('incidents') }}" class="nav-item-mobile {{ request()->routeIs('incidents') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list nav-icon-mobile"></i>
            <span>My Reports</span>
        </a>
        <a href="{{ route('report-incident') }}" class="nav-item-mobile {{ request()->routeIs('report-incident') ? 'active' : '' }}">
            <i class="fas fa-plus-circle nav-icon-mobile"></i>
            <span>New Report</span>
        </a>
        <a href="{{ route('profile.edit') }}" class="nav-item-mobile {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <i class="fas fa-user nav-icon-mobile"></i>
            <span>Profile</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingOverlay = document.getElementById('page-loading');
            const userMenuToggle = document.getElementById('userMenuToggle');
            const userMenuModal = document.getElementById('userMenuModal');

            // Show loading overlay
            function showLoading() {
                loadingOverlay.classList.add('active');
            }

            // Hide loading overlay
            function hideLoading() {
                loadingOverlay.classList.remove('active');
            }

            // User menu toggle
            userMenuToggle.addEventListener('click', function() {
                userMenuModal.classList.toggle('active');
            });

            // Close user menu when clicking outside
            userMenuModal.addEventListener('click', function(e) {
                if (e.target === userMenuModal) {
                    userMenuModal.classList.remove('active');
                }
            });

            // Enhanced click handler for navigation
            document.addEventListener('click', function(e) {
                const element = e.target.closest('a, button');

                if (element) {
                    // Check if this element triggers a modal
                    const isModalTrigger = element.hasAttribute('data-bs-toggle') &&
                                          element.getAttribute('data-bs-toggle') === 'modal';

                    // Don't show loading for modal triggers or bottom nav
                    if (isModalTrigger || element.closest('.bottom-nav') || element.closest('.user-menu-modal')) {
                        return;
                    }

                    // Handle regular links
                    if (element.tagName === 'A' &&
                        element.href &&
                        !element.href.startsWith('javascript:') &&
                        !element.getAttribute('href')?.startsWith('#') &&
                        element.target !== '_blank' &&
                        !element.hasAttribute('download') &&
                        !element.hasAttribute('data-no-loading')) {

                        e.preventDefault();
                        showLoading();

                        setTimeout(() => {
                            window.location.href = element.href;
                        }, 300);
                    }
                }
            });

            // Hide loading when Bootstrap modals are shown
            document.addEventListener('show.bs.modal', function() {
                hideLoading();
            });

            // Hide loading when page is fully loaded
            window.addEventListener('load', function() {
                hideLoading();
            });

            // Also hide loading if there's an error
            window.addEventListener('error', function() {
                hideLoading();
            });

            // Handle session messages
            @if(session('error'))
                hideLoading();
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: '{{ session('error') }}',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                }, 500);
            @endif

            @if(session('success'))
                hideLoading();
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: '{{ session('success') }}',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 500);
            @endif

            // Logout modal
            const logoutLink = document.getElementById('logoutLink');
            const logoutModal = document.getElementById('logoutModal');
            const cancelLogout = document.getElementById('cancelLogout');
            const confirmLogout = document.getElementById('confirmLogout');

            logoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                logoutModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });

            cancelLogout.addEventListener('click', function() {
                logoutModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });

            confirmLogout.addEventListener('click', function() {
                document.getElementById('logoutForm').submit();
            });

            logoutModal.addEventListener('click', function(e) {
                if (e.target === logoutModal) {
                    logoutModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && logoutModal.style.display === 'flex') {
                    logoutModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
