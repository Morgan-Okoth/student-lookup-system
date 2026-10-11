<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Results') — Kisii University</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand img {
            height: 48px;
        }
        .site-header {
            background: linear-gradient(135deg, #1a3a5c 0%, #2d6a4f 100%);
        }
        .site-header .navbar-brand-text {
            line-height: 1.1;
        }
        .site-header .university-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
        }
        .site-header .university-motto {
            font-size: 0.72rem;
            color: #c8e6c9;
            letter-spacing: 0.04em;
        }
        .site-footer {
            background: #1a3a5c;
            color: #a8c4df;
            font-size: 0.82rem;
        }
        .logo-placeholder {
            width: 52px;
            height: 52px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            color: #1a3a5c;
            border: 2px solid #c8e6c9;
            flex-shrink: 0;
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Top Navigation -->
    <header class="site-header shadow-sm">
        <div class="container">
            <nav class="navbar navbar-dark py-2">
                <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="{{ route('results.lookup') }}">
                    <div class="logo-placeholder">KU</div>
                    <div class="navbar-brand-text">
                        <div class="university-name">Kisii University</div>
                        <div class="university-motto">Empowering Minds, Transforming Lives</div>
                    </div>
                </a>
                <span class="text-white-50 small d-none d-md-block">
                    <i class="bi bi-mortarboard-fill me-1"></i> Student Results Portal
                </span>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer mt-auto py-3">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} Kisii University. All rights reserved. &mdash; Student Results Lookup System</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmXN7F5bq6MHPZkJV6TKtFaKYw8"
            crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
