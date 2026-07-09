@extends('layouts.app')
@section('title', isset($usuario) ? 'Editar Usuario' : 'Nuevo Usuario')

@push('styles')
<style>
.form-body { padding: 20px; max-width: 960px; margin: 0 auto; }
.form-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); overflow: hidden; margin-bottom: 16px;
}
.form-card-header {
    background: var(--navy); color: #fff;
    padding: 12px 18px; font-size: .82rem; font-weight: 700;
}
.form-card-body { padding: 18px; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* ── Tabla de permisos por área (estilo legacy mejorado) ──── */
.permisos-area-table {
    width: 100%; border-collapse: collapse; font-size: .8rem;
}
.permisos-area-table th {
    background: var(--navy); color: #fff;
    padding: 9px 12px; text-align: center;
    font-size: .7rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .04em;
}
.permisos-area-table th:first-child { text-align: left; min-width: 180px; }
.permisos-area-table td {
    padding: 8px 12px; border-bottom: 1px solid var(--border);
    text-align: center; vertical-align: middle;
}
.permisos-area-table td:first-child {
    text-align: left; color: var(--text-primary);
    font-weight: 500; font-size: .8rem;
}
.permisos-area-table tr:last-child td { border-bottom: none; }
.permisos-area-table tr:hover td { background: var(--surface-2); }
.permisos-area-table tr.tiene-permisos td { background: #F0FDF4; }
.permisos-area-table tr.tiene-permisos:hover td { background: #DCFCE7; }

/* Grupo de permisos en la cabecera */
.th-group {
    border-left: 2px solid rgba(255,255,255,.2);
    border-right: 2px solid rgba(255,255,255,.2);
}
.th-planificacion { background: #1D4ED8; }
.th-minutas       { background: #7C3AED; }

/* Checkbox estilizado */
.perm-cb-wrap {
    display: flex; align-items: center; justify-content: center;
}
.perm-cb {
    width: 18px; height: 18px;
    accent-color: var(--navy); cursor: pointer;
}
.perm-cb-plan { accent-color: #1D4ED8; }
.perm-cb-min  { accent-color: #7C3AED; }

/* Fila con separador de grupo */
.col-plan { border-left: 2px solid #DBEAFE; border-right: 1px solid var(--border); }
.col-plan-r { border-right: 2px solid #DBEAFE; }
.col-min  { border-left: 2px solid #EDE9FE; border-right: 1px solid var(--border); }
.col-min-r { border-right: 2px solid #EDE9FE; }

/* Acciones rápidas */
.tabla-acciones {
    display: flex; gap: 6px; margin-bottom: 10px; flex-wrap: wrap;
}
.btn-tabla-accion {
    padding: 4px 12px; border: 1px solid var(--border);
    border-radius: var(--radius-sm); font-size: .72rem;
    cursor: pointer; background: var(--surface-2); color: var(--text-secondary);
    transition: all .12s;
}
.btn-tabla-accion:hover { border-color: var(--navy); color: var(--navy); }
.btn-tabla-accion.plan { border-color: #BFDBFE; color: #1D4ED8; background: #EFF6FF; }
.btn-tabla-accion.plan:hover { background: #DBEAFE; }
.btn-tabla-accion.min  { border-color: #DDD6FE; color: #7C3AED; background: #F5F3FF; }
.btn-tabla-accion.min:hover  { background: #EDE9FE; }

/* Leyenda */
.tabla-leyenda {
    display: flex; gap: 14px; margin-bottom: 10px;
    font-size: .72rem; flex-wrap: wrap;
}
.leyenda-dot { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }

/* Bloques */
.bloques-grid-form { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.bloque-check {
    display: flex; align-items: center; gap: 8px; padding: 8px 12px;
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    cursor: pointer; font-size: .78rem; color: var(--text-secondary); transition: all .12s;
}
.bloque-check:hover { border-color: var(--navy); background: var(--surface-2); }
.bloque-check input[type=checkbox] { accent-color: var(--navy); }
.bloque-check.activo { border-color: var(--navy); background: var(--surface-2); color: var(--navy); font-weight: 600; }

/* Carpetas */
.carpeta-perm { border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; margin-bottom: 8px; }
.carpeta-perm-header {
    background: var(--surface-2); padding: 8px 14px; font-size: .8rem;
    font-weight: 600; color: var(--navy);
    display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;
}
.carpeta-perm-body { padding: 12px 14px; display: none; }
.carpeta-perm-body.visible { display: block; }
.permisos-checks { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; }
.perm-check {
    display: flex; flex-direction: column; align-items: center;
    gap: 4px; font-size: .7rem; color: var(--text-muted); cursor: pointer;
}
.perm-check input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--navy); }
.perm-check.activo { color: var(--navy); font-weight: 600; }

/* Acciones */
.form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px; }
.btn-guardar {
    padding: 10px 24px; background: var(--navy); color: #fff;
    border: none; border-radius: var(--radius-sm); font-size: .85rem; font-weight: 600; cursor: pointer;
}
.btn-guardar:hover { background: var(--navy-light); }
.btn-cancelar {
    padding: 10px 20px; background: transparent; color: var(--text-secondary);
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: .85rem; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center;
}
.breadcrumb-nav {
    display: flex; align-items: center; gap: 6px;
    margin-bottom: 16px; font-size: .78rem; color: var(--text-muted);
}
.breadcrumb-nav a { color: var(--text-muted); text-decoration: none; }
.breadcrumb-nav a:hover { color: var(--navy); }

@media (max-width: 640px) {
    .form-body { padding: 12px; }
    .form-grid-2 { grid-template-columns: 1fr; }
    .bloques-grid-form { grid-template-columns: repeat(2, 1fr); }
    .permisos-checks { grid-template-columns: repeat(3, 1fr); }
    .permisos-area-table { font-size: .72rem; }
}
</style>
@endpush

@section('content')
<div class="form-body">

    <div class="breadcrumb-nav">
        <a href="{{ route('usuarios.index') }}">👥 Usuarios</a>
        <span>›</span>
        <span>{{ isset($usuario) ? 'Editar: ' . $usuario->nombre : 'Nuevo usuario' }}</span>
    </div>

    @php
        $accion = isset($usuario)
            ? route('usuarios.update', $usuario->id)
            : route('usuarios.store');
        $metodo = isset($usuario) ? 'PUT' : 'POST';
        // $areas viene del controlador (AreaController → BD), no hardcodear aquí
    @endphp

    <form method="POST" action="{{ $accion }}">
        @csrf
        @if($metodo === 'PUT') @method('PUT') @endif

        {{-- ── Datos básicos ─────────────────────────────────── --}}
        <div class="form-card">
            <div class="form-card-header">👤 {{ isset($usuario) ? 'Editar usuario' : 'Nuevo usuario' }}</div>
            <div class="form-card-body">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $usuario->nombre ?? '') }}"
                               placeholder="Ej: Juan Pérez" required>
                        @error('nombre')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo corporativo</label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $usuario->email ?? '') }}"
                               placeholder="usuario@fycchilespa.cl" required>
                        @error('email')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            {{ isset($usuario) ? 'Nueva contraseña (vacío = no cambia)' : 'Contraseña' }}
                        </label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="••••••••"
                               {{ isset($usuario) ? '' : 'required' }}>
                        @error('password')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation"
                               class="form-control" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Perfil de usuario</label>
                        <select name="id_perfil" class="form-control" required>
                            @foreach($perfiles as $p)
                            <option value="{{ $p->id_perfil }}"
                                {{ old('id_perfil', $usuario->id_perfil ?? '') == $p->id_perfil ? 'selected' : '' }}>
                                {{ $p->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Permisos por área ─────────────────────────────── --}}
        <div class="form-card">
            <div class="form-card-header">🗂️ Permisos de Planificación y Minutas por Área</div>
            <div class="form-card-body">
                <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:12px">
                    Asigna las áreas y define qué puede hacer el usuario en cada una.
                    Si "Editar" está marcado, "Ver" se activa automáticamente.
                </p>

                {{-- Acciones rápidas --}}
                <div class="tabla-acciones">
                    <button type="button" class="btn-tabla-accion plan"
                            onclick="marcarColumna('ver_planificacion', true)">
                        ✓ Ver Plan. — todas
                    </button>
                    <button type="button" class="btn-tabla-accion plan"
                            onclick="marcarColumna('editar_planificacion', true)">
                        ✓ Editar Plan. — todas
                    </button>
                    <button type="button" class="btn-tabla-accion min"
                            onclick="marcarColumna('ver_minutas', true)">
                        ✓ Ver Min. — todas
                    </button>
                    <button type="button" class="btn-tabla-accion min"
                            onclick="marcarColumna('editar_minutas', true)">
                        ✓ Editar Min. — todas
                    </button>
                    <button type="button" class="btn-tabla-accion"
                            onclick="limpiarTodos()">
                        ✕ Quitar todos
                    </button>
                </div>

                <div style="overflow-x:auto">
                    <table class="permisos-area-table">
                        <thead>
                            <tr>
                                <th rowspan="2">Área</th>
                                <th colspan="2" class="th-group th-planificacion">📋 Planificación</th>
                                <th colspan="2" class="th-group th-minutas">📅 Minutas</th>
                            </tr>
                            <tr>
                                <th class="th-planificacion" style="font-size:.65rem">Ver</th>
                                <th class="th-planificacion th-group" style="font-size:.65rem">Editar</th>
                                <th class="th-minutas" style="font-size:.65rem">Ver</th>
                                <th class="th-minutas th-group" style="font-size:.65rem">Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($areas as $idArea => $nombreArea)
                            @php
                                $perm = isset($permisosArea) ? ($permisosArea[$idArea] ?? null) : null;
                                $verPlan    = old("permisos_area.{$idArea}.ver_planificacion",    $perm ? (bool)$perm->ver_planificacion    : false);
                                $editarPlan = old("permisos_area.{$idArea}.editar_planificacion", $perm ? (bool)$perm->editar_planificacion : false);
                                $verMin     = old("permisos_area.{$idArea}.ver_minutas",          $perm ? (bool)$perm->ver_minutas          : false);
                                $editarMin  = old("permisos_area.{$idArea}.editar_minutas",       $perm ? (bool)$perm->editar_minutas       : false);
                                $tieneAlgo  = $verPlan || $editarPlan || $verMin || $editarMin;
                            @endphp
                            <tr class="{{ $tieneAlgo ? 'tiene-permisos' : '' }}" id="fila-area-{{ $idArea }}">
                                <td>{{ $nombreArea }}</td>
                                {{-- Ver planificación --}}
                                <td class="col-plan">
                                    <div class="perm-cb-wrap">
                                        <input type="checkbox"
                                               class="perm-cb perm-cb-plan"
                                               name="permisos_area[{{ $idArea }}][ver_planificacion]"
                                               value="1"
                                               id="vp_{{ $idArea }}"
                                               {{ $verPlan ? 'checked' : '' }}
                                               onchange="onPermChange({{ $idArea }})">
                                    </div>
                                </td>
                                {{-- Editar planificación --}}
                                <td class="col-plan-r">
                                    <div class="perm-cb-wrap">
                                        <input type="checkbox"
                                               class="perm-cb perm-cb-plan"
                                               name="permisos_area[{{ $idArea }}][editar_planificacion]"
                                               value="1"
                                               id="ep_{{ $idArea }}"
                                               {{ $editarPlan ? 'checked' : '' }}
                                               onchange="onEditarPlan({{ $idArea }})">
                                    </div>
                                </td>
                                {{-- Ver minutas --}}
                                <td class="col-min">
                                    <div class="perm-cb-wrap">
                                        <input type="checkbox"
                                               class="perm-cb perm-cb-min"
                                               name="permisos_area[{{ $idArea }}][ver_minutas]"
                                               value="1"
                                               id="vm_{{ $idArea }}"
                                               {{ $verMin ? 'checked' : '' }}
                                               onchange="onPermChange({{ $idArea }})">
                                    </div>
                                </td>
                                {{-- Editar minutas --}}
                                <td class="col-min-r">
                                    <div class="perm-cb-wrap">
                                        <input type="checkbox"
                                               class="perm-cb perm-cb-min"
                                               name="permisos_area[{{ $idArea }}][editar_minutas]"
                                               value="1"
                                               id="em_{{ $idArea }}"
                                               {{ $editarMin ? 'checked' : '' }}
                                               onchange="onEditarMin({{ $idArea }})">
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p style="font-size:.72rem;color:var(--text-muted);margin-top:10px">
                    💡 Las filas en verde indican áreas con permisos activos.
                    Los permisos se aplican al cerrar sesión y volver a ingresar.
                </p>
            </div>
        </div>

        {{-- ── Módulos visibles ──────────────────────────────── --}}
        <div class="form-card">
            <div class="form-card-header">📋 Módulos visibles en el panel</div>
            <div class="form-card-body">
                <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:12px">
                    Selecciona qué bloques de módulos verá este usuario en su panel.
                </p>
                <div class="bloques-grid-form">
                    @foreach($modulosCarpetas as $modulo)
                    @if($modulo->es_dinamico)
                    @php
                        $dynChecked = old("bloque_dinamico.{$modulo->id}", false);
                        $dynIcono   = $modulo->icono ?? 'fa-folder-open';
                    @endphp
                    <label class="bloque-check {{ $dynChecked ? 'activo' : '' }}">
                        <input type="checkbox" name="bloque_dinamico[{{ $modulo->id }}]" value="1"
                               {{ $dynChecked ? 'checked' : '' }}
                               onchange="this.closest('.bloque-check').classList.toggle('activo', this.checked)">
                        @if(str_starts_with($dynIcono, 'fa-'))
                            <i class="fa-solid {{ $dynIcono }}" style="font-size:.95rem;width:1.2em;text-align:center"></i>
                        @else
                            <span style="font-size:.95rem;width:1.2em;text-align:center;display:inline-block">{{ $dynIcono }}</span>
                        @endif
                        <span>{{ $modulo->descripcion }}</span>
                    </label>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Permisos de carpetas ──────────────────────────── --}}
        <div class="form-card">
            <div class="form-card-header">📁 Permisos sub módulos</div>
            <div class="form-card-body">
                <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:12px">
                    Los permisos se asignan por submodulo. Haz clic para expandir.
                </p>
                @foreach($modulosCarpetas as $modulo)
                <div style="margin-bottom:18px">
                    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                                color:var(--navy);padding:6px 10px;background:var(--surface-2);
                                border:1px solid var(--border);border-radius:4px 4px 0 0;
                                border-bottom:2px solid var(--navy);margin-bottom:0">
                        📦 {{ $modulo->descripcion }}
                    </div>

                    @php
                        $carpetasPermisionables = $modulo->submodulos->isEmpty()
                            ? collect([$modulo])
                            : $modulo->submodulos;
                        $esModuloRaiz = $modulo->submodulos->isEmpty();
                    @endphp
                    @foreach($carpetasPermisionables as $carpeta)
                    @php
                        $perm2     = isset($permisosCarpetas) ? ($permisosCarpetas[$carpeta->id] ?? null) : null;
                        $tienePerm = $perm2 !== null;
                    @endphp
                    <div class="carpeta-perm" style="border-radius:0;border-top:none;
                         {{ $loop->last ? 'border-radius:0 0 4px 4px' : '' }}">
                        <div class="carpeta-perm-header" onclick="toggleCarpeta({{ $carpeta->id }})">
                            <span>{{ $tienePerm ? '📂' : '📁' }}</span>
                            <span>{{ $esModuloRaiz ? 'Acceso al módulo' : $carpeta->descripcion }}</span>
                            @if($tienePerm)
                                <span style="margin-left:auto;font-size:.68rem;color:#16A34A;font-weight:700">✓ Con permisos</span>
                            @endif
                            <svg id="arrow-{{ $carpeta->id }}" width="12" height="12" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2.5"
                                 style="margin-left:{{ $tienePerm ? '6px' : 'auto' }};transition:transform .15s">
                                <path d="M9 18l6-6-6-6"/>
                            </svg>
                        </div>
                        <div class="carpeta-perm-body {{ $tienePerm ? 'visible' : '' }}" id="perm-{{ $carpeta->id }}">
                            @php
                                $carpetasPermUnico = [9, 11, 98, 99, 105]; // No Conformidades, Cert. Calidad, Control de Instrumentos, Matriz Cursos, Matriz Competencias
                            @endphp

                            @if(in_array($carpeta->id, $carpetasPermUnico))
                            {{-- ── 2 opciones exclusivas + sin acceso implícito ── --}}
                            @php
                                $hasCarga    = $perm2 ? (bool)$perm2->carga : false;
                                $hasVer      = $perm2 ? (bool)($perm2->ver ?? false) : false;
                                $nivelActual = $hasCarga ? 'completo' : ($hasVer ? 'ver' : 'ninguno');
                            @endphp
                            <input type="hidden"
                                   id="nivel-{{ $carpeta->id }}"
                                   name="carpetas[{{ $carpeta->id }}][nivel_acceso]"
                                   value="{{ $nivelActual }}">
                            <div style="display:flex;gap:10px;padding:8px 0;flex-wrap:wrap">
                                <div id="opt-ver-{{ $carpeta->id }}"
                                     onclick="toggleNivelPerm({{ $carpeta->id }},'ver')"
                                     style="flex:1;min-width:180px;cursor:pointer;padding:10px 14px;border-radius:8px;
                                            border:2px solid {{ $nivelActual==='ver' ? '#1d4ed8' : 'var(--border)' }};
                                            background:{{ $nivelActual==='ver' ? '#EFF6FF' : '#fff' }};
                                            transition:all .15s;user-select:none">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
                                        <span style="font-size:1rem">👁</span>
                                        <span style="font-size:.82rem;font-weight:700;color:{{ $nivelActual==='ver' ? '#1d4ed8' : 'var(--text-primary)' }}">Solo visualización</span>
                                        @if($nivelActual==='ver')
                                        <span style="margin-left:auto;font-size:.68rem;background:#1d4ed8;color:#fff;padding:1px 7px;border-radius:10px;font-weight:600">Activo</span>
                                        @endif
                                    </div>
                                    <div style="font-size:.71rem;color:var(--text-muted)">Puede ver y exportar registros. No puede crear, editar ni eliminar.</div>
                                </div>
                                <div id="opt-completo-{{ $carpeta->id }}"
                                     onclick="toggleNivelPerm({{ $carpeta->id }},'completo')"
                                     style="flex:1;min-width:180px;cursor:pointer;padding:10px 14px;border-radius:8px;
                                            border:2px solid {{ $nivelActual==='completo' ? '#16A34A' : 'var(--border)' }};
                                            background:{{ $nivelActual==='completo' ? '#DCFCE7' : '#fff' }};
                                            transition:all .15s;user-select:none">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
                                        <span style="font-size:1rem">🔑</span>
                                        <span style="font-size:.82rem;font-weight:700;color:{{ $nivelActual==='completo' ? '#16A34A' : 'var(--text-primary)' }}">Acceso completo</span>
                                        @if($nivelActual==='completo')
                                        <span style="margin-left:auto;font-size:.68rem;background:#16A34A;color:#fff;padding:1px 7px;border-radius:10px;font-weight:600">Activo</span>
                                        @endif
                                    </div>
                                    <div style="font-size:.71rem;color:var(--text-muted)">Puede ver, crear, editar, eliminar y exportar. Importar solo admins.</div>
                                </div>
                            </div>
                            @if($nivelActual==='ninguno')
                            <p style="font-size:.72rem;color:var(--text-muted);margin:2px 0 6px;font-style:italic">Sin acceso — selecciona una opción para otorgar permisos.</p>
                            @endif
                            @else
                            {{-- ── Permisos granulares (comportamiento normal) ── --}}
                            <div style="display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap">
                                <a href="#" onclick="setPermisoRapido({{ $carpeta->id }},'ver'); return false;"
                                   style="font-size:.71rem;display:inline-flex;align-items:center;gap:4px;
                                          padding:3px 10px;border-radius:20px;border:1px solid #93C5FD;
                                          background:#EFF6FF;color:#1D4ED8;text-decoration:none;font-weight:600">
                                    👁 Solo visualización
                                </a>
                                <a href="#" onclick="setPermisoRapido({{ $carpeta->id }},'completo'); return false;"
                                   style="font-size:.71rem;display:inline-flex;align-items:center;gap:4px;
                                          padding:3px 10px;border-radius:20px;border:1px solid #86EFAC;
                                          background:#F0FDF4;color:#16A34A;text-decoration:none;font-weight:600">
                                    🔑 Acceso completo
                                </a>
                                <a href="#" onclick="setPermisoRapido({{ $carpeta->id }},'ninguno'); return false;"
                                   style="font-size:.71rem;display:inline-flex;align-items:center;gap:4px;
                                          padding:3px 10px;border-radius:20px;border:1px solid var(--border);
                                          background:var(--surface-2);color:var(--text-muted);text-decoration:none;font-weight:600">
                                    ✕ Sin acceso
                                </a>
                            </div>
                            <div class="permisos-checks" id="checks-{{ $carpeta->id }}">
                                @foreach(['carga'=>['📤','Subir'],'descarga'=>['📥','Descargar'],'crear'=>['📁','Crear'],'eliminar'=>['🗑️','Eliminar'],'editar'=>['✏️','Editar']] as $permKey => [$icon, $label])
                                @php $isChecked = old("carpetas.{$carpeta->id}.{$permKey}", $perm2 ? (bool)$perm2->$permKey : false); @endphp
                                <label class="perm-check {{ $isChecked ? 'activo' : '' }}">
                                    <input type="checkbox" name="carpetas[{{ $carpeta->id }}][{{ $permKey }}]"
                                           value="1" {{ $isChecked ? 'checked' : '' }}>
                                    <span style="font-size:1.1rem">{{ $icon }}</span>
                                    <span>{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('usuarios.index') }}" class="btn-cancelar">Cancelar</a>
            <button type="submit" class="btn-guardar">
                {{ isset($usuario) ? '💾 Guardar cambios' : '✅ Crear usuario' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
var AREAS = @json(array_keys($areas ?? []));

// Al marcar "Editar planificación" activa automáticamente "Ver planificación"
function onEditarPlan(idArea) {
    var editar = document.getElementById('ep_' + idArea);
    var ver    = document.getElementById('vp_' + idArea);
    if (editar.checked) ver.checked = true;
    onPermChange(idArea);
}

// Al marcar "Editar minutas" activa automáticamente "Ver minutas"
function onEditarMin(idArea) {
    var editar = document.getElementById('em_' + idArea);
    var ver    = document.getElementById('vm_' + idArea);
    if (editar.checked) ver.checked = true;
    onPermChange(idArea);
}

// Resalta la fila si tiene algún permiso activo
function onPermChange(idArea) {
    var fila = document.getElementById('fila-area-' + idArea);
    var checks = fila.querySelectorAll('input[type=checkbox]');
    var alguno = Array.from(checks).some(function(cb) { return cb.checked; });
    fila.classList.toggle('tiene-permisos', alguno);
}

// Marcar/desmarcar una columna completa
function marcarColumna(nombre, valor) {
    AREAS.forEach(function(id) {
        var cb = document.querySelector('input[name="permisos_area[' + id + '][' + nombre + ']"]');
        if (cb) {
            cb.checked = valor;
            // Si es editar, activar también ver
            if (nombre === 'editar_planificacion' && valor) {
                var ver = document.getElementById('vp_' + id);
                if (ver) ver.checked = true;
            }
            if (nombre === 'editar_minutas' && valor) {
                var ver = document.getElementById('vm_' + id);
                if (ver) ver.checked = true;
            }
            onPermChange(id);
        }
    });
}

// Quitar todos los permisos de la tabla
function limpiarTodos() {
    AREAS.forEach(function(id) {
        ['vp_','ep_','vm_','em_'].forEach(function(prefix) {
            var cb = document.getElementById(prefix + id);
            if (cb) cb.checked = false;
        });
        onPermChange(id);
    });
}

function toggleCarpeta(id) {
    var body  = document.getElementById('perm-' + id);
    var arrow = document.getElementById('arrow-' + id);
    arrow.style.transform = body.classList.toggle('visible') ? 'rotate(90deg)' : '';
}

function toggleNivelPerm(carpetaId, nivel) {
    const hidden = document.getElementById(`nivel-${carpetaId}`);
    const nuevoNivel = hidden.value === nivel ? 'ninguno' : nivel;
    hidden.value = nuevoNivel;

    const cfg = {
        ver:      { border: '#1d4ed8', bg: '#EFF6FF', txtColor: '#1d4ed8', badgeBg: '#1d4ed8' },
        completo: { border: '#16A34A', bg: '#DCFCE7', txtColor: '#16A34A', badgeBg: '#16A34A' },
    };

    ['ver', 'completo'].forEach(op => {
        const card  = document.getElementById(`opt-${op}-${carpetaId}`);
        const title = card?.querySelector('span:nth-child(2)');
        let badge   = card?.querySelector('.nivel-badge');
        const active = nuevoNivel === op;
        const c = cfg[op];

        if (!card) return;
        card.style.borderColor = active ? c.border : 'var(--border)';
        card.style.background  = active ? c.bg     : '#fff';
        if (title) title.style.color = active ? c.txtColor : 'var(--text-primary)';

        if (active && !badge) {
            badge = document.createElement('span');
            badge.className = 'nivel-badge';
            badge.style.cssText = `margin-left:auto;font-size:.68rem;background:${c.badgeBg};color:#fff;padding:1px 7px;border-radius:10px;font-weight:600`;
            badge.textContent = 'Activo';
            card.querySelector('div').appendChild(badge);
        } else if (!active && badge) {
            badge.remove();
        }
    });
}

function setPermisoRapido(carpetaId, nivel) {
    const mapa = {
        ver:      { carga: false, descarga: true,  crear: false, eliminar: false, editar: false },
        completo: { carga: true,  descarga: true,  crear: true,  eliminar: true,  editar: true  },
        ninguno:  { carga: false, descarga: false, crear: false, eliminar: false, editar: false },
    };
    const vals = mapa[nivel];
    if (!vals) return;
    const container = document.getElementById(`checks-${carpetaId}`);
    if (!container) return;
    container.querySelectorAll('input[type="checkbox"]').forEach(input => {
        const key = input.name.match(/\[(\w+)\]$/)?.[1];
        if (key && key in vals) {
            input.checked = vals[key];
            input.closest('.perm-check')?.classList.toggle('activo', vals[key]);
        }
    });
}
</script>
@endpush
