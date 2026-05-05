@extends('layouts.app')
@section('title', 'Panel principal')

@section('content')
<div class="panel-body">

    @if(session('sin_permiso_carpeta'))
    <div style="background:#FEF2F2;border-left:4px solid #DC2626;color:#991B1B;
                padding:11px 16px;border-radius:4px;margin-bottom:16px;font-size:.84rem;display:flex;align-items:center;gap:8px">
        🔒 {{ session('sin_permiso_carpeta') }}
    </div>
    @endif

    <div class="panel-welcome">
        <h2>Bienvenido, {{ session('usuario_nombre') }}</h2>
        <p>{{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}
            @if(session('es_superadmin'))
                · <span style="color:var(--blue-accent);font-weight:500">Super Administrador</span>
            @elseif(session('es_admin'))
                · <span style="color:var(--blue-accent);font-weight:500">Administrador</span>
            @endif
        </p>
    </div>

    {{-- Bloques --}}
    @if(count($bloques) > 0)
        <div class="section-label">Módulos del sistema</div>
        <div class="bloques-grid">
            @foreach($bloques as $bloque)
            <a href="{{ route('carpetas.show', $bloque['carpeta_id']) }}"
               class="bloque"
               style="border-top-color: {{ $bloque['color'] }}; text-decoration:none">
                <div class="bloque-icon-wrap" style="background: {{ $bloque['color'] }}18">
                    <span style="font-size:1.5rem;line-height:1">{{ $bloque['emoji'] }}</span>
                </div>
                <div class="bloque-title">{{ $bloque['titulo'] }}</div>
                <div class="bloque-badge">{{ $bloque['badge'] }}</div>
            </a>
            @endforeach
        </div>
    @else
        <div style="padding:40px 0;text-align:center">
            <div style="font-size:2.5rem;margin-bottom:12px">🔒</div>
            <p style="color:var(--text-muted);font-size:.9rem">No tienes módulos asignados. Contacta al administrador.</p>
        </div>
    @endif

    {{-- Resumen --}}
    <div class="section-label">Resumen del sistema</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value {{ $stats['cumplimiento'] >= 80 ? 'success' : ($stats['cumplimiento'] >= 50 ? 'warning' : 'danger') }}">
                {{ $stats['cumplimiento'] }}%
            </div>
            <div class="stat-label">Cumplimiento global</div>
        </div>
        <div class="stat-card">
            <div class="stat-value {{ $stats['pendientes'] > 5 ? 'danger' : ($stats['pendientes'] > 0 ? 'warning' : 'success') }}">
                {{ $stats['pendientes'] }}
            </div>
            <div class="stat-label">Actividades pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value success">{{ $stats['cerradas'] }}</div>
            <div class="stat-label">Actividades cerradas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['minutas_mes'] }}</div>
            <div class="stat-label">Minutas este mes</div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
window.history.pushState(null, '', window.location.href);
window.addEventListener('popstate', function() {
    window.history.pushState(null, '', window.location.href);
});
</script>
@endpush
