<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>@yield('title', 'Dashboard - EcoConnect')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

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
            --sidebar-width: 260px;
            --header-height: 70px;
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
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            color: var(--denr-green);
        }

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--denr-green);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            left: 0;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
            width: 0;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: var(--sidebar-width);
        }

        .sidebar-logo {
            background: white;
            color: var(--denr-green);
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .sidebar-brand {
            font-weight: 700;
            font-size: 1.2rem;
        }

        .sidebar-nav {
            padding: 20px 0;
            min-width: var(--sidebar-width);
        }

        .nav-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            min-width: var(--sidebar-width);
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--denr-gold);
        }

        .nav-icon {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            width: calc(100% - var(--sidebar-width));
        }

        .main-content.expanded {
            margin-left: 0;
            width: 100%;
        }

        /* Header */
        .header {
            height: var(--header-height);
            background: var(--denr-white);
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--denr-green);
            padding: 8px;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .menu-toggle:hover {
            background: var(--denr-light);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-widget {
            position: relative;
            display: inline-block;
        }

        .notification-widget .btn {
            position: relative;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            color: var(--denr-green);
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .notification-widget .btn:hover {
            background: var(--denr-light);
            border-color: var(--denr-green);
        }

        .notification-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 300px;
            z-index: 1000;
        }

        .notification-header {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }

        .notification-body {
            padding: 12px;
            min-height: 80px;
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-footer {
            padding: 12px;
            border-top: 1px solid #eee;
        }

        .badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .bg-success {
            background-color: #28a745 !important;
            color: white !important;
        }

        .bg-secondary {
            background-color: #6c757d !important;
            color: white !important;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background 0.3s ease;
        }

        .user-menu:hover {
            background: var(--denr-light);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--denr-green);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Content Area */
        .content {
            padding: 30px;
            min-height: calc(100vh - var(--header-height));
        }

        /* Cards */
        .card {
            background: var(--denr-white);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 24px;
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--denr-green);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--denr-white);
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--denr-green);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--denr-green);
            margin-bottom: 8px;
        }

        .stat-label {
            color: var(--denr-gray);
            font-size: 0.875rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--denr-green);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
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

        /* Loading Animation Styles */
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
            width: 60px;
            height: 60px;
            position: relative;
        }

        .denr-loader:before {
            content: '';
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 5px solid var(--denr-accent);
            position: absolute;
            top: 0;
            left: 0;
        }

        .denr-loader:after {
            content: '';
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 5px solid transparent;
            border-top-color: var(--denr-primary);
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
            margin-top: 20px;
            color: var(--denr-primary);
            font-weight: 500;
            text-align: center;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }

            .menu-toggle {
                display: block;
            }

            .header {
                padding: 0 20px;
            }

            .content {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        /* Desktop Collapse/Expand */
        @media (min-width: 769px) {
            .menu-toggle {
                display: block;
            }

            .sidebar.collapsed {
                transform: translateX(-100%);
                width: 0;
            }

            .main-content.expanded {
                margin-left: 0;
                width: 100%;
            }
        }

        /* Tablet Styles */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }

            .main-content {
                margin-left: 240px;
                width: calc(100% - 240px);
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .sidebar-header,
            .sidebar-nav,
            .nav-item {
                min-width: 240px;
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

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img class="sidebar-logo" src="{{ asset('logo.png') }}" width="60">
                <div class="sidebar-brand">EcoConnect</div>
            </div>

            <nav class="sidebar-nav">
                @if(auth()->user()->isUser())
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">🧭</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('incidents') }}" class="nav-item {{ request()->routeIs('incidents') ? 'active' : '' }}">
                        <span class="nav-icon">📝</span>
                        <span>My Reports</span>
                    </a>

                    <a href="{{ route('report-incident') }}" class="nav-item {{ request()->routeIs('report-incident') ? 'active' : '' }}">
                        <span class="nav-icon">➕</span>
                        <span>New Report</span>
                    </a>

                @elseif(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">🧭</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.incidents') }}" class="nav-item {{ request()->routeIs('admin.incidents') ? 'active' : '' }}">
                        <span class="nav-icon">📝</span>
                        <span>Incident Reports</span>
                    </a>

                    <a href="{{ route('admin.citizens') }}" class="nav-item {{ request()->routeIs('admin.citizens') ? 'active' : '' }}">
                        <span class="nav-icon">🧑‍🤝‍🧑</span>
                        <span>Manage Citizens</span>
                    </a>

                    <a href="{{ route('admin.police') }}" class="nav-item {{ request()->routeIs('admin.police') ? 'active' : '' }}">
                        <span class="nav-icon">👮</span>
                        <span>Manage Police</span>
                    </a>

                    {{-- manage bfp --}}
                    <a href="{{ route('admin.bfp') }}" class="nav-item {{ request()->routeIs('admin.bfp') ? 'active' : '' }}">
                        <span class="nav-icon">🚒</span>
                        <span>Manage BFP</span>
                    </a>

                    <a href="{{ route('admin.analytics') }}" class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                        <span class="nav-icon">📈</span>
                        <span>Analytics</span>
                    </a>

                    <a href="{{ route('admin.backup') }}" class="nav-item {{ request()->routeIs('admin.backup') ? 'active' : '' }}">
                        <span class="nav-icon">💾</span>
                        <span>Database Backup</span>
                    </a>
                @elseif(auth()->user()->isPolice())
                    <a href="{{ route('police.dashboard') }}" class="nav-item {{ request()->routeIs('police.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">🧭</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('police.incidents') }}" class="nav-item {{ request()->routeIs('police.incidents') ? 'active' : '' }}">
                        <span class="nav-icon">📝</span>
                        <span>Incident Reports</span>
                    </a>
                @else
                    <a href="{{ route('bfp.dashboard') }}" class="nav-item {{ request()->routeIs('bfp.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">🧭</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('bfp.incidents') }}" class="nav-item {{ request()->routeIs('bfp.incidents') ? 'active' : '' }}">
                        <span class="nav-icon">📝</span>
                        <span>Incident Reports</span>
                    </a>
                @endif



                <div class="nav-section" style="margin-top: 20px; padding: 0 20px;">
                    <div style="color: rgba(255, 255, 255, 0.5); font-size: 0.75rem; text-transform: uppercase; margin-bottom: 10px;">
                        Account
                    </div>
                </div>

                <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                    <span class="nav-icon">👤</span>
                    <span>Profile</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                </form>

                <!-- Logout Link -->
                <a href="#" class="nav-item" id="logoutLink">
                    <span class="nav-icon">🚪</span>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Custom Modal -->
        <div id="logoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 10000; justify-content: center; align-items: center;">
            <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h3 style="margin-bottom: 1rem; color: var(--denr-green);">Confirm Logout</h3>
                <p style="margin-bottom: 1.5rem;">Are you sure you want to logout?</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button id="cancelLogout" style="padding: 0.5rem 1.5rem; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                    <button id="confirmLogout" style="padding: 0.5rem 1.5rem; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Logout</button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        ☰
                    </button>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="header-right">
                    <!-- Notification Widget -->
                    @include('components.notification-widget')

                    <!-- User Menu -->
                    <div class="user-menu" id="userMenu">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->fname, 0, 1)) }}{{ strtoupper(substr(Auth::user()->lname, 0, 1)) }}
                        </div>
                        <div style="display: none; sm:display: block;">
                            <div style="font-weight: 600; font-size: 0.9rem;">
                                {{ Auth::user()->fname }} {{ Auth::user()->lname }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--denr-gray);">
                                {{ Auth::user()->email }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    // Your existing closeAllModals function and session message code remains the same
    function closeAllModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });
    }

    @if(session('error'))
        closeAllModals();
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
        closeAllModals();
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

    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const loadingOverlay = document.getElementById('page-loading');

        let isCollapsed = false;

        // Show loading overlay
        function showLoading() {
            loadingOverlay.classList.add('active');
        }

        // Hide loading overlay
        function hideLoading() {
            loadingOverlay.classList.remove('active');
        }

        // Sidebar toggle function (your existing code)
        function toggleSidebar() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            } else {
                isCollapsed = !isCollapsed;
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');

                if (isCollapsed) {
                    menuToggle.innerHTML = '☰';
                } else {
                    menuToggle.innerHTML = '✕';
                }
            }
        }

        if (menuToggle) {
            menuToggle.addEventListener('click', toggleSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });
        }

        // Close sidebar when clicking on nav items (mobile)
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Enhanced click handler that excludes modal triggers
        document.addEventListener('click', function(e) {
            const element = e.target.closest('a, button');

            if (element) {
                // Check if this element triggers a modal
                const isModalTrigger = element.hasAttribute('data-bs-toggle') &&
                                      element.getAttribute('data-bs-toggle') === 'modal';

                // Don't show loading for modal triggers
                if (isModalTrigger) {
                    return;
                }

                // Handle regular links (non-modal)
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

        // Handle window resize (your existing code)
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');

                if (!isCollapsed) {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('expanded');
                    menuToggle.innerHTML = '✕';
                } else {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                    menuToggle.innerHTML = '☰';
                }
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                menuToggle.innerHTML = '☰';
            }
        });

        // Your existing logout modal code remains the same
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

        // Hide loading when page is fully loaded
        window.addEventListener('load', function() {
            hideLoading();
        });

        // Also hide loading if there's an error
        window.addEventListener('error', function() {
            hideLoading();
        });
    });
</script>

    <!-- In resources/views/layouts/app.blade.php -->
    <script src="{{ asset('js/notification-manager.js') }}"></script>
    @include('components.notification-widget')

    @stack('scripts')
</body>
</html>
