@extends('layouts.app')

@section('title', 'Panel principal')

@section('subnav')
    <a href="{{ route('panel') }}" class="subnav-item active">Inicio</a>
    <a href="#" class="subnav-item">Planificación</a>
    <a href="#" class="subnav-item">Minutas</a>
    <a href="#" class="subnav-item">Información SIG</a>
    <a href="#" class="subnav-item">Medio Ambiente</a>
    <a href="#" class="subnav-item">Usuarios</a>
@endsection

@section('content')
<div class="panel-body">

    {{-- Bienvenida --}}
    <div class="panel-welcome" style="margin-bottom:20px">
        <h2>Bienvenido, {{ session('usuario_nombre') }}</h2>
        <p>{{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
    </div>

    <div class="section-label">Módulos del sistema</div>

    {{-- Los bloques se construyen en el Sprint 2 --}}
    <div class="bloques-grid" id="bloques-container">
        <p style="color:var(--text-muted);font-size:.82rem;grid-column:1/-1">
            Los módulos se cargarán en el Sprint 2.
            Sesión activa correctamente para <strong>{{ session('usuario_email') }}</strong>.
        </p>
    </div>

</div>
@endsection
