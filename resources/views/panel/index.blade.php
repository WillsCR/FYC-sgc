@extends('layouts.app')
@section('title', 'Panel principal')

@section('content')
<div class="panel-body">

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
            <div class="bloque" id="bloque-{{ $bloque['id'] }}"
                 onclick="toggleSubBloques('{{ $bloque['id'] }}')"
                 style="border-top-color: {{ $bloque['color'] }}">
                <div class="bloque-icon-wrap" style="background: {{ $bloque['color'] }}18">
                    <span style="font-size:1.5rem;line-height:1">{{ $bloque['emoji'] }}</span>
                </div>
                <div class="bloque-title">{{ $bloque['titulo'] }}</div>
                <div class="bloque-badge">{{ $bloque['badge'] }}</div>
            </div>
            @endforeach
        </div>

        @foreach($bloques as $bloque)
        <div class="sub-bloques" id="sub-{{ $bloque['id'] }}" style="display:none">
            @foreach($bloque['sub'] as $sub)
            <a href="{{ $sub['ruta'] }}" class="sub-bloque" style="background-color:{{ $sub['color'] }}">
                <span style="font-size:.95rem">{{ $sub['emoji'] }}</span>
                {{ $sub['titulo'] }}
            </a>
            @endforeach
        </div>
        @endforeach
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
var bloqueActivo = null;
function toggleSubBloques(id) {
    var subEl    = document.getElementById('sub-' + id);
    var bloqueEl = document.getElementById('bloque-' + id);
    if (bloqueActivo && bloqueActivo !== id) {
        document.getElementById('sub-' + bloqueActivo).style.display = 'none';
        document.getElementById('bloque-' + bloqueActivo).classList.remove('activo');
    }
    var estaAbierto = subEl.style.display === 'flex';
    subEl.style.display = estaAbierto ? 'none' : 'flex';
    bloqueEl.classList.toggle('activo', !estaAbierto);
    bloqueActivo = estaAbierto ? null : id;
}
</script>
@endpush
