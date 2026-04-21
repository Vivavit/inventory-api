<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Inventory')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('images/logo_white.png') }}" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@vite(['resources/css/app.css', 'resources/css/features/layout.css', 'resources/js/app.js', 'resources/js/features/layout.js'])
    @stack('styles')
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar" aria-label="Primary navigation">
        <div class="logo" id="logo-toggle" title="Toggle sidebar">
            <img src="{{ asset('storage/products/logo_white.png') }}" alt="Cam Inventory logo">
            <span class="logo-text">Cam Inventory</span>
        </div>
        <nav>
            <div class="nav-group">
                <p class="nav-group-title">Overview</p>
                <a href="{{ route('dashboard') }}" class="{{ request()->is('/') ? 'active' : '' }}" title="Dashboard">
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
                <p class="nav-group-title">Purchasing</p>
                @can('manage-inventory')
                <a href="{{ route('purchase-orders.index') }}" class="{{ request()->is('purchase-orders*') ? 'active' : '' }}" title="Purchase Orders">
                    <i class="bi bi-clipboard-check"></i>
                    <span class="link-text">Purchase Orders</span>
                </a>
                <a href="{{ route('supplier-purchases.index') }}" class="{{ request()->is('supplier-purchases*') ? 'active' : '' }}" title="Supplier Purchases">
                    <i class="bi bi-truck"></i>
                    <span class="link-text">Supplier Purchases</span>
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

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>

    <!-- TOP HEADER -->
    <div class="top-header" id="top-header">
        <div style="display: flex; align-items: center; gap: 12px;">
            <button class="hamburger-menu" id="hamburger-menu" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div>
                welcome back, <strong>{{ auth()->user()->name ?? 'User' }}</strong>
            </div>
        </div>
        <div class="header-right">
            <div class="search-bar">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search..." id="search-input" aria-label="Search products">
            </div>
            <button class="header-icon-btn" id="notifications-btn" title="Notifications" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                <span class="notification-badge">3</span>
            </button>
            <button class="header-icon-btn" id="theme-toggle" title="Toggle theme" aria-label="Toggle dark mode">
                <i class="bi bi-moon"></i>
            </button>
            <div class="user-avatar" id="user-menu-btn" title="{{ auth()->user()->name ?? 'User' }}" role="button" tabindex="0">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content" id="main-content">
        @yield('content')
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
