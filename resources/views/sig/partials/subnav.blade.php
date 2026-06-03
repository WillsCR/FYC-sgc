{{-- ── Sub-navegación del módulo SIG ──────────────────────────────────── --}}
<style>
.sig-subnav {
    background: var(--surface);
    border-bottom: 2px solid var(--border);
    padding: 0 20px;
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 0;
}
.sig-subnav-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 11px 18px;
    font-size: .78rem;
    font-weight: 600;
    color: var(--text-muted);
    text-decoration: none;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: color .15s, border-color .15s;
    white-space: nowrap;
}
.sig-subnav-item:hover {
    color: var(--navy);
    border-bottom-color: var(--border);
}
.sig-subnav-item.activo {
    color: var(--navy);
    border-bottom-color: var(--blue-accent);
    background: transparent;
}
.sig-subnav-item i {
    font-size: .8rem;
    opacity: .8;
}
</style>

<nav class="sig-subnav">
    <a href="{{ route('sig.index') }}"
       class="sig-subnav-item {{ request()->routeIs('sig.index') ? 'activo' : '' }}">
        <i class="fa-solid fa-newspaper"></i> Información SIG
    </a>
    <a href="{{ route('nc.index') }}"
       class="sig-subnav-item {{ request()->routeIs('nc.*') ? 'activo' : '' }}">
        <i class="fa-solid fa-triangle-exclamation"></i> No Conformidades
    </a>
    <a href="{{ route('control-instrumentos.index') }}"
       class="sig-subnav-item {{ request()->routeIs('control-instrumentos.*') ? 'activo' : '' }}">
        <i class="fa-solid fa-gauge"></i> Control de Instrumentos
    </a>
</nav>
