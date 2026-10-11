<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Kisii University Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body { background-color: #f0f4f8; }

        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1a3a5c 0%, #12294a 100%);
            width: 240px;
            flex-shrink: 0;
        }
        .admin-sidebar .sidebar-brand {
            background: rgba(0,0,0,0.2);
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .admin-sidebar .nav-link {
            color: #a8c4df;
            border-radius: 0.375rem;
            margin: 2px 8px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }
        .admin-sidebar .nav-link i { width: 20px; }

        .admin-topbar {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.6rem 1.5rem;
        }
        .admin-content {
            flex: 1;
            overflow-x: auto;
        }
        @media (max-width: 767px) {
            .admin-sidebar { width: 100%; min-height: auto; }
            .admin-wrapper { flex-direction: column !important; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="d-flex admin-wrapper">

    <!-- Sidebar -->
    <aside class="admin-sidebar d-flex flex-column">
        <div class="sidebar-brand">
            <div class="d-flex align-items-center gap-2">
                <div style="width:38px;height:38px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:#1a3a5c;font-size:.9rem;flex-shrink:0;">KU</div>
                <div>
                    <div style="color:#fff;font-size:.85rem;font-weight:700;line-height:1.2;">Kisii University</div>
                    <div style="color:#a8c4df;font-size:.68rem;">Admin Portal</div>
                </div>
            </div>
        </div>

        <nav class="mt-3 flex-grow-1">
            <ul class="nav flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                       href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}"
                       href="{{ route('admin.students.index') }}">
                        <i class="bi bi-people-fill me-2"></i> Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}"
                       href="{{ route('admin.courses.index') }}">
                        <i class="bi bi-book-fill me-2"></i> Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.results.*') ? 'active' : '' }}"
                       href="{{ route('admin.results.index') }}">
                        <i class="bi bi-clipboard2-data-fill me-2"></i> Results
                    </a>
                </li>
                <li class="mt-auto pt-3 border-top border-secondary mx-2">
                    <a class="nav-link text-danger-emphasis"
                       href="{{ route('results.lookup') }}" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-2"></i> View Public Portal
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main area -->
    <div class="admin-content d-flex flex-column">

        <!-- Top bar -->
        <div class="admin-topbar d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-muted fw-normal">@yield('page-title', 'Admin Panel')</h6>
            <div class="d-flex align-items-center gap-3">
                <span class="small text-muted">
                    <i class="bi bi-person-circle me-1"></i>
                    {{ auth()->user()->name ?? 'Administrator' }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Flash messages -->
        <div class="px-4 pt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <!-- Page content -->
        <div class="p-4 flex-grow-1">
            @yield('content')
        </div>

        <footer class="text-center py-2 text-muted small border-top">
            &copy; {{ date('Y') }} Kisii University &mdash; Admin Portal
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmXN7F5bq6MHPZkJV6TKtFaKYw8"
        crossorigin="anonymous"></script>
@stack('scripts')
</body>
</html>
