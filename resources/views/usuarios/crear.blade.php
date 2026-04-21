@extends('layouts.app')
@section('title', isset($usuario) ? 'Editar Usuario' : 'Nuevo Usuario')

@push('styles')
<style>
.form-body { padding: 20px; max-width: 900px; margin: 0 auto; }
.form-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); overflow: hidden; margin-bottom: 16px;
}
.form-card-header {
    background: var(--navy); color: #fff;
    padding: 12px 18px; font-size: .82rem; font-weight: 700;
    display: flex; align-items: center; gap: 8px;
}
.form-card-body { padding: 18px; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* Áreas */
.areas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 8px;
}
.area-check {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 12px; border: 1px solid var(--border);
    border-radius: var(--radius-sm); cursor: pointer;
    font-size: .8rem; color: var(--text-secondary);
    transition: all .12s; user-select: none;
}
.area-check:hover { border-color: var(--blue-accent); background: var(--surface-2); }
.area-check.activo {
    border-color: var(--blue-accent);
    background: #EFF6FF; color: #1D4ED8; font-weight: 600;
}
.area-check input[type=checkbox] { accent-color: var(--blue-accent); flex-shrink: 0; }
.area-numero {
    display: inline-flex; align-items: center; justify-content: center;
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--surface-2); color: var(--text-muted);
    font-size: .65rem; font-weight: 700; flex-shrink: 0;
}
.area-check.activo .area-numero {
    background: #DBEAFE; color: #1D4ED8;
}

/* Bloques */
.bloques-grid-form {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
}
.bloque-check {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px; border: 1px solid var(--border);
    border-radius: var(--radius-sm); cursor: pointer;
    font-size: .78rem; color: var(--text-secondary); transition: all .12s;
}
.bloque-check:hover { border-color: var(--navy); background: var(--surface-2); }
.bloque-check input[type=checkbox] { accent-color: var(--navy); }
.bloque-check.activo { border-color: var(--navy); background: var(--surface-2); color: var(--navy); font-weight: 600; }

/* Carpetas */
.carpeta-perm { border: 1px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; margin-bottom: 8px; }
.carpeta-perm-header {
    background: var(--surface-2); padding: 8px 14px;
    font-size: .8rem; font-weight: 600; color: var(--navy);
    display: flex; align-items: center; gap: 8px;
    cursor: pointer; user-select: none;
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
    border: none; border-radius: var(--radius-sm);
    font-size: .85rem; font-weight: 600; cursor: pointer;
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

/* Acciones rápidas de áreas */
.areas-acciones {
    display: flex; gap: 6px; margin-bottom: 10px;
}
.btn-areas-toggle {
    padding: 4px 10px; border: 1px solid var(--border);
    border-radius: var(--radius-sm); font-size: .72rem;
    cursor: pointer; background: var(--surface-2); color: var(--text-secondary);
    transition: all .12s;
}
.btn-areas-toggle:hover { border-color: var(--navy); color: var(--navy); }

@media (max-width: 640px) {
    .form-body { padding: 12px; }
    .form-grid-2 { grid-template-columns: 1fr; }
    .permisos-checks { grid-template-columns: repeat(3, 1fr); }
    .bloques-grid-form { grid-template-columns: repeat(2, 1fr); }
    .areas-grid { grid-template-columns: 1fr 1fr; }
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
    @endphp

    <form method="POST" action="{{ $accion }}">
        @csrf
        @if($metodo === 'PUT') @method('PUT') @endif

        {{-- ── Datos básicos ─────────────────────────────────── --}}
        <div class="form-card">
            <div class="form-card-header">
                👤 {{ isset($usuario) ? 'Editar usuario' : 'Nuevo usuario' }}
            </div>
            <div class="form-card-body">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $usuario->nombre ?? '') }}"
                               placeholder="Ej: Juan Pérez" required>
                        @error('nombre') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo corporativo</label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $usuario->email ?? '') }}"
                               placeholder="usuario@fycchilespa.cl" required>
                        @error('email') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            {{ isset($usuario) ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña' }}
                        </label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="••••••••"
                               {{ isset($usuario) ? '' : 'required' }}>
                        @error('password') <div class="form-error">{{ $message }}</div> @enderror
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

        {{-- ── Áreas asignadas ───────────────────────────────── --}}
        <div class="form-card">
            <div class="form-card-header">🗂️ Áreas asignadas</div>
            <div class="form-card-body">
                <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:10px">
                    Selecciona las áreas a las que pertenece este usuario.
                    Esto determina qué planificaciones verá en su vista.
                </p>
                <div class="areas-acciones">
                    <button type="button" class="btn-areas-toggle" onclick="seleccionarTodasAreas(true)">
                        Seleccionar todas
                    </button>
                    <button type="button" class="btn-areas-toggle" onclick="seleccionarTodasAreas(false)">
                        Quitar todas
                    </button>
                </div>
                <div class="areas-grid" id="areas-grid">
                    @foreach($areas as $idArea => $nombreArea)
                    @php
                        $asignada = isset($areasAsignadas)
                            ? in_array($idArea, $areasAsignadas)
                            : false;
                        $asignada = old("areas.{$idArea}", $asignada ? $idArea : null) == $idArea;
                    @endphp
                    <label class="area-check {{ $asignada ? 'activo' : '' }}"
                           id="label-area-{{ $idArea }}">
                        <input type="checkbox"
                               name="areas[]"
                               value="{{ $idArea }}"
                               {{ $asignada ? 'checked' : '' }}
                               onchange="toggleAreaStyle(this, {{ $idArea }})">
                        <span class="area-numero">{{ $idArea }}</span>
                        <span>{{ $nombreArea }}</span>
                    </label>
                    @endforeach
                </div>
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
                    @foreach([
                        'bloque_sig'             => ['📋', 'Control SIG'],
                        'bloque_seguridad'       => ['🛡️', 'Seguridad SST'],
                        'bloque_ambiente'        => ['🌿', 'Medio Ambiente'],
                        'bloque_rrhh'            => ['👨‍💼', 'RRHH'],
                        'bloque_abastecimiento'  => ['🏗️', 'Abastecimiento'],
                        'bloque_proyectos'       => ['📈', 'Proyectos'],
                        'bloque_gerencia'        => ['🏢', 'Gerencia'],
                        'bloque_patio'           => ['🏭', 'Patio e Infraestructura'],
                        'bloque_calidad'         => ['✅', 'Calidad'],
                        'bloque_docs_legales'    => ['⚖️', 'Docs. Legales'],
                        'bloque_formatos'        => ['📝', 'Formatos'],
                        'bloque_listado_interes' => ['📌', 'Listado de Interés'],
                    ] as $col => [$emoji, $nombre])
                    @php
                        $checked = old("bloques.{$col}",
                            isset($bloques) ? ($bloques[$col] ?? false) : false);
                    @endphp
                    <label class="bloque-check {{ $checked ? 'activo' : '' }}"
                           id="label-{{ $col }}">
                        <input type="checkbox" name="bloques[{{ $col }}]" value="1"
                               {{ $checked ? 'checked' : '' }}
                               onchange="toggleBloqueStyle(this)">
                        <span>{{ $emoji }}</span>
                        <span>{{ $nombre }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Permisos de carpetas ──────────────────────────── --}}
        <div class="form-card">
            <div class="form-card-header">📁 Permisos de carpetas documentales</div>
            <div class="form-card-body">
                <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:12px">
                    Haz clic en una carpeta para expandir sus permisos.
                </p>
                @foreach($carpetas as $carpeta)
                @php
                    $perm      = isset($permisosCarpetas) ? ($permisosCarpetas[$carpeta->id] ?? null) : null;
                    $tienePerm = $perm !== null;
                @endphp
                <div class="carpeta-perm">
                    <div class="carpeta-perm-header" onclick="toggleCarpeta({{ $carpeta->id }})">
                        <span>{{ $tienePerm ? '📂' : '📁' }}</span>
                        <span>{{ $carpeta->descripcion }}</span>
                        @if($tienePerm)
                            <span style="margin-left:auto;font-size:.68rem;color:#16A34A;font-weight:700">
                                ✓ Con permisos
                            </span>
                        @endif
                        <svg id="arrow-{{ $carpeta->id }}" width="12" height="12" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5"
                             style="margin-left:{{ $tienePerm ? '0' : 'auto' }};transition:transform .15s">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </div>
                    <div class="carpeta-perm-body {{ $tienePerm ? 'visible' : '' }}"
                         id="perm-{{ $carpeta->id }}">
                        <div class="permisos-checks">
                            @foreach([
                                'carga'    => ['📤', 'Subir'],
                                'descarga' => ['📥', 'Descargar'],
                                'crear'    => ['📁', 'Crear'],
                                'eliminar' => ['🗑️', 'Eliminar'],
                                'editar'   => ['✏️', 'Editar'],
                            ] as $permKey => [$icon, $label])
                            @php
                                $isChecked = old("carpetas.{$carpeta->id}.{$permKey}",
                                    $perm ? (bool)$perm->$permKey : false);
                            @endphp
                            <label class="perm-check {{ $isChecked ? 'activo' : '' }}">
                                <input type="checkbox"
                                       name="carpetas[{{ $carpeta->id }}][{{ $permKey }}]"
                                       value="1" {{ $isChecked ? 'checked' : '' }}>
                                <span style="font-size:1.1rem">{{ $icon }}</span>
                                <span>{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Acciones --}}
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
function toggleCarpeta(id) {
    var body  = document.getElementById('perm-' + id);
    var arrow = document.getElementById('arrow-' + id);
    var visible = body.classList.toggle('visible');
    arrow.style.transform = visible ? 'rotate(90deg)' : '';
}

function toggleBloqueStyle(checkbox) {
    checkbox.closest('.bloque-check').classList.toggle('activo', checkbox.checked);
}

function toggleAreaStyle(checkbox, id) {
    document.getElementById('label-area-' + id).classList.toggle('activo', checkbox.checked);
}

function seleccionarTodasAreas(marcar) {
    document.querySelectorAll('#areas-grid input[type=checkbox]').forEach(function(cb) {
        cb.checked = marcar;
        var id = cb.value;
        document.getElementById('label-area-' + id).classList.toggle('activo', marcar);
    });
}
</script>
@endpush
