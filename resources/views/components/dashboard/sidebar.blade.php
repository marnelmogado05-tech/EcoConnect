<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">ECO</div>
        <div class="sidebar-brand">EcoConnect</div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon">📊</span>
            <span>Dashboard</span>
        </a>
        
        <a href="#" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <span class="nav-icon">📝</span>
            <span>My Reports</span>
        </a>
        
        <a href="#" class="nav-item {{ request()->routeIs('reports.create') ? 'active' : '' }}">
            <span class="nav-icon">➕</span>
            <span>New Report</span>
        </a>
        
        <a href="#" class="nav-item {{ request()->routeIs('tracking.*') ? 'active' : '' }}">
            <span class="nav-icon">🔍</span>
            <span>Track Reports</span>
        </a>
        
        <div class="nav-section" style="margin-top: 20px; padding: 0 20px;">
            <div style="color: rgba(255, 255, 255, 0.5); font-size: 0.75rem; text-transform: uppercase; margin-bottom: 10px;">
                Account
            </div>
        </div>
        
        <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <span class="nav-icon">👤</span>
            <span>Profile</span>
        </a>
        
        <a href="#" class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
            <span class="nav-icon">⚙️</span>
            <span>Settings</span>
        </a>
        
        <form method="POST" action="{{ route('logout') }}" id="logoutForm" style="display: none;">
            @csrf
        </form>
        
        <a href="#" class="nav-item" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
            <span class="nav-icon">🚪</span>
            <span>Logout</span>
        </a>
    </nav>
</aside>