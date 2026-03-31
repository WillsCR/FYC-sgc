@extends('layouts.app')

@section('title', 'Panel principal')

@section('subnav')
    <a href="{{ route('panel') }}" class="subnav-item active">Inicio</a>
    <a href="#" class="subnav-item">Planificación</a>
    <a href="#" class="subnav-item">Minutas</a>
    <a href="#" class="subnav-item">Información SIG</a>
    <a href="#" class="subnav-item">Medio Ambiente</a>
    @if(session('es_admin'))
        <a href="#" class="subnav-item">Usuarios</a>
    @endif
@endsection

@section('content')
<div class="panel-body">

    {{-- Bienvenida --}}
    <div class="panel-welcome">
        <h2>Bienvenido, {{ session('usuario_nombre') }}</h2>
        <p>{{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}
            @if(session('es_admin'))
                · <span style="color:var(--blue-accent);font-weight:500">Administrador</span>
            @endif
        </p>
    </div>

    {{-- ── Bloques principales ────────────────────────────────── --}}
    @if(count($bloques) > 0)
        <div class="section-label">Módulos del sistema</div>
        <div class="bloques-grid" id="bloques-container">
            @foreach($bloques as $bloque)
            <div class="bloque" id="bloque-{{ $bloque['id'] }}"
                 onclick="toggleSubBloques('{{ $bloque['id'] }}')"
                 style="border-top-color: {{ $bloque['color'] }}">

                {{-- Ícono del bloque --}}
                <div class="bloque-icon-wrap" style="background: {{ $bloque['color'] }}18">
                    <span style="color: {{ $bloque['color'] }}">
                        @include('components.icono', ['nombre' => $bloque['icono'], 'size' => 20])
                    </span>
                </div>

                <div class="bloque-title">{{ $bloque['titulo'] }}</div>
                <div class="bloque-badge">{{ $bloque['badge'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- ── Sub-bloques desplegables ───────────────────────── --}}
        @foreach($bloques as $bloque)
        <div class="sub-bloques" id="sub-{{ $bloque['id'] }}" style="display:none">
            @foreach($bloque['sub'] as $sub)
            <a href="{{ $sub['ruta'] }}" class="sub-bloque"
               style="background-color: {{ $sub['color'] }}">
                <span style="color:#fff;opacity:.9">
                    @include('components.icono', ['nombre' => $sub['icono'], 'size' => 14])
                </span>
                {{ $sub['titulo'] }}
            </a>
            @endforeach
        </div>
        @endforeach

    @else
        {{-- Sin permisos asignados --}}
        <div style="padding:40px 0;text-align:center">
            <div style="font-size:2rem;margin-bottom:12px">🔒</div>
            <p style="color:var(--text-muted);font-size:.9rem">
                No tienes módulos asignados. Contacta al administrador del sistema.
            </p>
        </div>
    @endif

    {{-- ── Estadísticas globales ─────────────────────────────── --}}
    <div class="section-label">Resumen del sistema</div>
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-value {{ $stats['cumplimiento'] >= 80 ? 'success' : ($stats['cumplimiento'] >= 50 ? 'warning' : 'danger') }}">
                {{ $stats['cumplimiento'] }}%
            </div>
            <div class="stat-label">Cumplimiento global</div>
        </div>

        <div class="stat-card">
            <div class="stat-value {{ $stats['pendientes'] > 0 ? 'danger' : 'success' }}">
                {{ $stats['pendientes'] }}
            </div>
            <div class="stat-label">Pendientes críticos</div>
        </div>

        <div class="stat-card">
            <div class="stat-value">{{ $stats['minutas_mes'] }}</div>
            <div class="stat-label">Minutas este mes</div>
        </div>

        <div class="stat-card">
            <div class="stat-value">{{ $stats['documentos_activos'] }}</div>
            <div class="stat-label">Documentos activos</div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
var bloqueActivo = null;

function toggleSubBloques(id) {
    var subEl    = document.getElementById('sub-' + id);
    var bloqueEl = document.getElementById('bloque-' + id);

    // Si hay un bloque abierto distinto, cerrarlo
    if (bloqueActivo && bloqueActivo !== id) {
        document.getElementById('sub-' + bloqueActivo).style.display = 'none';
        document.getElementById('bloque-' + bloqueActivo).classList.remove('activo');
    }

    // Alternar el bloque actual
    var estaAbierto = subEl.style.display === 'flex';
    subEl.style.display    = estaAbierto ? 'none' : 'flex';
    bloqueEl.classList.toggle('activo', !estaAbierto);
    bloqueActivo = estaAbierto ? null : id;
}
</script>
@endpush
