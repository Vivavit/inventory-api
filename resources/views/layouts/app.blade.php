<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Inventory')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/js/app.js'])



    <style>
        :root {
            /* ===== COLORS ===== */
            --green: #03624C;           /* Primary - dark teal */
            --teal: #0fb9b1;            /* Secondary - accent */
            --mint: #E9FFFA;            /* Light background */
            --blue: #4facfe;            /* Additional accent */
            --bg: #f7faf9;              /* Main background */
            --glass: rgba(255,255,255,.75); /* Glassmorphism */
            
            /* ===== SPACING ===== */
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing-md: 12px;
            --spacing-lg: 16px;
            --spacing-xl: 24px;
            --spacing-2xl: 32px;
            
            /* ===== TYPOGRAPHY ===== */
            --font-display: 'Instrument Sans', system-ui, sans-serif;
            --font-size-xs: 11px;
            --font-size-sm: 12px;
            --font-size-base: 14px;
            --font-size-lg: 16px;
            --font-size-xl: 18px;
            --font-size-2xl: 24px;
            --font-size-3xl: 32px;
            --fw-regular: 400;
            --fw-medium: 500;
            --fw-semibold: 600;
            --fw-bold: 700;
            --fw-extrabold: 800;
            
            /* ===== SHADOWS ===== */
            --shadow-sm: 0 4px 12px rgba(0,0,0,.04);
            --shadow-md: 0 8px 16px rgba(0,0,0,.08);
            --shadow-lg: 0 20px 40px rgba(0,0,0,.12);
            --shadow-xl: 0 30px 60px rgba(0,0,0,.15);
            --shadow-hover: 0 12px 30px rgba(15,185,177,.4);
            
            /* ===== BORDER RADIUS ===== */
            --radius-sm: 6px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --radius-full: 9999px;
            
            /* ===== TRANSITIONS ===== */
            --transition-fast: all .15s ease;
            --transition-base: all .25s ease;
            --transition-slow: all .35s ease;
            
            /* ===== SIDEBAR ===== */
            --sidebar-width: 250px;
            --sidebar-width-collapsed: 80px;
            
            /* ===== HEADER ===== */
            --header-height: 60px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: radial-gradient(circle at top left, #e9fffa, #f7faf9);
            font-family: var(--font-display);
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* ===== SIDEBAR (FULL HEIGHT) ===== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--green), #024c3d);
            color: white;
            padding: var(--spacing-lg) 0;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }

        .sidebar .logo {
            font-size: var(--font-size-xl);
            font-weight: var(--fw-extrabold);
            padding-left: 5px;
            letter-spacing: .5px;
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            cursor: pointer;
            border-radius: var(--radius-md);
            margin: 0 var(--spacing-lg) var(--spacing-lg);
            user-select: none;
            position: relative;
        }

        .sidebar.collapsed .logo {
            padding: var(--spacing-lg);
            justify-content: center;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .sidebar .logo i {
            font-size: var(--font-size-2xl);
            flex-shrink: 0;
        }

        .sidebar .logo .logo-text {
            transition: opacity 0.3s ease, transform 0.3s ease;
            overflow: hidden;
        }

        .sidebar.collapsed .logo-text {
            opacity: 0;
            width: 0;
            transform: translateX(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .sidebar .toggle-btn {
            position: absolute;
            top: var(--spacing-xl);
            right: 12px;
            background: rgba(255,255,255,.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-base);
            font-size: var(--font-size-lg);
        }

        .sidebar .toggle-btn:hover {
            background: rgba(255,255,255,.3);
            transform: scale(1.05);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-lg) var(--spacing-xl);
            color: rgba(255,255,255,.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            white-space: nowrap;
        }

        .sidebar.collapsed a {
            padding: var(--spacing-lg);
            justify-content: center;
        }

        .sidebar a i {
            font-size: var(--font-size-lg);
            flex-shrink: 0;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,.12);
            color: white;
            transform: translateX(4px);
        }

        .sidebar.collapsed a:hover {
            border-radius: var(--radius-md);
            transform: none;
        }

        .sidebar a.active {
            background: rgba(255,255,255,.18);
            border-left-color: var(--teal);
            color: white;
        }

        .sidebar .link-text {
            transition: opacity 0.3s ease, transform 0.3s ease;
            overflow: hidden;
        }

        .sidebar.collapsed .link-text {
            opacity: 0;
            width: 0;
            transform: translateX(-10px);
        }

        /* ===== TOP HEADER (FIXED) ===== */
        .top-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: white;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--spacing-lg);
            z-index: 950;
            gap: var(--spacing-lg);
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.collapsed ~ .top-header {
            left: var(--sidebar-width-collapsed);
        }

        .top-header .search-bar {
            flex: 1;
            max-width: 300px;
            position: relative;
        }

        .top-header .search-bar input {
            width: 100%;
            padding: 8px 12px;
            padding-left: 32px;
            border: 1px solid #e0e0e0;
            border-radius: var(--radius-full);
            font-size: var(--font-size-sm);
            transition: var(--transition-base);
            background: #f5f5f5;
        }

        .top-header .search-bar input:focus {
            outline: none;
            border-color: var(--green);
            background: white;
            box-shadow: 0 0 0 3px rgba(3, 98, 76, .1);
        }

        .top-header .search-bar i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: var(--font-size-sm);
            pointer-events: none;
        }

        .top-header .header-right {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            margin-left: auto;
        }

        .top-header .header-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            border: none;
            background: #f5f5f5;
            color: var(--green);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-lg);
            transition: var(--transition-base);
            position: relative;
        }

        .top-header .header-icon-btn:hover {
            background: var(--mint);
            transform: scale(1.05);
        }

        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ff4757;
            color: white;
            width: 18px;
            height: 18px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: var(--fw-bold);
            border: 2px solid white;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--green), var(--teal));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: var(--fw-bold);
            font-size: 12px;
            cursor: pointer;
            transition: var(--transition-base);
        }

        .user-avatar:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-hover);
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: calc(100vh - var(--header-height));
            position: relative;
            z-index: 10;
            background: radial-gradient(circle at top left, #e9fffa, #f7faf9);
            padding: var(--spacing-2xl);
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-width-collapsed);
        }

        /* ===== CARDS ===== */
        .custom-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            border: 1px solid #f0f0f0;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-base);
        }

        .custom-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }

        .card-stat {
            background: white;
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            border: 1px solid #f0f0f0;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-base);
            text-align: center;
        }

        .card-stat:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }

        .card-stat-icon {
            font-size: var(--font-size-3xl);
            margin-bottom: var(--spacing-sm);
        }

        .card-stat-value {
            font-size: var(--font-size-2xl);
            font-weight: var(--fw-bold);
            color: var(--green);
            margin: 0;
        }

        .card-stat-label {
            font-size: var(--font-size-sm);
            color: #999;
            margin: var(--spacing-sm) 0 0;
        }

        .card-alert {
            background: linear-gradient(135deg, #fff5f5, #ffe9e9);
            border: 1px solid #ffcccb;
            border-left: 4px solid #ff6b6b;
        }

        /* ===== TITLES ===== */
        .section-title {
            font-size: var(--font-size-sm);
            font-weight: var(--fw-bold);
            letter-spacing: .7px;
            text-transform: uppercase;
            color: var(--green);
            margin-bottom: var(--spacing-lg);
            margin-top: var(--spacing-xl);
        }

        .section-title:first-child {
            margin-top: 0;
        }

        .page-title {
            font-size: var(--font-size-3xl);
            font-weight: var(--fw-bold);
            color: var(--green);
            margin: 0 0 var(--spacing-lg);
        }

        /* ===== BUTTONS ===== */
        .btn {
            border-radius: var(--radius-md);
            font-weight: var(--fw-semibold);
            transition: var(--transition-base);
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--green), var(--teal));
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
            color: white;
            text-decoration: none;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: var(--green);
            border: none;
        }

        .btn-secondary:hover {
            background: var(--mint);
            transform: translateY(-2px);
            color: var(--green);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: var(--font-size-sm);
        }

        .btn-lg {
            padding: 12px 24px;
            font-size: var(--font-size-lg);
        }

        /* ===== TABLE ===== */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        table tr {
            transition: var(--transition-base);
        }

        table thead th {
            background: #f9f9f9;
            border: 1px solid #f0f0f0;
            padding: var(--spacing-lg);
            font-weight: var(--fw-bold);
            color: var(--green);
            text-align: left;
            font-size: var(--font-size-sm);
        }

        table tbody td {
            padding: var(--spacing-lg);
            border: 1px solid #f0f0f0;
        }

        table tbody tr:hover {
            background: var(--mint);
        }

        /* ===== BADGES ===== */
        .avatar {
            font-weight: var(--fw-semibold);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
        }

        .list-group-item {
            border-color: rgba(0,0,0,.08);
        }

        .badge {
            font-weight: var(--fw-semibold);
            padding: 4px 8px;
            border-radius: var(--radius-sm);
            font-size: var(--font-size-xs);
        }

        .badge-success {
            background-color: rgba(3, 98, 76, .15);
            color: var(--green);
        }

        .badge-warning {
            background-color: rgba(255, 112, 67, .15);
            color: #ff7043;
        }

        .badge-danger {
            background-color: rgba(255, 107, 107, .15);
            color: #ff6b6b;
        }

        /* ===== FORM ELEMENTS ===== */
        .form-check-input:checked {
            background-color: var(--green);
            border-color: var(--green);
        }

        .form-control {
            border-color: #ddd;
            border-radius: var(--radius-md);
            transition: var(--transition-base);
        }

        .form-control:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(3, 98, 76, .1);
        }

        .btn-group .btn {
            border-radius: var(--radius-md) !important;
        }

        .btn-group .btn:not(:last-child) {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .btn-group .btn:not(:first-child) {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: none; }
        }

        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: none; }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: none; }
        }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            :root {
                --header-height: 56px;
            }

            .sidebar {
                width: var(--sidebar-width-collapsed);
            }

            .sidebar .logo-text,
            .sidebar .link-text {
                display: none;
            }

            .top-header {
                left: var(--sidebar-width-collapsed);
            }

            .main-content {
                margin-left: var(--sidebar-width-collapsed);
                padding: var(--spacing-lg);
            }
        }

        @media (max-width: 768px) {
            :root {
                --header-height: 52px;
                --spacing-xl: 16px;
                --spacing-2xl: 20px;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .top-header {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                padding: var(--spacing-lg);
            }

            .page-title {
                font-size: var(--font-size-2xl);
            }
        }

        @media (max-width: 480px) {
            :root {
                --header-height: 48px;
                --spacing-xl: 12px;
                --spacing-lg: 12px;
            }

            .top-header {
                padding: 0 var(--spacing-sm);
            }

            .top-header .search-bar {
                max-width: 150px;
            }

            .top-header .header-icon-btn {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }

            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 10px;
            }

            .main-content {
                padding: var(--spacing-lg);
            }
        }
    </style>
</head>
<body>

<!-- SIDEBAR (FULL HEIGHT) -->
<div class="sidebar" id="sidebar">
    
    <div class="logo" id="logo-toggle" title="Click to toggle sidebar">
        <i class="bi bi-box"></i>
        <span class="logo-text">Cam Inventory</span>
    </div>
    
    {{-- Dashboard link - visible to all authenticated users --}}
    <a href="{{ route('dashboard') }}" class="{{ request()->is('/') ? 'active' : '' }}" title="Dashboard">
        <i class="bi bi-speedometer2"></i>
        <span class="link-text">Dashboard</span>
    </a>
    
    {{-- Products link - visible to users with 'view-products' permission --}}
    @can('view-products')
    <a href="{{ route('products.index') }}" class="{{ request()->is('products*') ? 'active' : '' }}" title="Products">
        <i class="bi bi-box"></i>
        <span class="link-text">Products</span>
    </a>
    @endcan
    
    {{-- Orders link --}}
    <a href="{{ route('orders.index') }}" class="{{ request()->is('orders*') ? 'active' : '' }}" title="Orders">
        <i class="bi bi-receipt"></i>
        <span class="link-text">Orders</span>
    </a>
    
    {{-- Warehouses link - visible to users with 'view-warehouses' permission --}}
    @can('view-warehouses')
    <a href="{{ route('warehouses.index') }}" class="{{ request()->is('warehouses*') ? 'active' : '' }}" title="Warehouses">
        <i class="bi bi-building"></i>
        <span class="link-text">Warehouses</span>
    </a>
    @endcan
    
    {{-- Analytics link - visible to users with 'view-analytics' permission --}}
    @can('view-analytics')
    <a href="{{ route('analytics') }}" class="{{ request()->is('analytics*') ? 'active' : '' }}" title="Analytics">
        <i class="bi bi-graph-up"></i>
        <span class="link-text">Analytics</span>
    </a>
    @endcan
    
    {{-- Users link - visible only to users with 'manage-users' permission (admins only) --}}
    @can('manage-users')
    <a href="{{ route('users.index') }}" class="{{ request()->is('users*') ? 'active' : '' }}" title="Users">
        <i class="bi bi-people"></i>
        <span class="link-text">Users</span>
    </a>
    @endcan
    
    <a href="{{ route('logout') }}" 
       onclick="event.preventDefault();document.getElementById('logout-form').submit();"
       title="Logout">
        <i class="bi bi-box-arrow-right"></i>
        <span class="link-text">Logout</span>
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
</div>

<!-- TOP FIXED HEADER -->
<div class="top-header" id="top-header">
    <div>
        welcome back, <strong>{{ auth()->user()->name ?? 'User' }}</strong>
    </div>
    <div class="header-right">
        <div class="search-bar">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search..." id="search-input">
        </div>
        <button class="header-icon-btn" id="notifications-btn" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="notification-badge">3</span>
        </button>
        <div class="user-avatar" id="user-menu-btn" title="{{ auth()->user()->name ?? 'User' }}">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content" id="main-content">
    @yield('content')
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const logoToggle = document.getElementById('logo-toggle');
        const savedState = localStorage.getItem('sidebar-expanded');
        
        // Initialize sidebar state
        if (savedState === 'false') {
            sidebar.classList.add('collapsed');
        }
        
        // Logo click to toggle sidebar
        logoToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar-expanded', !sidebar.classList.contains('collapsed'));
        });

        // Search functionality
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    window.location.href = '/products?search=' + encodeURIComponent(this.value);
                }
            });
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>