<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - @yield('title')</title>
    
    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            background-color: #f3f4f6; 
            min-height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            overflow-x: hidden;
        }
        
        /* --- SIDEBAR STYLES --- */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #111827; /* Dark Navy */
            color: #fff;
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header { 
            padding: 20px; 
            background: #0f1623; 
            border-bottom: 1px solid #1f2937; 
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-menu {
            padding: 1rem 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar a {
            color: #9ca3af; /* Light Gray */
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 14px 25px;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }

        .sidebar a:hover {
            background: #1f2937;
            color: #fff;
        }

        .sidebar a.active {
            background: #1f2937;
            color: #fff;
            border-left-color: #3b82f6; /* Blue Accent */
        }

        .sidebar i { 
            width: 24px; 
            margin-right: 12px; 
            text-align: center; 
        }
        
        /* --- MAIN CONTENT STYLES --- */
        .main-content { 
            margin-left: 260px; 
            padding: 20px; 
            transition: all 0.3s ease;
        }

        .top-bar {
            background: #fff;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        /* --- RESPONSIVE --- */
        @media(max-width: 768px) {
            .sidebar { margin-left: -260px; }
            .sidebar.active { margin-left: 0; }
            .main-content { margin-left: 0; }
            .overlay {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }
            .overlay.active { display: block; }
        }
    </style>
</head>
<body>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0 fw-bold text-white">
            <i class="fas fa-shield-alt text-warning me-2"></i>ADMIN
        </h4>
    </div>
    
    <div class="sidebar-menu">
        <small class="text-uppercase px-4 fw-bold mb-2 d-block" style="font-size: 0.7rem; letter-spacing: 1px; color">Analytics</small>
        
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i> Dashboard
        </a>

        <small class="text-uppercase px-4 fw-bold mt-4 mb-2 d-block" style="font-size: 0.7rem; letter-spacing: 1px;">Management</small>
        
        <a href="{{ route('tournaments.index') }}">
            <i class="fas fa-trophy"></i> Tournaments
        </a>
        
        <a href="{{ route('game.list') }}">
            <i class="fas fa-basketball-ball"></i> Game Sessions
        </a>
        
        {{-- Placeholder for future features --}}
        <a href="#">
            <i class="fas fa-users"></i> Users <span class="badge bg-secondary ms-auto" style="font-size: 0.6rem;">SOON</span>
        </a>

        <small class="text-uppercase px-4 fw-bold mt-4 mb-2 d-block" style="font-size: 0.7rem; letter-spacing: 1px;">Reports</small>
        
        <a href="{{ route('admin.reports.financial') }}" class="{{ request()->routeIs('admin.reports.financial') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Financial Report
        </a>

        <a href="{{ route('admin.reports.users') }}" class="{{ request()->routeIs('admin.reports.users') ? 'active' : '' }}">
            <i class="fas fa-users"></i> User Report
        </a>

        <a href="{{ route('admin.reports.games') }}" class="{{ request()->routeIs('admin.reports.games') ? 'active' : '' }}">
            <i class="fas fa-basketball-ball"></i> Game Report
        </a>

        <a href="{{ route('admin.reports.tournaments') }}" class="{{ request()->routeIs('admin.reports.tournaments') ? 'active' : '' }}">
            <i class="fas fa-trophy"></i> Tournament Report
        </a>
    </div>

    <div class="p-3 border-top border-secondary">
        <a href="/" class="text-warning fw-bold">
            <i class="fas fa-arrow-left"></i> Back to Website
        </a>
        
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-link text-danger text-decoration-none w-100 text-start d-flex align-items-center px-3 py-2">
                <i class="fas fa-sign-out-alt me-3"></i> Logout
            </button>
        </form>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <div class="d-flex align-items-center">
            <button class="btn btn-light d-md-none me-3" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h4 class="m-0 fw-bold text-secondary">@yield('header', 'Dashboard')</h4>
        </div>

        <div class="d-flex align-items-center">
            <div class="text-end me-3 d-none d-sm-block">
                <div class="fw-bold text-dark">{{ Auth::user()->name ?? 'Admin' }}</div>
                <div class="small" style="font-size: 0.75rem;">Administrator</div>
            </div>
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px;">
                {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
            </div>
        </div>
    </div>

    @yield('content')
</div>

{{-- SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('overlay').classList.toggle('active');
    }
</script>
@stack('scripts')

</body>
</html>