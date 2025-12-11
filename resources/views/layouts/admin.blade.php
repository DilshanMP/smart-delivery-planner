<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | Smart Delivery</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">

    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- User Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i> {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="{{ asset('build/assets/logo_s&c.png') }}"
             alt=""
             class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">Smart Delivery</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-white"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name }}</a>
                <small class="text-muted">{{ Auth::user()->roles->pluck('name')->first() ?? 'User' }}</small>
            </div>
        </div>

        <!-- Sidebar Menu -->

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <!-- ROUTE MANAGEMENT SECTION --><!-- ROUTE MANAGEMENT SECTION -->
<li class="nav-header">ROUTE MANAGEMENT</li>

<!-- Routes Main Menu -->
<li class="nav-item {{ Request::is('routes*') && !Request::is('routes/reports*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('routes*') && !Request::is('routes/reports*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-route text-info"></i>
        <p>
            Route Planning
            <i class="right fas fa-angle-left"></i>
            @php
                $pendingRoutes = \App\Models\Route::whereIn('status', ['planned', 'in_progress'])->count();
            @endphp
            @if($pendingRoutes > 0)
            <span class="badge badge-warning right">{{ $pendingRoutes }}</span>
            @endif
        </p>
    </a>
    <ul class="nav nav-treeview">
        <!-- All Routes -->
        <li class="nav-item">
            <a href="{{ route('routes.index') }}"
               class="nav-link {{ request()->routeIs('routes.index') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-info"></i>
                <p>All Routes</p>
            </a>
        </li>

        <!-- Plan New Route -->
        <li class="nav-item">
            <a href="{{ route('routes.create') }}"
               class="nav-link {{ request()->routeIs('routes.create') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-success"></i>
                <p>
                    Plan New Route
                    <span class="badge badge-success right">
                        <i class="fas fa-plus"></i>
                    </span>
                </p>
            </a>
        </li>

        <!-- Complete Routes -->
        <li class="nav-item">
            <a href="{{ route('routes.actual.index') }}"
               class="nav-link {{ request()->routeIs('routes.actual.*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-warning"></i>
                <p>
                    Complete Routes
                    @if($pendingRoutes > 0)
                    <span class="badge badge-warning right">{{ $pendingRoutes }}</span>
                    @endif
                </p>
            </a>
        </li>
    </ul>
</li>

<!-- Reports Section -->
<li class="nav-item {{ Request::is('routes/reports*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('routes/reports*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-line text-primary"></i>
        <p>
            Route Reports
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('routes.reports.dashboard') }}"
               class="nav-link {{ request()->routeIs('routes.reports.dashboard') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Reports Dashboard</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('routes.reports.estimatedVsActual') }}"
               class="nav-link {{ request()->routeIs('routes.reports.estimatedVsActual') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Estimated vs Actual</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('routes.reports.companyPerformance') }}"
               class="nav-link {{ request()->routeIs('routes.reports.companyPerformance') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Company Performance</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('routes.reports.driverPerformance') }}"
               class="nav-link {{ request()->routeIs('routes.reports.driverPerformance') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Driver Performance</p>
            </a>
        </li>
    </ul>
</li>


                <!-- USER MANAGEMENT SECTION -->
                @canany(['view users', 'view roles'])
                <li class="nav-header">USER MANAGEMENT</li>
                @endcanany

                <!-- Users -->
                @can('view users')
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Users
                            @can('create users')
                            <span class="badge badge-info right">Manage</span>
                            @endcan
                        </p>
                    </a>
                </li>
                @endcan

                <!-- Roles & Permissions -->
                @can('view roles')
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <p>
                            Roles & Permissions
                            @can('create roles')
                            <span class="badge badge-warning right">Admin</span>
                            @endcan
                        </p>
                    </a>
                </li>
                @endcan

                <!-- COMPANY & WAREHOUSE SECTION -->
                @canany(['view companies', 'view warehouses'])
                <li class="nav-header">INFRASTRUCTURE</li>
                @endcanany

                <!-- Companies -->
                @can('view companies')
                <li class="nav-item">
                    <a href="{{ route('companies.index') }}" class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-building"></i>
                        <p>Companies</p>
                    </a>
                </li>
                @endcan

                <!-- Warehouses -->
                @can('view warehouses')
                <li class="nav-item">
                    <a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>Warehouses</p>
                    </a>
                </li>
                @endcan

                <!-- FLEET MANAGEMENT SECTION -->
                @canany(['view vehicles', 'view drivers'])
                <li class="nav-header">FLEET MANAGEMENT</li>
                @endcanany

                <!-- Vehicles -->
                @can('view vehicles')
                <li class="nav-item">
                    <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>Vehicles</p>
                    </a>
                </li>
                @endcan

                <!-- Drivers -->
                @can('view drivers')
                <li class="nav-item">
                    <a href="{{ route('drivers.index') }}" class="nav-link {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p>Drivers</p>
                    </a>
                </li>
                @endcan
{{--
    CLEAN ROUTE MANAGEMENT SIDEBAR MENU (Without Reports)
    Add this to your main sidebar (layouts/admin.blade.php or partials/sidebar.blade.php)
    Location: Inside <ul class="nav nav-pills nav-sidebar flex-column">
--}}



{{-- REPORTS TEMPORARILY DISABLED
<li class="nav-item">
    <a href="#" class="nav-link" onclick="alert('Reports coming soon!')">
        <i class="nav-icon fas fa-chart-line text-primary"></i>
        <p>Route Reports <small>(Coming Soon)</small></p>
    </a>
</li>
--}}

{{-- PROFILE TEMPORARILY DISABLED
<li class="nav-item">
    <a href="#" class="nav-link" onclick="alert('Profile coming soon!')">
        <i class="nav-icon fas fa-user text-secondary"></i>
        <p>Profile <small>(Coming Soon)</small></p>
    </a>
</li>
--}}

{{-- Optional: Quick Stats Widget --}}
@if(Request::is('routes*'))
<li class="nav-item mt-2">
    <div class="info-box bg-gradient-info mx-2">
        <span class="info-box-icon"><i class="fas fa-route"></i></span>
        <div class="info-box-content">
            <span class="info-box-text">This Month</span>
            <span class="info-box-number">
                {{ \App\Models\Route::whereMonth('route_date', now()->month)->count() }} Routes
            </span>
        </div>
    </div>
</li>
@endif
{{--
                <!-- REPORTS SECTION -->
                @can('view reports')
                <li class="nav-header">REPORTS & ANALYTICS</li>

                <li class="nav-item {{ request()->is('reports*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('reports*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>
                            Reports
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Reports</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}?type=cost" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Cost Analysis</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}?type=route" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Route Performance</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}?type=vehicle" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Vehicle Utilization</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan --}}

                <!-- SYSTEM SECTION -->
                <li class="nav-header">SYSTEM</li>

                <!-- Logout -->
                <li class="nav-item">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                        <p>Logout</p>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">

                <!-- Flash Messages -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif

                @yield('content')
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; 2024 <a href="#">Akvora International</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

<script>
// Auto-hide alerts after 5 seconds
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

@stack('scripts')

</body>
</html>
