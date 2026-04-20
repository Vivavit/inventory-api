<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Inventory')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])



    <style>
        :root {
            /* ===== COLORS ===== */
            --green: #03624C;           /* Primary - dark teal */
            --teal: #0fb9b1;            /* Secondary - accent */
            --accent: #0fb9b1;          /* Active accent */
            --accent-soft: rgba(15,185,177,.14);
            --surface: #ffffff;         /* Card surface */
            --surface-soft: #f7fbf9;    /* Subtle surface */
            --surface-strong: #edf7f2;  /* Input background */
            --text: #18242b;
            --muted: #6c7884;
            --bg: #f4fbfa;              /* Main background */
            --bg-soft: #eaf6f3;
            --glass: rgba(255,255,255,.82); /* Glassmorphism */
            
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
            --shadow-sm: 0 8px 24px rgba(0,0,0,.05);
            --shadow-md: 0 12px 32px rgba(0,0,0,.08);
            --shadow-lg: 0 22px 55px rgba(0,0,0,.12);
            --shadow-hover: 0 16px 40px rgba(15,185,177,.2);
            
            /* ===== BORDER RADIUS ===== */
            --radius-sm: 6px;
            --radius-md: 14px;
            --radius-lg: 22px;
            --radius-full: 9999px;
            
            /* ===== TRANSITIONS ===== */
            --transition-fast: all .15s ease;
            --transition-base: all .25s ease;
            --transition-slow: all .35s ease;
            
            /* ===== SIDEBAR ===== */
            --sidebar-width: 250px;
            --sidebar-width-collapsed: 82px;
            
            /* ===== HEADER ===== */
            --header-height: 64px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at top left, #effdf8, #f4fbfa 40%, #ffffff 100%);
            font-family: var(--font-display);
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            color: var(--text);
        }

        .dark body {
            background: radial-gradient(circle at top left, #141414, #111111 45%, #171717 100%);
            color: #e8ecef;
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
            transition: width 0.35s ease, transform 0.35s ease;
            z-index: 100;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 4px 0 32px rgba(0,0,0,.08);
        }

        .sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }

        .sidebar .logo {
            font-size: var(--font-size-xl);
            font-weight: var(--fw-extrabold);
            padding-left: 16px;
            letter-spacing: .5px;
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            transition: all 0.35s ease;
            white-space: nowrap;
            cursor: pointer;
            border-radius: var(--radius-md);
            margin: 0 var(--spacing-lg) var(--spacing-lg);
            user-select: none;
            position: relative;
            color: white;
        }

        .sidebar.collapsed .logo {
            padding: var(--spacing-lg);
            justify-content: center;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .sidebar .logo img {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            object-fit: contain;
            background: rgba(255,255,255,.12);
            padding: 4px;
        }

        .sidebar .logo .logo-text {
            transition: opacity 0.3s ease, transform 0.3s ease;
            overflow: hidden;
        }

        .sidebar.collapsed .logo-text {
            opacity: 0;
            width: 0;
            transform: translateX(-10px);
        }

        .sidebar .toggle-btn {
            position: absolute;
            top: var(--spacing-xl);
            right: 12px;
            background: rgba(255,255,255,.16);
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
            background: rgba(255,255,255,.24);
            transform: scale(1.05);
        }

        .sidebar nav {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            padding: 0 var(--spacing-lg);
        }

        .sidebar .nav-group {
            margin-top: var(--spacing-lg);
        }

        .sidebar .nav-group-title {
            font-size: var(--font-size-xs);
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255,255,255,.65);
            margin-bottom: var(--spacing-sm);
            padding-left: 12px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: 12px 16px;
            color: rgba(255,255,255,.88);
            text-decoration: none;
            transition: all 0.25s ease;
            border-left: 4px solid transparent;
            border-radius: 12px;
            white-space: nowrap;
        }

        .sidebar.collapsed a {
            padding: var(--spacing-lg);
            justify-content: center;
        }

        .sidebar a i {
            font-size: var(--font-size-lg);
            flex-shrink: 0;
            min-width: 24px;
            text-align: center;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,.12);
            color: white;
            transform: translateX(3px);
        }

        .sidebar.collapsed a:hover {
            border-radius: var(--radius-md);
            transform: none;
        }

        .sidebar a.active {
            background: rgba(255,255,255,.18);
            border-left-color: var(--accent);
            color: white;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.08);
        }

        .sidebar .link-text {
            transition: opacity 0.25s ease, transform 0.25s ease;
            overflow: hidden;
        }

        .sidebar.collapsed .link-text {
            opacity: 0;
            width: 0;
            transform: translateX(-12px);
        }

        /* ===== TOP HEADER (FIXED) ===== */
        .top-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background: rgba(255,255,255,.95);
            backdrop-filter: blur(14px);
            box-shadow: 0 10px 34px rgba(0, 0, 0, .06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--spacing-lg);
            z-index: 90;
            gap: var(--spacing-lg);
            transition: left 0.35s ease;
        }

        .sidebar.collapsed ~ .top-header {
            left: var(--sidebar-width-collapsed);
        }

        .hamburger-menu {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 24px;
            height: 24px;
            cursor: pointer;
            padding: 4px;
        }

        .hamburger-menu span {
            width: 100%;
            height: 2px;
            background: var(--green);
            border-radius: 1px;
            transition: all 0.3s ease;
        }

        .top-header .search-bar {
            flex: 1;
            max-width: 300px;
            position: relative;
        }

        .top-header .search-bar input {
            width: 100%;
            padding: 8px 12px;
            padding-left: 34px;
            border: 1px solid rgba(22, 40, 56, .12);
            border-radius: var(--radius-full);
            font-size: var(--font-size-sm);
            transition: var(--transition-base);
            background: var(--surface-strong);
            color: var(--text);
        }

        .top-header .search-bar input:focus {
            outline: none;
            border-color: var(--accent);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(15,185,177,.12);
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
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            border: none;
            background: var(--surface-soft);
            color: var(--text);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-lg);
            transition: var(--transition-base);
            position: relative;
        }

        .top-header .header-icon-btn:hover {
            background: var(--surface-strong);
            transform: translateY(-1px);
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
            transition: margin-left 0.35s ease;
            min-height: calc(100vh - var(--header-height));
            position: relative;
            z-index: auto;
            background: var(--bg);
            padding: var(--spacing-2xl);
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-width-collapsed);
        }

        /* ===== CARDS ===== */
        .custom-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            border: 1px solid rgba(115, 134, 141, .12);
            box-shadow: var(--shadow-sm);
            transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
        }

        .custom-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .card-stat {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            border: 1px solid rgba(115, 134, 141, .12);
            box-shadow: var(--shadow-sm);
            transition: transform .25s ease, box-shadow .25s ease;
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
            color: var(--accent);
            margin-bottom: var(--spacing-lg);
            margin-top: var(--spacing-xl);
        }

        .section-title:first-child {
            margin-top: 0;
        }

        .settings-block {
            display: grid;
            gap: var(--spacing-lg);
        }

        .settings-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--spacing-lg);
            padding: 18px 20px;
            background: var(--surface-soft);
            border: 1px solid rgba(115, 134, 141, .12);
            border-radius: var(--radius-md);
        }

        .settings-label {
            font-size: 0.95rem;
            font-weight: var(--fw-semibold);
            margin-bottom: 4px;
            color: var(--text);
        }

        .accent-options {
            justify-content: flex-end;
        }

        .accent-chip {
            min-width: 82px;
            padding: 8px 14px;
            border-radius: var(--radius-full);
            border: 1px solid rgba(15, 185, 177, .18);
            background: var(--surface);
            color: var(--text);
            transition: all 0.2s ease;
        }

        .accent-chip.active,
        .accent-chip:hover {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }

        .text-muted {
            color: var(--muted) !important;
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
            position: relative;
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: var(--radius-md);
            cursor: pointer;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .btn-primary:hover {
            background: #0aa596;
            transform: translateY(-1px);
            box-shadow: var(--shadow-hover);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-secondary {
            background: var(--surface-soft);
            color: var(--text);
            border: 1px solid rgba(22, 40, 56, .08);
        }

        .btn-secondary:hover {
            background: var(--surface-strong);
            transform: translateY(-1px);
            color: var(--text);
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
            background: var(--surface-soft);
            border: 1px solid rgba(115, 134, 141, .12);
            padding: var(--spacing-lg);
            font-weight: var(--fw-bold);
            color: var(--accent);
            text-align: left;
            font-size: var(--font-size-sm);
        }

        table tbody td {
            padding: var(--spacing-lg);
            border: 1px solid rgba(115, 134, 141, .12);
        }

        table tbody tr:hover {
            background: var(--accent-soft);
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

            .hamburger-menu {
                display: flex;
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
<div class="sidebar" id="sidebar" aria-label="Primary navigation">
    <div class="logo" id="logo-toggle" title="Toggle sidebar">
        <img src="{{ asset('storage/products/logo_white.png') }}" alt="Cam Inventory logo">
        <span class="logo-text">Cam Inventory</span>
    </div>
    <nav>
        <div class="nav-group">
            <p class="nav-group-title">Overview</p>
            <a href="{{ route('dashboard') }}" class="{{ request()->is('/') ? 'active' : '' }}" title="Dashboard" aria-current="page">
                <i class="bi bi-speedometer2"></i>
                <span class="link-text">Dashboard</span>
            </a>
        </div>

        <div class="nav-group">
            <p class="nav-group-title">Inventory</p>
            @can('view-products')
            <a href="{{ route('products.index') }}" class="{{ request()->is('products*') ? 'active' : '' }}" title="Products">
                <i class="bi bi-box-seam"></i>
                <span class="link-text">Products</span>
            </a>
            @endcan
            <a href="{{ route('orders.index') }}" class="{{ request()->is('orders*') ? 'active' : '' }}" title="Orders">
                <i class="bi bi-receipt"></i>
                <span class="link-text">Orders</span>
            </a>
            @can('view-warehouses')
            <a href="{{ route('warehouses.index') }}" class="{{ request()->is('warehouses*') ? 'active' : '' }}" title="Warehouses">
                <i class="bi bi-building"></i>
                <span class="link-text">Warehouses</span>
            </a>
            @endcan
        </div>

        <div class="nav-group">
            <p class="nav-group-title">Management</p>
            @can('view-analytics')
            <a href="{{ route('analytics') }}" class="{{ request()->is('analytics*') ? 'active' : '' }}" title="Analytics">
                <i class="bi bi-graph-up"></i>
                <span class="link-text">Analytics</span>
            </a>
            @endcan
            @can('manage-users')
            <a href="{{ route('users.index') }}" class="{{ request()->is('users*') ? 'active' : '' }}" title="Users">
                <i class="bi bi-people"></i>
                <span class="link-text">Users</span>
            </a>
            @endcan
        </div>

        <div class="nav-group">
            <p class="nav-group-title">Preferences</p>
            <a href="{{ route('settings') }}" class="{{ request()->is('settings*') ? 'active' : '' }}" title="Settings">
                <i class="bi bi-sliders"></i>
                <span class="link-text">Settings</span>
            </a>
            <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
                <span class="link-text">Logout</span>
            </a>
        </div>
    </nav>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
</div>

<!-- TOP FIXED HEADER -->
<div class="top-header" id="top-header">
    <div style="display: flex; align-items: center; gap: 12px;">
        <div class="hamburger-menu" id="hamburger-menu">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div>
            welcome back, <strong>{{ auth()->user()->name ?? 'User' }}</strong>
        </div>
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
        <button class="header-icon-btn" id="theme-toggle" title="Toggle Theme">
            <i class="bi bi-moon"></i>
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

        // Hamburger menu for mobile
        const hamburgerMenu = document.getElementById('hamburger-menu');
        if (hamburgerMenu) {
            hamburgerMenu.addEventListener('click', function() {
                sidebar.classList.toggle('open');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !hamburgerMenu.contains(e.target) && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
            }
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

        // Dark mode toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = themeToggle.querySelector('i');
        const html = document.documentElement;

        const savedAccent = localStorage.getItem('accent');
        if (savedAccent) {
            html.style.setProperty('--accent', savedAccent);
            html.style.setProperty('--accent-soft', savedAccent + '22');
        }

        // Check for saved theme preference or default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            html.classList.add('dark');
            themeIcon.className = 'bi bi-sun';
        }

        themeToggle.addEventListener('click', function() {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                themeIcon.className = 'bi bi-moon';
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                themeIcon.className = 'bi bi-sun';
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>