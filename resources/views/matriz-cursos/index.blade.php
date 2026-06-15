@extends('layouts.app')
@section('title', 'Matriz Cursos')

@push('styles')
<style>
:root {
    --mc-navy:   #0D2B5E;
    --mc-blue:   #1d4ed8;
    --mc-green:  #16a34a;
    --mc-orange: #d97706;
    --mc-red:    #dc2626;
    --mc-border: #d1d5db;
    --mc-radius: 6px;
}

/* ── Page layout ── */
.mc-page {
    padding: 0;
    font-family: 'Inter', sans-serif;
    font-size: .82rem;
}

/* ── Header ── */
.mc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px 12px;
    background: #fff;
    border-bottom: 1px solid var(--mc-border);
    flex-wrap: wrap;
    gap: 10px;
}
.mc-header-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--mc-navy);
    letter-spacing: .01em;
}
.mc-header-btns { display: flex; gap: 8px; }

/* ── Filtros ── */
.mc-filters {
    background: #f8fafc;
    border-bottom: 1px solid var(--mc-border);
    padding: 10px 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.mc-filters select,
.mc-filters input[type="text"] {
    height: 34px;
    padding: 0 10px;
    border: 1px solid var(--mc-border);
    border-radius: var(--mc-radius);
    font-size: .8rem;
    color: #374151;
    background: #fff;
}
.mc-filters select { min-width: 220px; max-width: 300px; }
.mc-filters input[type="text"] { width: 130px; }
.mc-filter-label {
    font-size: .72rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .05em;
}

/* ── Botones ── */
.btn-mc {
    display: inline-flex; align-items: center; gap: 5px;
    height: 34px; padding: 0 14px;
    border: none; border-radius: var(--mc-radius);
    font-size: .8rem; font-weight: 600;
    cursor: pointer; transition: opacity .15s;
    white-space: nowrap;
}
.btn-mc:hover { opacity: .88; }
.btn-mc-green  { background: #16a34a; color: #fff; }
.btn-mc-navy   { background: var(--mc-navy); color: #fff; }
.btn-mc-blue   { background: var(--mc-blue); color: #fff; }
.btn-mc-gray   { background: #6b7280; color: #fff; }
.btn-mc-icon {
    width: 34px; height: 34px; padding: 0;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; border-radius: var(--mc-radius);
    cursor: pointer; transition: opacity .15s;
}
.btn-mc-icon:hover { opacity: .85; }
.btn-mc-add    { background: #16a34a; color: #fff; font-size: 1.1rem; font-weight: 700; }
.btn-mc-edit   { background: #eab308; color: #fff; }
.btn-mc-del    { background: #dc2626; color: #fff; }

/* ── Tabla ── */
.mc-table-wrap {
    padding: 16px 24px 40px;
    overflow-x: auto;
}
.mc-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .78rem;
}
.mc-table th {
    background: var(--mc-navy);
    color: #fff;
    font-weight: 600;
    font-size: .7rem;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 9px 10px;
    text-align: center;
    white-space: nowrap;
    border: 1px solid rgba(255,255,255,.1);
}
.mc-table td {
    padding: 7px 10px;
    border: 1px solid #e5e7eb;
    vertical-align: middle;
    background: #fff;
}
.mc-table tbody tr:hover td { background: #f8fafc; }
.mc-table td.td-worker {
    font-weight: 600;
    color: var(--mc-navy);
    vertical-align: top;
}
.mc-table td.td-curso { color: #1e293b; }
.mc-table td.td-fecha { text-align: center; white-space: nowrap; }
.mc-table td.td-semaforo { text-align: center; }
.mc-table td.td-actions { text-align: center; white-space: nowrap; vertical-align: top; }

/* Semaforos */
.semaforo {
    display: inline-block;
    width: 18px; height: 18px;
    border-radius: 50%;
    border: 2px solid rgba(0,0,0,.15);
}
.semaforo-verde   { background: #16a34a; }
.semaforo-naranja { background: #f59e0b; }
.semaforo-rojo    { background: #dc2626; }

/* Fecha vencida resaltada */
.fecha-vencida { background: #dc2626; color: #fff; border-radius: 4px; padding: 2px 6px; }
.fecha-proxima { background: #fef3c7; color: #92400e; border-radius: 4px; padding: 2px 6px; }

/* ── Modal ── */
.mc-modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1000;
    align-items: flex-start;
    justify-content: center;
    padding: 30px 20px;
    overflow-y: auto;
}
.mc-modal-overlay.visible { display: flex; }
.mc-modal {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 8px 40px rgba(0,0,0,.22);
    width: 100%;
    max-width: 860px;
    position: relative;
}
.mc-modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--mc-border);
}
.mc-modal-head h3 { font-size: .95rem; font-weight: 700; color: var(--mc-navy); margin: 0; }
.mc-modal-close {
    background: none; border: none; font-size: 1.3rem;
    cursor: pointer; color: #6b7280; line-height: 1;
}
.mc-modal-body { padding: 18px 20px; }
.mc-section-title {
    font-size: .8rem; font-weight: 700;
    color: var(--mc-navy); margin: 0 0 12px;
    padding-bottom: 6px; border-bottom: 2px solid #e5e7eb;
}
.mc-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}
.mc-form-grid.cols-5 { grid-template-columns: repeat(5, 1fr); }
.mc-form-group label {
    display: block;
    font-size: .72rem; font-weight: 600; color: #374151;
    margin-bottom: 4px;
}
.mc-form-group input[type="text"],
.mc-form-group input[type="date"],
.mc-form-group input[type="email"] {
    width: 100%; height: 32px; padding: 0 8px;
    border: 1px solid var(--mc-border);
    border-radius: 4px; font-size: .78rem;
    color: #1e293b; background: #fff;
}
.mc-form-group input:focus { outline: none; border-color: var(--mc-blue); }

/* Tabla de cursos en modal */
.mc-cursos-table-wrap {
    overflow-x: auto;
    border-radius: 4px;
    border: 1px solid var(--mc-border);
    margin-top: 10px;
}
.mc-cursos-table {
    width: 100%; min-width: 600px; border-collapse: collapse;
    font-size: .76rem;
}
.mc-cursos-table th {
    background: #f1f5f9; color: #374151;
    font-weight: 600; font-size: .68rem;
    text-transform: uppercase; letter-spacing: .04em;
    padding: 6px 8px; text-align: left;
    border-bottom: 1px solid var(--mc-border);
    white-space: nowrap;
}
.mc-cursos-table td {
    padding: 5px 7px;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
}
.mc-cursos-table tr:last-child td { border-bottom: none; }
.mc-cursos-table tr:hover td { background: #f8fafc; }

/* Fila de nuevo curso */
.mc-new-curso-row td { background: #f0fdf4; }
.mc-new-curso-row input,
.mc-new-curso-row input[type="date"] {
    width: 100%; height: 28px; padding: 0 5px;
    border: 1px solid #bbf7d0;
    border-radius: 3px; font-size: .74rem; box-sizing: border-box;
}
.mc-new-curso-row input:focus { border-color: #16a34a; outline: none; }

/* Fila edición en modal cursos */
.mc-edit-curso-row td { background: #eff6ff; }
.mc-edit-curso-row input,
.mc-edit-curso-row input[type="date"] {
    width: 100%; height: 28px; padding: 0 5px;
    border: 1px solid #bfdbfe;
    border-radius: 3px; font-size: .74rem; box-sizing: border-box;
}

.mc-modal-foot {
    display: flex; justify-content: flex-end; gap: 8px;
    padding: 14px 20px; border-top: 1px solid var(--mc-border);
}

/* Toast */
#mc-toasts {
    position: fixed; bottom: 24px; right: 24px;
    display: flex; flex-direction: column; gap: 8px; z-index: 9999;
}
.mc-toast {
    padding: 10px 18px; border-radius: 6px;
    font-size: .82rem; font-weight: 500; color: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,.2);
    animation: mcSlideIn .25s ease;
}
.mc-toast.ok  { background: #16a34a; }
.mc-toast.err { background: #dc2626; }
@keyframes mcSlideIn { from { transform: translateX(50px); opacity: 0; } to { transform: none; opacity: 1; } }

/* Empty state */
.mc-empty { text-align: center; padding: 40px; color: #9ca3af; font-size: .85rem; }

/* Filtros col labels */
.mc-col-filters {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 24px;
    background: #fff;
    border-bottom: 1px solid var(--mc-border);
    flex-wrap: wrap;
}
.mc-col-filter-input {
    height: 30px; padding: 0 8px;
    border: 1px solid var(--mc-border);
    border-radius: 4px;
    font-size: .75rem;
    color: #374151;
}
</style>
@endpush

@section('content')
<div class="mc-page">
<div id="mc-toasts"></div>

{{-- ── Header ───────────────────────────────────────────────────────────── --}}
<div class="mc-header">
    <div class="mc-header-title">
        <i class="fa-solid fa-graduation-cap" style="color:var(--mc-navy);margin-right:6px"></i>
        MATRIZ CONTROL CURSOS Y ACREDITACIÓN DE COMPETENCIAS
    </div>
    <div class="mc-header-btns">
        @if($puedeGestionar)
        <button class="btn-mc btn-mc-green" onclick="abrirModal('modal-importar')">
            <i class="fa-solid fa-file-excel"></i> Importar Excel
        </button>
        @endif
        <a href="{{ route('matriz-cursos.exportar') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
           class="btn-mc btn-mc-green">
            <i class="fa-solid fa-file-excel"></i> Exportar Excel
        </a>
        <a href="{{ route('carpetas.show', 5) }}" class="btn-mc btn-mc-navy" style="background:#6b7280">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
        <a href="{{ route('panel') }}" class="btn-mc btn-mc-navy" style="background:#374151">
            <i class="fa-solid fa-house"></i> Panel Principal
        </a>
    </div>
</div>

{{-- ── Filtro dropdown trabajador ────────────────────────────────────────── --}}
<div class="mc-filters">
    <span class="mc-filter-label">Trabajador:</span>
    <select id="filtro-trabajador" onchange="filtrarPorTrabajador(this.value)">
        <option value="">— Todos los trabajadores —</option>
        @foreach($todosLosTrabajadores as $t)
        <option value="{{ $t->id }}" {{ request('trabajador_id') == $t->id ? 'selected' : '' }}>
            {{ $t->apellidos }}, {{ $t->nombres }}
        </option>
        @endforeach
    </select>
    <button class="btn-mc btn-mc-green" onclick="filtrarPorTrabajador(document.getElementById('filtro-trabajador').value)">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>
    <button class="btn-mc btn-mc-navy" style="background:#0891b2" onclick="limpiarFiltros()" title="Limpiar filtros">
        <i class="fa-solid fa-rotate"></i>
    </button>
</div>

{{-- ── Filtros por columna + botón agregar trabajador ────────────────────── --}}
<form id="form-col-filtros" method="GET" action="{{ route('matriz-cursos.index') }}">
<div class="mc-col-filters">
    <span class="mc-filter-label" style="min-width:60px">Filtrar:</span>
    <input class="mc-col-filter-input" style="width:130px" type="text" name="nombres"
           placeholder="Nombres" value="{{ request('nombres') }}">
    <input class="mc-col-filter-input" style="width:130px" type="text" name="apellidos"
           placeholder="Apellidos" value="{{ request('apellidos') }}">
    <input class="mc-col-filter-input" style="width:100px" type="text" name="rut"
           placeholder="RUT" value="{{ request('rut') }}">
    <input class="mc-col-filter-input" style="width:160px" type="text" name="cargo"
           placeholder="Cargo" value="{{ request('cargo') }}">
    <input class="mc-col-filter-input" style="width:160px" type="text" name="contrato"
           placeholder="Contrato" value="{{ request('contrato') }}">
    <button type="submit" class="btn-mc btn-mc-navy" style="height:30px;padding:0 12px">
        <i class="fa-solid fa-filter"></i>
    </button>
    @if($puedeGestionar)
    <button type="button" class="btn-mc btn-mc-navy" style="height:30px;padding:0 12px;background:#0D2B5E"
            onclick="abrirModalNuevoTrabajador()">
        <i class="fa-solid fa-plus"></i> Nuevo Trabajador
    </button>
    @endif
</div>
</form>

{{-- ── Tabla principal ─────────────────────────────────────────────────────── --}}
<div class="mc-table-wrap">
    @if($trabajadores->isEmpty())
    <div class="mc-empty">
        <i class="fa-solid fa-users" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.3"></i>
        No hay trabajadores registrados.
        @if($puedeGestionar)
            <a href="#" onclick="abrirModalNuevoTrabajador()" style="color:var(--mc-blue)">Agregar el primero</a>
        @endif
    </div>
    @else
    <table class="mc-table">
        <thead>
            <tr>
                <th>NOMBRES</th>
                <th>APELLIDOS</th>
                <th>RUT</th>
                <th>CARGO</th>
                <th>CONTRATO</th>
                <th>CURSOS</th>
                <th>ENTIDAD ACREDITADORA</th>
                <th>FECHA DE REALIZACIÓN</th>
                <th>FECHA DE VENCIMIENTO</th>
                <th>ARCHIVO DEL CURSO</th>
                <th>SEMÁFORO</th>
                <th>CORREO DE AVISO</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody>
        @foreach($trabajadores as $t)
        @php $cursos = $t->cursos; $rowspan = max($cursos->count(), 1); @endphp

        @if($cursos->isEmpty())
        <tr>
            <td class="td-worker" rowspan="1">{{ $t->nombres }}</td>
            <td class="td-worker" rowspan="1">{{ $t->apellidos }}</td>
            <td rowspan="1" style="text-align:center">{{ $t->rut ?? '—' }}</td>
            <td rowspan="1">{{ $t->cargo ?? '—' }}</td>
            <td rowspan="1">{{ $t->contrato ?? '—' }}</td>
            <td colspan="6" style="color:#9ca3af;text-align:center;font-style:italic">Sin cursos registrados</td>
            <td class="td-actions" rowspan="1">
                @if($puedeGestionar)
                <button class="btn-mc-icon btn-mc-edit" style="width:28px;height:28px"
                        onclick="abrirModalEditar({{ $t->id }})" title="Editar">
                    <i class="fa-solid fa-pencil" style="font-size:.7rem"></i>
                </button>
                <button class="btn-mc-icon btn-mc-del" style="width:28px;height:28px;margin-left:3px"
                        onclick="confirmarEliminarTrabajador({{ $t->id }}, '{{ addslashes($t->nombres . ' ' . $t->apellidos) }}')" title="Eliminar">
                    <i class="fa-solid fa-trash" style="font-size:.7rem"></i>
                </button>
                @endif
            </td>
        </tr>
        @else
        @foreach($cursos as $i => $c)
        @php
            $semaforo = $c->semaforo;
            $fvClass  = $semaforo === 'rojo'    ? 'fecha-vencida'
                      : ($semaforo === 'naranja' ? 'fecha-proxima' : '');
        @endphp
        <tr>
            @if($i === 0)
            <td class="td-worker" rowspan="{{ $rowspan }}">{{ $t->nombres }}</td>
            <td class="td-worker" rowspan="{{ $rowspan }}">{{ $t->apellidos }}</td>
            <td rowspan="{{ $rowspan }}" style="text-align:center">{{ $t->rut ?? '—' }}</td>
            <td rowspan="{{ $rowspan }}">{{ $t->cargo ?? '—' }}</td>
            <td rowspan="{{ $rowspan }}">{{ $t->contrato ?? '—' }}</td>
            @endif

            <td class="td-curso">{{ $c->curso }}</td>
            <td style="text-align:center">{{ $c->entidad_acreditadora ?? '—' }}</td>
            <td class="td-fecha">{{ $c->fecha_realizacion?->format('d-m-Y') ?? '—' }}</td>
            <td class="td-fecha">
                @if($c->fecha_vencimiento)
                    <span class="{{ $fvClass }}">{{ $c->fecha_vencimiento->format('d-m-Y') }}</span>
                @else
                    <span style="color:#9ca3af">—</span>
                @endif
            </td>
            <td style="text-align:center">
                @if($c->archivo_ruta)
                    <a href="{{ route('matriz-cursos.cursos.descargar', $c->id) }}"
                       style="color:var(--mc-blue);font-weight:600;text-decoration:none">
                       <i class="fa-solid fa-download" style="font-size:.75rem;margin-right:2px"></i> Descargar
                    </a>
                @else
                    <span style="color:#9ca3af">—</span>
                @endif
            </td>
            <td class="td-semaforo">
                <span class="semaforo semaforo-{{ $semaforo }}" title="{{ $semaforo }}"></span>
            </td>
            <td style="font-size:.73rem;color:#374151">{{ $c->correo_aviso ?? '—' }}</td>

            @if($i === 0)
            <td class="td-actions" rowspan="{{ $rowspan }}">
                @if($puedeGestionar)
                <button class="btn-mc-icon btn-mc-edit" style="width:28px;height:28px"
                        onclick="abrirModalEditar({{ $t->id }})" title="Editar trabajador y cursos">
                    <i class="fa-solid fa-pencil" style="font-size:.7rem"></i>
                </button>
                <button class="btn-mc-icon btn-mc-del" style="width:28px;height:28px;margin-left:3px"
                        onclick="confirmarEliminarTrabajador({{ $t->id }}, '{{ addslashes($t->nombres . ' ' . $t->apellidos) }}')" title="Eliminar trabajador">
                    <i class="fa-solid fa-trash" style="font-size:.7rem"></i>
                </button>
                @endif
            </td>
            @endif
        </tr>
        @endforeach
        @endif
        @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- Modal: Nuevo trabajador --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-nuevo-trabajador" class="mc-modal-overlay"
     onclick="if(event.target===this)cerrarModal('modal-nuevo-trabajador')">
    <div class="mc-modal">
        <div class="mc-modal-head">
            <h3><i class="fa-solid fa-user-plus" style="margin-right:6px"></i>Nuevo Trabajador</h3>
            <button class="mc-modal-close" onclick="cerrarModal('modal-nuevo-trabajador')">&times;</button>
        </div>
        <div class="mc-modal-body">
            <p class="mc-section-title">Datos del trabajador</p>
            <div class="mc-form-grid cols-5">
                <div class="mc-form-group" style="grid-column:span 2">
                    <label>Nombres <span style="color:red">*</span></label>
                    <input type="text" id="nt-nombres" placeholder="Ej: Juan Carlos">
                </div>
                <div class="mc-form-group" style="grid-column:span 2">
                    <label>Apellidos <span style="color:red">*</span></label>
                    <input type="text" id="nt-apellidos" placeholder="Ej: Pérez González">
                </div>
                <div class="mc-form-group">
                    <label>RUT</label>
                    <input type="text" id="nt-rut" placeholder="Ej: 12.345.678-9">
                </div>
                <div class="mc-form-group" style="grid-column:span 3">
                    <label>Cargo</label>
                    <input type="text" id="nt-cargo" placeholder="Ej: Técnico Mecánico">
                </div>
                <div class="mc-form-group" style="grid-column:span 2">
                    <label>Contrato</label>
                    <input type="text" id="nt-contrato" placeholder="Ej: Servicio Mantenimiento Pozos">
                </div>
            </div>
        </div>
        <div class="mc-modal-foot">
            <button class="btn-mc btn-mc-gray" onclick="cerrarModal('modal-nuevo-trabajador')">Cancelar</button>
            <button class="btn-mc btn-mc-green" onclick="crearTrabajador()">
                <i class="fa-solid fa-plus"></i> Crear Trabajador
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- Modal: Editar trabajador + gestionar cursos --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-editar" class="mc-modal-overlay"
     onclick="if(event.target===this)cerrarModal('modal-editar')">
    <div class="mc-modal">
        <div class="mc-modal-head">
            <h3 id="modal-editar-titulo">Cursos Trabajador</h3>
            <button class="mc-modal-close" onclick="cerrarModal('modal-editar')">&times;</button>
        </div>
        <div class="mc-modal-body">

            {{-- Datos del trabajador --}}
            <p class="mc-section-title">Datos del trabajador</p>
            <input type="hidden" id="edit-trabajador-id">
            <div class="mc-form-grid cols-5">
                <div class="mc-form-group" style="grid-column:span 2">
                    <label>Nombres</label>
                    <input type="text" id="edit-nombres">
                </div>
                <div class="mc-form-group" style="grid-column:span 2">
                    <label>Apellidos</label>
                    <input type="text" id="edit-apellidos">
                </div>
                <div class="mc-form-group">
                    <label>RUT</label>
                    <input type="text" id="edit-rut">
                </div>
                <div class="mc-form-group" style="grid-column:span 3">
                    <label>Cargo</label>
                    <input type="text" id="edit-cargo">
                </div>
                <div class="mc-form-group" style="grid-column:span 2">
                    <label>Contrato</label>
                    <input type="text" id="edit-contrato">
                </div>
            </div>

            {{-- Ingresar nuevo curso --}}
            <p class="mc-section-title" style="margin-top:16px">Ingresar curso</p>
            <div class="mc-cursos-table-wrap">
            <table class="mc-cursos-table">
                <thead>
                    <tr>
                        <th style="width:22%">Curso</th>
                        <th style="width:14%">Entidad</th>
                        <th style="width:11%">Ejecutado</th>
                        <th style="width:11%">Vencimiento</th>
                        <th style="width:14%">Archivo</th>
                        <th style="width:20%">Correo</th>
                        <th style="width:8%"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="mc-new-curso-row">
                        <td><input type="text" id="nc-curso" placeholder="Nombre del curso"></td>
                        <td><input type="text" id="nc-entidad" placeholder="ISI, CAPLAB..."></td>
                        <td><input type="date" id="nc-realizado"></td>
                        <td><input type="date" id="nc-vencimiento"></td>
                        <td>
                            <input type="file" id="nc-archivo" style="display:none" accept=".pdf,.jpg,.jpeg,.png">
                            <button type="button" onclick="document.getElementById('nc-archivo').click()"
                                style="font-size:.7rem;padding:3px 6px;border:1px solid #bbf7d0;border-radius:3px;cursor:pointer;background:#f0fdf4;white-space:nowrap">
                                <i class="fa-solid fa-paperclip"></i> Elegir
                            </button>
                            <span id="nc-archivo-nombre" style="font-size:.68rem;color:#6b7280;display:block;margin-top:2px;word-break:break-all"></span>
                        </td>
                        <td><input type="text" id="nc-correo" placeholder="email@empresa.cl"></td>
                        <td style="text-align:center">
                            <button class="btn-mc-icon btn-mc-add" style="width:28px;height:28px;border-radius:4px"
                                    onclick="agregarCurso()" title="Agregar curso">+</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>

            {{-- Lista de cursos realizados --}}
            <p class="mc-section-title" style="margin-top:18px">Cursos realizados</p>
            <div id="lista-cursos" class="mc-cursos-table-wrap" style="border:none;padding:0">
                <div style="color:#9ca3af;font-size:.8rem;text-align:center;padding:16px">
                    Cargando cursos...
                </div>
            </div>

        </div>
        <div class="mc-modal-foot">
            <button class="btn-mc btn-mc-gray" onclick="cerrarModal('modal-editar')">Cerrar Ventana</button>
            <button class="btn-mc btn-mc-blue" onclick="guardarDatosTrabajador()">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- Modal: Importar Excel --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-importar" class="mc-modal-overlay"
     onclick="if(event.target===this)cerrarModal('modal-importar')">
    <div class="mc-modal" style="max-width:500px">
        <div class="mc-modal-head">
            <h3><i class="fa-solid fa-file-excel" style="color:#16a34a;margin-right:6px"></i>Importar desde Excel</h3>
            <button class="mc-modal-close" onclick="cerrarModal('modal-importar')">&times;</button>
        </div>
        <div class="mc-modal-body">
            <p style="font-size:.83rem;color:#374151;margin:0 0 14px">
                Importa trabajadores y cursos desde el archivo Excel de la matriz.
                Se procesarán <strong>todas las hojas</strong> (excepto "Control Capacitaciones").<br>
                Los registros duplicados (mismo trabajador + mismo curso) se omitirán automáticamente.
            </p>
            <div style="background:#fefce8;border:1px solid #fde68a;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:.78rem;color:#92400e">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:5px"></i>
                Los trabajadores existentes no se modificarán. Solo se agregarán cursos nuevos.
            </div>
            <div class="mc-form-group">
                <label>Seleccionar archivo Excel (.xlsx)</label>
                <input type="file" id="imp-archivo" accept=".xlsx,.xls"
                       style="display:block;width:100%;padding:6px;border:1px solid var(--mc-border);border-radius:4px;font-size:.8rem;margin-top:4px">
            </div>
            <div id="imp-resultado" style="display:none;margin-top:12px;padding:10px 14px;border-radius:6px;font-size:.82rem"></div>
        </div>
        <div class="mc-modal-foot">
            <button class="btn-mc btn-mc-gray" onclick="cerrarModal('modal-importar')">Cancelar</button>
            <button id="imp-btn" class="btn-mc btn-mc-green" onclick="ejecutarImport()">
                <i class="fa-solid fa-upload"></i> Importar
            </button>
        </div>
    </div>
</div>

{{-- Modal confirmar eliminar --}}
<div id="modal-confirm" class="mc-modal-overlay"
     onclick="if(event.target===this)cerrarModal('modal-confirm')">
    <div class="mc-modal" style="max-width:420px;margin-top:120px">
        <div class="mc-modal-head">
            <h3><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;margin-right:6px"></i>Confirmar eliminación</h3>
            <button class="mc-modal-close" onclick="cerrarModal('modal-confirm')">&times;</button>
        </div>
        <div class="mc-modal-body">
            <p id="mc-confirm-msg" style="color:#374151;font-size:.88rem;margin:0"></p>
        </div>
        <div class="mc-modal-foot">
            <button class="btn-mc btn-mc-gray" onclick="cerrarModal('modal-confirm')">Cancelar</button>
            <button id="mc-confirm-ok" class="btn-mc" style="background:#dc2626;color:#fff">
                <i class="fa-solid fa-trash"></i> Eliminar
            </button>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
const ROUTES = {
    trabajadoresStore:  '{{ route('matriz-cursos.trabajadores.store') }}',
    trabajadoresBase:   '{{ url('matriz-cursos/trabajadores') }}',
    cursosBase:         '{{ url('matriz-cursos/cursos') }}',
    importar:           '{{ route('matriz-cursos.importar') }}',
    index:              '{{ route('matriz-cursos.index') }}',
};

// ── Helpers ──────────────────────────────────────────────────────────────────
function notif(type, msg) {
    const d = document.createElement('div');
    d.className = 'mc-toast ' + (type === 'ok' ? 'ok' : 'err');
    d.textContent = msg;
    document.getElementById('mc-toasts').appendChild(d);
    setTimeout(() => d.remove(), 3500);
}
function abrirModal(id)  { document.getElementById(id).classList.add('visible'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('visible'); }
async function mcFetch(url, opts = {}) {
    const headers = { 'X-CSRF-TOKEN': window.CSRF_TOKEN, ...(opts.headers || {}) };
    if (!(opts.body instanceof FormData)) headers['Content-Type'] = 'application/json';
    return fetch(url, { ...opts, headers });
}

// ── Filtros ───────────────────────────────────────────────────────────────────
function filtrarPorTrabajador(id) {
    const url = new URL(ROUTES.index);
    if (id) url.searchParams.set('trabajador_id', id);
    window.location.href = url.toString();
}
function limpiarFiltros() { window.location.href = ROUTES.index; }

// ── Nuevo trabajador ──────────────────────────────────────────────────────────
function abrirModalNuevoTrabajador() {
    ['nt-nombres','nt-apellidos','nt-rut','nt-cargo','nt-contrato']
        .forEach(id => document.getElementById(id).value = '');
    abrirModal('modal-nuevo-trabajador');
}
async function crearTrabajador() {
    const nombres   = document.getElementById('nt-nombres').value.trim();
    const apellidos = document.getElementById('nt-apellidos').value.trim();
    if (!nombres || !apellidos) { notif('err', 'Nombres y apellidos son obligatorios'); return; }

    const res = await mcFetch(ROUTES.trabajadoresStore, {
        method: 'POST',
        body: JSON.stringify({
            nombres, apellidos,
            rut:      document.getElementById('nt-rut').value.trim()      || null,
            cargo:    document.getElementById('nt-cargo').value.trim()    || null,
            contrato: document.getElementById('nt-contrato').value.trim() || null,
        })
    });
    const data = await res.json();
    if (data.success) {
        notif('ok', 'Trabajador creado');
        cerrarModal('modal-nuevo-trabajador');
        setTimeout(() => location.reload(), 600);
    } else {
        notif('err', data.message || 'Error al crear');
    }
}

// ── Editar trabajador ─────────────────────────────────────────────────────────
async function abrirModalEditar(id) {
    const res = await mcFetch(`${ROUTES.trabajadoresBase}/${id}/datos`);
    const data = await res.json();

    document.getElementById('edit-trabajador-id').value  = data.id;
    document.getElementById('modal-editar-titulo').textContent =
        `Cursos Trabajador ${data.nombres} ${data.apellidos} — ID: ${data.id}`;
    document.getElementById('edit-nombres').value   = data.nombres;
    document.getElementById('edit-apellidos').value = data.apellidos;
    document.getElementById('edit-rut').value       = data.rut      || '';
    document.getElementById('edit-cargo').value     = data.cargo    || '';
    document.getElementById('edit-contrato').value  = data.contrato || '';

    renderCursos(data.cursos, id);
    abrirModal('modal-editar');
}

function renderCursos(cursos, trabajadorId) {
    const cont = document.getElementById('lista-cursos');
    if (!cursos || cursos.length === 0) {
        cont.innerHTML = '<p style="color:#9ca3af;font-size:.8rem;text-align:center;padding:12px">Sin cursos registrados</p>';
        return;
    }
    let html = `<div class="mc-cursos-table-wrap"><table class="mc-cursos-table">
        <thead><tr>
            <th style="width:22%">Curso</th><th style="width:11%">Entidad</th><th style="width:10%">Ejecutado</th>
            <th style="width:10%">Vencimiento</th><th style="width:8%">Archivo</th><th style="width:17%">Correo</th>
            <th style="width:6%">Semáforo</th><th style="width:8%">Acción</th>
        </tr></thead><tbody>`;
    cursos.forEach(c => {
        const semColor = c.semaforo === 'verde' ? '#16a34a' : c.semaforo === 'naranja' ? '#f59e0b' : '#dc2626';
        const vencClass = c.semaforo === 'rojo' ? 'style="color:#dc2626;font-weight:600"'
                        : c.semaforo === 'naranja' ? 'style="color:#d97706;font-weight:600"' : '';
        html += `<tr id="curso-row-${c.id}">
            <td>${escHtml(c.curso)}</td>
            <td>${escHtml(c.entidad_acreditadora || '—')}</td>
            <td style="white-space:nowrap">${c.fecha_realizacion || '—'}</td>
            <td style="white-space:nowrap" ${vencClass}>${c.fecha_vencimiento || '—'}</td>
            <td style="text-align:center">
                ${c.archivo_nombre
                    ? `<a href="${ROUTES.cursosBase}/${c.id}/descargar" style="color:var(--mc-blue);font-size:.72rem"><i class="fa-solid fa-download"></i> Descargar</a>`
                    : '<span style="color:#9ca3af">—</span>'}
            </td>
            <td style="font-size:.72rem">${escHtml(c.correo_aviso || '—')}</td>
            <td style="text-align:center">
                <span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:${semColor};border:2px solid rgba(0,0,0,.15)"></span>
            </td>
            <td style="text-align:center;white-space:nowrap">
                <button class="btn-mc-icon btn-mc-edit" style="width:26px;height:26px"
                        onclick="editarCursoInline(${c.id}, ${trabajadorId})" title="Editar">
                    <i class="fa-solid fa-pencil" style="font-size:.65rem"></i>
                </button>
                <button class="btn-mc-icon btn-mc-del" style="width:26px;height:26px;margin-left:2px"
                        onclick="eliminarCurso(${c.id}, ${trabajadorId})" title="Eliminar">
                    <i class="fa-solid fa-trash" style="font-size:.65rem"></i>
                </button>
            </td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    cont.innerHTML = html;
}

async function guardarDatosTrabajador() {
    const id = document.getElementById('edit-trabajador-id').value;
    const res = await mcFetch(`${ROUTES.trabajadoresBase}/${id}`, {
        method: 'PUT',
        body: JSON.stringify({
            nombres:   document.getElementById('edit-nombres').value.trim(),
            apellidos: document.getElementById('edit-apellidos').value.trim(),
            rut:       document.getElementById('edit-rut').value.trim()      || null,
            cargo:     document.getElementById('edit-cargo').value.trim()    || null,
            contrato:  document.getElementById('edit-contrato').value.trim() || null,
        })
    });
    const data = await res.json();
    if (data.success) {
        notif('ok', 'Datos actualizados');
        setTimeout(() => location.reload(), 600);
    } else {
        notif('err', 'Error al guardar');
    }
}

// ── Agregar curso ─────────────────────────────────────────────────────────────
async function agregarCurso() {
    const id = document.getElementById('edit-trabajador-id').value;
    const curso = document.getElementById('nc-curso').value.trim();
    if (!curso) { notif('err', 'El nombre del curso es obligatorio'); return; }

    const fd = new FormData();
    fd.append('curso',                 curso);
    fd.append('entidad_acreditadora',  document.getElementById('nc-entidad').value.trim());
    fd.append('fecha_realizacion',     document.getElementById('nc-realizado').value);
    fd.append('fecha_vencimiento',     document.getElementById('nc-vencimiento').value);
    fd.append('correo_aviso',          document.getElementById('nc-correo').value.trim());
    const archivo = document.getElementById('nc-archivo').files[0];
    if (archivo) fd.append('archivo', archivo);

    const res = await mcFetch(`${ROUTES.trabajadoresBase}/${id}/cursos`, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        notif('ok', 'Curso agregado');
        // Limpiar campos
        ['nc-curso','nc-entidad','nc-realizado','nc-vencimiento','nc-correo'].forEach(x => document.getElementById(x).value = '');
        document.getElementById('nc-archivo').value = '';
        document.getElementById('nc-archivo-nombre').textContent = '';
        // Recargar lista
        const resp2 = await mcFetch(`${ROUTES.trabajadoresBase}/${id}/datos`);
        const d2 = await resp2.json();
        renderCursos(d2.cursos, id);
    } else {
        notif('err', 'Error al agregar curso');
    }
}

// Mostrar nombre archivo seleccionado
document.getElementById('nc-archivo').addEventListener('change', function() {
    document.getElementById('nc-archivo-nombre').textContent = this.files[0]?.name || '';
});

// ── Editar curso inline ───────────────────────────────────────────────────────
async function editarCursoInline(cursoId, trabajadorId) {
    // Cargar datos actuales del curso
    const resp = await mcFetch(`${ROUTES.trabajadoresBase}/${trabajadorId}/datos`);
    const d    = await resp.json();
    const c    = d.cursos.find(x => x.id === cursoId);
    if (!c) return;

    const row = document.getElementById(`curso-row-${cursoId}`);
    row.className = 'mc-edit-curso-row';
    row.innerHTML = `
        <td><input type="text" id="ec-curso-${cursoId}" value="${escHtml(c.curso)}"></td>
        <td><input type="text" id="ec-entidad-${cursoId}" value="${escHtml(c.entidad_acreditadora||'')}"></td>
        <td><input type="date" id="ec-realizado-${cursoId}" value="${c.fecha_realizacion||''}"></td>
        <td><input type="date" id="ec-vencimiento-${cursoId}" value="${c.fecha_vencimiento||''}"></td>
        <td>
            <input type="file" id="ec-archivo-${cursoId}" style="display:none" accept=".pdf,.jpg,.jpeg,.png"
                   onchange="document.getElementById('ec-archivo-nombre-${cursoId}').textContent=this.files[0]?.name||''">
            <button type="button" onclick="document.getElementById('ec-archivo-${cursoId}').click()"
                style="font-size:.72rem;padding:2px 6px;border:1px solid #bfdbfe;border-radius:3px;cursor:pointer;background:#eff6ff">
                <i class="fa-solid fa-paperclip"></i>
            </button>
            <span id="ec-archivo-nombre-${cursoId}" style="font-size:.68rem;color:#374151;display:block;margin-top:2px;word-break:break-all"></span>
            ${c.archivo_nombre ? `<a href="${ROUTES.cursosBase}/${cursoId}/descargar" style="font-size:.7rem;color:var(--mc-blue);display:block;margin-top:2px"><i class="fa-solid fa-download"></i> actual</a>` : ''}
        </td>
        <td><input type="text" id="ec-correo-${cursoId}" value="${escHtml(c.correo_aviso||'')}"></td>
        <td></td>
        <td style="text-align:center;white-space:nowrap">
            <button class="btn-mc-icon btn-mc-green" style="width:26px;height:26px;background:#16a34a"
                    onclick="guardarCursoEdit(${cursoId}, ${trabajadorId})" title="Guardar">
                <i class="fa-solid fa-check" style="font-size:.65rem"></i>
            </button>
            <button class="btn-mc-icon btn-mc-gray" style="width:26px;height:26px;margin-left:2px"
                    onclick="abrirModalEditar(${trabajadorId})" title="Cancelar">
                <i class="fa-solid fa-xmark" style="font-size:.65rem"></i>
            </button>
        </td>`;
}

async function guardarCursoEdit(cursoId, trabajadorId) {
    const fd = new FormData();
    fd.append('curso',                document.getElementById(`ec-curso-${cursoId}`).value.trim());
    fd.append('entidad_acreditadora', document.getElementById(`ec-entidad-${cursoId}`).value.trim());
    fd.append('fecha_realizacion',    document.getElementById(`ec-realizado-${cursoId}`).value);
    fd.append('fecha_vencimiento',    document.getElementById(`ec-vencimiento-${cursoId}`).value);
    fd.append('correo_aviso',         document.getElementById(`ec-correo-${cursoId}`).value.trim());
    const archivo = document.getElementById(`ec-archivo-${cursoId}`).files[0];
    if (archivo) fd.append('archivo', archivo);
    fd.append('_method', 'POST'); // override

    const res = await mcFetch(`${ROUTES.cursosBase}/${cursoId}`, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        notif('ok', 'Curso actualizado');
        abrirModalEditar(trabajadorId);
    } else {
        notif('err', 'Error al actualizar');
    }
}

// ── Eliminar curso ────────────────────────────────────────────────────────────
async function eliminarCurso(cursoId, trabajadorId) {
    if (!confirm('¿Eliminar este curso?')) return;
    const res = await mcFetch(`${ROUTES.cursosBase}/${cursoId}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
        notif('ok', 'Curso eliminado');
        abrirModalEditar(trabajadorId);
    } else {
        notif('err', 'Error al eliminar');
    }
}

// ── Eliminar trabajador ───────────────────────────────────────────────────────
function confirmarEliminarTrabajador(id, nombre) {
    document.getElementById('mc-confirm-msg').textContent =
        `¿Eliminar al trabajador "${nombre}" y todos sus cursos?`;
    document.getElementById('mc-confirm-ok').onclick = async () => {
        const res = await mcFetch(`${ROUTES.trabajadoresBase}/${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            cerrarModal('modal-confirm');
            notif('ok', 'Trabajador eliminado');
            setTimeout(() => location.reload(), 600);
        } else {
            notif('err', 'Error al eliminar');
        }
    };
    abrirModal('modal-confirm');
}

// ── Importar Excel ────────────────────────────────────────────────────────────
async function ejecutarImport() {
    const archivo = document.getElementById('imp-archivo').files[0];
    if (!archivo) { notif('err', 'Selecciona un archivo Excel'); return; }

    const btn = document.getElementById('imp-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importando...';

    const fd = new FormData();
    fd.append('archivo_excel', archivo);

    try {
        const res  = await mcFetch(ROUTES.importar, { method: 'POST', body: fd });
        const data = await res.json();
        const div  = document.getElementById('imp-resultado');
        div.style.display = 'block';
        if (data.success) {
            div.style.background = '#f0fdf4';
            div.style.border     = '1px solid #bbf7d0';
            div.style.color      = '#166534';
            div.innerHTML = `<i class="fa-solid fa-circle-check"></i> <strong>${data.mensaje}</strong>`;
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa-solid fa-upload"></i> Importar';
            setTimeout(() => { cerrarModal('modal-importar'); location.reload(); }, 2500);
        } else {
            div.style.background = '#fef2f2';
            div.style.border     = '1px solid #fecaca';
            div.style.color      = '#991b1b';
            div.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> ${data.message || 'Error al importar'}`;
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa-solid fa-upload"></i> Importar';
        }
    } catch (e) {
        notif('err', 'Error de conexión');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa-solid fa-upload"></i> Importar';
    }
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
