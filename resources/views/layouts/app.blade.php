<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Inventory')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">



    <style>
        :root {
            --green:#03624C;
            --teal:#0fb9b1;
            --mint:#E9FFFA;
            --blue:#4facfe;
            --bg:#f7faf9;
            --glass:rgba(255,255,255,.75);
        }

        body {
            background: radial-gradient(circle at top left,#e9fffa,#f7faf9);
            font-family: 'Instrument Sans', system-ui, sans-serif;
            overflow-x: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            width: 250px;
            height: 100vh;
            background: linear-gradient(180deg,var(--green),#024c3d);
            color: white;
            padding: 20px 0;
            animation: slideIn .6s ease;
        }

        .sidebar .logo {
            font-size: 22px;
            font-weight: 800;
            padding: 0 24px 20px;
            letter-spacing: .5px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: rgba(255,255,255,.8);
            text-decoration: none;
            transition: all .25s ease;
            border-left: 4px solid transparent;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,.12);
            color: white;
            transform: translateX(4px);
        }

        .sidebar a.active {
            background: rgba(255,255,255,.18);
            border-left-color: var(--teal);
            color: white;
        }

        /* ===== MAIN ===== */
        .main-content {
            margin-left: 250px;
            padding: 32px;
            animation: fadeUp .6s ease;
        }

        /* ===== CARDS ===== */
        .custom-card {
            background: var(--glass);
            backdrop-filter: blur(14px);
            border-radius: 18px;
            padding: 24px;
            border: 1px solid rgba(255,255,255,.5);
            box-shadow: 0 20px 40px rgba(0,0,0,.06);
            transition: all .3s ease;
        }

        .custom-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 30px 60px rgba(0,0,0,.12);
        }

        /* ===== TITLES ===== */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .7px;
            text-transform: uppercase;
            color: var(--green);
            margin-bottom: 14px;
        }

        /* ===== BUTTONS ===== */
        .btn-primary {
            background: linear-gradient(135deg,var(--green),var(--teal));
            border: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all .25s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(15,185,177,.4);
        }

        /* ===== TABLE ===== */
        table tr {
            transition: all .2s ease;
        }

        table tbody tr:hover {
            background: linear-gradient(90deg,#e9fffa,#ffffff);
        }
        .avatar {
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.list-group-item {
    border-color: rgba(0,0,0,.08);
}

.badge {
    font-weight: 500;
}

.form-check-input:checked {
    background-color: var(--green);
    border-color: var(--green);
}

.btn-group .btn {
    border-radius: 8px !important;
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
            from { opacity:0; transform:translateY(20px); }
            to { opacity:1; transform:none; }
        }

        @keyframes slideIn {
            from { transform:translateX(-100%); }
            to { transform:none; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo">
        <i class="bi bi-box-seam"></i> Inventory
    </div>
    
    {{-- Dashboard link - visible to all authenticated users --}}
    <a href="{{ route('dashboard') }}" class="{{ request()->is('/') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    
    {{-- Products link - visible to users with 'view-products' permission --}}
    @can('view-products')
    <a href="{{ route('products.index') }}" class="{{ request()->is('products*') ? 'active' : '' }}">
        <i class="bi bi-box"></i> Products
    </a>
    @endcan
    
    {{-- Orders link --}}
    <a href="{{ route('orders.index') }}" class="{{ request()->is('orders*') ? 'active' : '' }}">
        <i class="bi bi-receipt"></i> Orders
    </a>
    
    {{-- Warehouses link - visible to users with 'view-warehouses' permission --}}
    @can('view-warehouses')
    <a href="{{ route('warehouses.index') }}" class="{{ request()->is('warehouses*') ? 'active' : '' }}">
        <i class="bi bi-building"></i> Warehouses
    </a>
    @endcan
    
    {{-- Analytics link - visible to users with 'view-analytics' permission --}}
    @can('view-analytics')
    <a href="{{ route('analytics') }}" class="{{ request()->is('analytics*') ? 'active' : '' }}">
        <i class="bi bi-graph-up"></i> Analytics
    </a>
    @endcan
    
    {{-- Users link - visible only to users with 'manage-users' permission (admins only) --}}
    @can('manage-users')
    <a href="{{ route('users.index') }}" class="{{ request()->is('users*') ? 'active' : '' }}">
        <i class="bi bi-people"></i> Users
    </a>
    @endcan
    
    <a href="{{ route('logout') }}"
       onclick="event.preventDefault();document.getElementById('logout-form').submit();">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST">@csrf</form>
</div>

<div class="main-content">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>