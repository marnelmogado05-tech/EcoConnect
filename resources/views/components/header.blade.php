<header>
    <div class="container">
        <nav class="navbar">
            <div class="nav-brand">
                <a href="#">
                    <div style="display: flex; align-items: center;">
                        {{-- <div style="background-color: var(--denr-green); color: white; padding: 8px 12px; border-radius: 4px; margin-right: 10px;"> --}}
                        <a href="{{ route('index') }}" class="navbar navbar-brand">
                            <img src="{{ asset('logo.png') }}" alt="" width="50">
                            <span style="font-weight: 700; color: var(--denr-green);"> EcoConnect</span>
                        </a>
                    </div>
                </a>
            </div>

            <div class="nav-menu" id="nav-menu">
                <a href="#home" class="nav-link">Home</a>
                <a href="#about" class="nav-link">About</a>
                <a href="{{ route('register') }}" class="nav-link">Report Incident</a>
                <a href="{{ route('track.index') }}" class="nav-link">Track Report</a>
                @if(Auth::check())
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">Admin Dashboard</a>
                    @elseif(Auth::user()->role === 'bfp')
                        <a href="{{ route('bfp.dashboard') }}" class="nav-link">BFP Dashboard</a>
                    @elseif(Auth::user()->role === 'police')
                        <a href="{{ route('police.dashboard') }}" class="nav-link">Police Dashboard</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="nav-link btn btn-outline">Login</a>
                @endif
            </div>

            <div class="menu-toggle" id="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </div>

    <style>
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .nav-brand a {
            text-decoration: none;
            font-size: 1.5rem;
        }

        .nav-menu {
            display: flex;
            align-items: center;
        }

        .nav-link {
            margin-left: 30px;
            text-decoration: none;
            color: var(--denr-green);
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--denr-light-green);
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .menu-toggle span {
            height: 3px;
            width: 25px;
            background-color: var(--denr-green);
            margin-bottom: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        /* Hamburger animation */
        .menu-toggle.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }

            .nav-menu {
                position: fixed;
                top: 80px;
                left: -100%;
                flex-direction: column;
                background-color: var(--denr-white);
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
                padding: 20px 0;
                z-index: 1000;
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-link {
                margin: 15px 0;
                display: block;
            }

            /* Make sure the login button looks good on mobile */
            .nav-link.btn {
                margin: 15px auto;
                display: inline-block;
                width: auto;
            }
        }
    </style>

    <!-- Add this JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            const navMenu = document.getElementById('nav-menu');

            if (menuToggle && navMenu) {
                menuToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    menuToggle.classList.toggle('active');
                });

                // Close menu when clicking on a link
                const navLinks = document.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        navMenu.classList.remove('active');
                        menuToggle.classList.remove('active');
                    });
                });

                // Close menu when clicking outside
                document.addEventListener('click', function(event) {
                    const isClickInsideNav = navMenu.contains(event.target) || menuToggle.contains(event.target);

                    if (!isClickInsideNav && navMenu.classList.contains('active')) {
                        navMenu.classList.remove('active');
                        menuToggle.classList.remove('active');
                    }
                });

                // Close menu on window resize (if resizing to larger screen)
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768) {
                        navMenu.classList.remove('active');
                        menuToggle.classList.remove('active');
                    }
                });
            }
        });
    </script>
</header>
