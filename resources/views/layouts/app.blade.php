<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SGC') — F&C Chile SPA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>

{{-- ── Navbar principal ─────────────────────────────────── --}}
<nav class="navbar">
    <a href="{{ route('panel') }}" class="navbar-logo">
        <div class="navbar-logo-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <rect x="3"  y="3" width="4" height="18" fill="#0D2B5E"/>
                <rect x="10" y="3" width="4" height="18" fill="#0D2B5E"/>
                <rect x="17" y="3" width="4" height="18" fill="#0D2B5E"/>
            </svg>
        </div>
        <div>
            <div class="navbar-logo-text">F&C Chile SPA</div>
            <div class="navbar-logo-sub">Ingeniería &amp; Construcción</div>
        </div>
    </a>

    <div class="navbar-title">Control y Gestión Transversal</div>

    <div class="navbar-user">
        {{-- Avatar con iniciales --}}
        <div class="navbar-avatar" title="{{ session('usuario_nombre') }}">
            {{ strtoupper(substr(session('usuario_nombre', 'U'), 0, 1)) }}{{ strtoupper(substr(strstr(session('usuario_nombre', 'U '), ' '), 1, 1)) }}
        </div>
        <span>{{ session('usuario_nombre') }}</span>

        {{-- Botón logout --}}
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit"
                style="background:none;border:none;cursor:pointer;color:var(--blue-muted);padding:4px 6px;display:flex;align-items:center"
                title="Cerrar sesión">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                </svg>
            </button>
        </form>
    </div>
</nav>

{{-- ── Subnav ───────────────────────────────────────────── --}}
@hasSection('subnav')
<div class="subnav">
    @yield('subnav')
</div>
@endif

{{-- ── Contenido principal ──────────────────────────────── --}}
<main>
    @yield('content')
</main>

@stack('scripts')
<script>
    window.CSRF_TOKEN = '{{ csrf_token() }}';
    window.sgcFetch = (url, options = {}) => fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            ...(options.headers || {})
        },
        ...options
    });
</script>
</body>
</html>
