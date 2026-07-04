<header class="header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            ☰
        </button>
        <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
    </div>
    
    <div class="header-right">
        <!-- Notifications -->
        <div class="notification-icon" style="position: relative;">
            <button style="background: none; border: none; font-size: 1.2rem; cursor: pointer; padding: 8px; border-radius: 50%; transition: background 0.3s ease;">
                🔔
            </button>
            <span style="position: absolute; top: 0; right: 0; background: #ef4444; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center;">
                3
            </span>
        </div>
        
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