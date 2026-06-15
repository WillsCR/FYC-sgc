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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
    @stack('styles')
</head>
<body>

{{-- ── Navbar principal ─────────────────────────────────── --}}
<nav class="navbar">
    <a href="{{ route('panel') }}" class="navbar-logo"
       style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;
              padding:5px 2px;transition:opacity .15s;flex-shrink:0"
       onmouseover="this.style.opacity='.82'" onmouseout="this.style.opacity='1'">

        {{-- Ícono columna clásica --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="38" viewBox="0 0 60 76" fill="none" aria-hidden="true">
            <!-- capitel (parte superior) -->
            <rect x="2"  y="2"  width="56" height="7"  rx="2" fill="white"/>
            <rect x="8"  y="9"  width="44" height="4"  rx="1" fill="white"/>
            <!-- fuste (columnas internas) -->
            <rect x="10" y="13" width="8"  height="42" rx="2" fill="white"/>
            <rect x="22" y="13" width="8"  height="42" rx="2" fill="white"/>
            <rect x="34" y="13" width="8"  height="42" rx="2" fill="white"/>
            <rect x="46" y="13" width="4"  height="42" rx="2" fill="white"/>
            <!-- base -->
            <rect x="8"  y="55" width="44" height="4"  rx="1" fill="white"/>
            <rect x="2"  y="59" width="56" height="7"  rx="2" fill="white"/>
            <!-- base inferior -->
            <rect x="0"  y="68" width="60" height="5"  rx="2" fill="white"/>
        </svg>

        {{-- Texto --}}
        <div style="line-height:1.15">
            <div style="color:#ffffff;font-size:.95rem;font-weight:800;
                        letter-spacing:.04em;font-family:'Inter',sans-serif">
                F&amp;C CHILE SPA
            </div>
            <div style="color:rgba(255,255,255,.78);font-size:.6rem;font-weight:700;
                        letter-spacing:.1em;text-transform:uppercase;
                        font-family:'Inter',sans-serif">
                Ingeniería &amp; Construcción
            </div>
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
<div class="subnav">
    <a href="{{ route('panel') }}"         class="subnav-item {{ request()->routeIs('panel')      ? 'active' : '' }}">Inicio</a>
    <a href="{{ route('metricas') }}"       class="subnav-item {{ request()->routeIs('metricas')   ? 'active' : '' }}">Métricas</a>
    
    <a href="{{ route('planificacion.index') }}" class="subnav-item {{ request()->routeIs('planificacion*') ? 'active' : '' }}">Planificación</a>
    <a href="{{ route('minutas.index') }}" class="subnav-item {{ request()->routeIs('minutas')? 'active' : ''}}">Minutas</a>
    <a href="{{ route('sig.index') }}"     class="subnav-item {{ request()->routeIs('sig*') ? 'active' : '' }}">Información SIG</a>
<a href="{{ route('ambiente.index') }}" class="subnav-item {{ request()->routeIs('ambiente*') ? 'active' : '' }}">Medio Ambiente</a>
    <a href="{{ route('videos.index') }}"  class="subnav-item {{ request()->routeIs('videos*')   ? 'active' : '' }}">Videos</a>
    @if(session('es_admin'))
        <a href="{{ route('usuarios.index') }}" class="subnav-item {{ request()->routeIs('usuarios*') ? 'active' : '' }}">Usuarios</a>
        <a href="{{ route('areas.index') }}"    class="subnav-item {{ request()->routeIs('areas*')    ? 'active' : '' }}">Áreas</a>
    @endif
</div>

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
<style>
.toast-container { 
    position: fixed; 
    bottom: 24px; 
    right: 24px; 
    z-index: 9999; 
    max-width: 360px;
}
.toast-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 13px 16px;
    border-radius: 8px;
    font-size: .85rem;
    font-weight: 500;
    color: #fff;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
    animation: slideIn .3s cubic-bezier(.22,1,.36,1) forwards;
    word-break: break-word;
}
.toast-item.ok  { background: #2e7d32; }
.toast-item.err { background: #c62828; }
.toast-item.warning { background: #f57c00; }
.toast-item.info { background: #1976d2; }
.toast-item svg { flex-shrink: 0; width: 18px; height: 18px; }
@keyframes slideIn { 
    from { transform: translateX(400px); opacity: 0; } 
    to { transform: translateX(0); opacity: 1; } 
}
</style>

<div class="toast-container" id="toast-container"></div>

<script>
// Función global para mostrar notificaciones
function showToast(message, type = 'ok', duration = 4000) {
    const container = document.getElementById('toast-container');
    
    const icons = {
        ok: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>',
        err: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 20h20L12 2z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast-item ${type}`;
    toast.innerHTML = `${icons[type] || icons.info}<span>${message}</span>`;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut .3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}
</script>
</body>
</html>
