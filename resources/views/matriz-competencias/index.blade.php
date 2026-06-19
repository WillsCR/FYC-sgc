@extends('layouts.app')
@section('title', 'Matriz Control de Competencias')

@push('styles')
<style>
:root {
    --mcc-navy:   #1B3A6B;
    --mcc-blue:   #1d4ed8;
    --mcc-green:  #16a34a;
    --mcc-red:    #dc2626;
    --mcc-border: #d1d5db;
    --mcc-radius: 6px;
}
.mcc-page { padding: 0; font-family: 'Inter', sans-serif; font-size: .82rem; }

.mcc-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 24px 12px; background: #fff;
    border-bottom: 1px solid var(--mcc-border); flex-wrap: wrap; gap: 10px;
}
.mcc-header-title { font-size: 1rem; font-weight: 700; color: var(--mcc-navy); letter-spacing: .01em; }
.mcc-header-btns  { display: flex; gap: 8px; }

.mcc-filters {
    background: #f8fafc; border-bottom: 1px solid var(--mcc-border);
    padding: 10px 24px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.mcc-filters input[type="text"],
.mcc-filters select {
    height: 32px; padding: 0 9px; border: 1px solid var(--mcc-border);
    border-radius: var(--mcc-radius); font-size: .78rem; color: #374151; background: #fff;
}
.mcc-filter-label { font-size: .72rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }

.btn-mcc {
    display: inline-flex; align-items: center; gap: 5px; height: 34px; padding: 0 14px;
    border: none; border-radius: var(--mcc-radius); font-size: .8rem; font-weight: 600;
    cursor: pointer; transition: opacity .15s; white-space: nowrap; text-decoration: none;
}
.btn-mcc:hover { opacity: .88; }
.btn-mcc-green { background: var(--mcc-green); color: #fff; }
.btn-mcc-navy  { background: var(--mcc-navy); color: #fff; }
.btn-mcc-blue  { background: var(--mcc-blue); color: #fff; }
.btn-mcc-gray  { background: #6b7280; color: #fff; }
.btn-mcc-sm    { height: 28px; padding: 0 10px; font-size: .74rem; }
.btn-mcc-icon  {
    width: 28px; height: 28px; padding: 0;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; border-radius: 4px; cursor: pointer; transition: opacity .15s;
}
.btn-mcc-icon:hover { opacity: .85; }
.btn-mcc-edit  { background: #eab308; color: #fff; }
.btn-mcc-del   { background: var(--mcc-red); color: #fff; }

.mcc-table-wrap { padding: 16px 24px 40px; overflow-x: auto; }
.mcc-table { width: 100%; border-collapse: collapse; font-size: .78rem; }
.mcc-table th {
    background: var(--mcc-navy); color: #fff; font-weight: 600; font-size: .7rem;
    letter-spacing: .04em; text-transform: uppercase; padding: 9px 10px;
    text-align: center; white-space: nowrap; border: 1px solid rgba(255,255,255,.1);
}
.mcc-table td { padding: 7px 10px; border: 1px solid #e5e7eb; vertical-align: middle; background: #fff; }
.mcc-table tbody tr:hover td { background: #f8fafc; }
.cumple-si  { background: #dcfce7; color: #166534; font-weight: 700; border-radius: 4px; padding: 2px 8px; font-size: .72rem; }
.cumple-no  { background: #fee2e2; color: #991b1b; font-weight: 700; border-radius: 4px; padding: 2px 8px; font-size: .72rem; }

/* Modal */
.mcc-modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45);
    z-index: 1000; align-items: flex-start; justify-content: center;
    padding: 30px 20px; overflow-y: auto;
}
.mcc-modal-overlay.visible { display: flex; }
.mcc-modal {
    background: #fff; border-radius: 8px; box-shadow: 0 8px 40px rgba(0,0,0,.22);
    width: 100%; max-width: 780px; position: relative;
}
.mcc-modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--mcc-border);
}
.mcc-modal-head h3 { font-size: .95rem; font-weight: 700; color: var(--mcc-navy); margin: 0; }
.mcc-modal-close { background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #6b7280; }
.mcc-modal-body  { padding: 18px 20px; }
.mcc-modal-foot  { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 20px; border-top: 1px solid var(--mcc-border); }
.mcc-section-title {
    font-size: .8rem; font-weight: 700; color: var(--mcc-navy);
    margin: 0 0 12px; padding-bottom: 6px; border-bottom: 2px solid #e5e7eb;
}
.mcc-form-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-bottom: 16px;
}
.mcc-form-group label { display: block; font-size: .72rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.mcc-form-group input[type="text"],
.mcc-form-group select {
    width: 100%; height: 32px; padding: 0 8px; border: 1px solid var(--mcc-border);
    border-radius: 4px; font-size: .78rem; color: #1e293b; background: #fff;
}
.mcc-form-group input:focus,
.mcc-form-group select:focus { outline: none; border-color: var(--mcc-blue); }
.mcc-file-row {
    display: flex; align-items: center; gap: 8px; padding: 8px 10px;
    border: 1px solid var(--mcc-border); border-radius: 5px; background: #f9fafb; margin-bottom: 8px;
}
.mcc-file-label { font-size: .75rem; font-weight: 600; color: var(--mcc-navy); min-width: 130px; }
.mcc-file-name  { font-size: .73rem; color: #374151; flex: 1; }

/* Toast */
#mcc-toasts { position: fixed; bottom: 24px; right: 24px; display: flex; flex-direction: column; gap: 8px; z-index: 9999; }
.mcc-toast {
    padding: 10px 18px; border-radius: 6px; font-size: .82rem; font-weight: 500; color: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,.2); animation: mccSlideIn .25s ease;
}
.mcc-toast.ok  { background: var(--mcc-green); }
.mcc-toast.err { background: var(--mcc-red); }
@keyframes mccSlideIn { from { transform: translateX(50px); opacity: 0; } to { transform: none; opacity: 1; } }
.mcc-empty { text-align: center; padding: 40px; color: #9ca3af; font-size: .85rem; }

/* Viewer modal */
.mcc-viewer-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.7); z-index: 2000;
    align-items: center; justify-content: center; padding: 20px;
}
.mcc-viewer-overlay.visible { display: flex; }
.mcc-viewer-box {
    background: #1e293b; border-radius: 8px; width: 100%; max-width: 900px;
    height: 90vh; display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,.5);
}
.mcc-viewer-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 16px; border-bottom: 1px solid rgba(255,255,255,.1);
    flex-shrink: 0;
}
.mcc-viewer-head span { color: #e2e8f0; font-size: .85rem; font-weight: 600; }
.mcc-viewer-head-btns { display: flex; gap: 8px; align-items: center; }
.mcc-viewer-frame { flex: 1; border: none; background: #fff; }
.mcc-viewer-img-wrap { flex: 1; overflow: auto; display: flex; align-items: center; justify-content: center; background: #334155; }
.mcc-viewer-img-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }
</style>
@endpush

@section('content')
<div class="mcc-page">
<div id="mcc-toasts"></div>

{{-- Header --}}
<div class="mcc-header">
    <div class="mcc-header-title">
        <i class="fa-solid fa-user-graduate" style="color:var(--mcc-navy);margin-right:6px"></i>
        MATRIZ CONTROL DE COMPETENCIAS
    </div>
    <div class="mcc-header-btns">
        @if($puedeGestionar)
        <button class="btn-mcc btn-mcc-navy" onclick="abrirModal('modal-nuevo')" style="background:var(--mcc-green)">
            <i class="fa-solid fa-plus"></i> Nuevo Trabajador
        </button>
        <button class="btn-mcc btn-mcc-green" onclick="abrirModal('modal-importar')">
            <i class="fa-solid fa-file-excel"></i> Importar Excel
        </button>
        @endif
        <a href="{{ route('matriz-competencias.exportar') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
           class="btn-mcc btn-mcc-green">
            <i class="fa-solid fa-file-excel"></i> Exportar Excel
        </a>
        <a href="{{ route('carpetas.show', 1) }}" class="btn-mcc btn-mcc-navy" style="background:#6b7280">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
        <a href="{{ route('panel') }}" class="btn-mcc btn-mcc-navy" style="background:#374151">
            <i class="fa-solid fa-house"></i> Panel Principal
        </a>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('matriz-competencias.index') }}">
<div class="mcc-filters">
    <span class="mcc-filter-label">Filtrar:</span>
    <input type="text" name="nombres"   placeholder="Nombres"   value="{{ request('nombres') }}"   style="width:120px">
    <input type="text" name="apellidos" placeholder="Apellidos" value="{{ request('apellidos') }}" style="width:120px">
    <input type="text" name="rut"       placeholder="RUT"       value="{{ request('rut') }}"       style="width:100px">
    <input type="text" name="cargo"     placeholder="Cargo"     value="{{ request('cargo') }}"     style="width:140px">
    <input type="text" name="contrato"  placeholder="Contrato"  value="{{ request('contrato') }}"  style="width:150px">
    <select name="cumple" style="width:110px">
        <option value="">— Cumple —</option>
        <option value="1" {{ request('cumple') === '1' ? 'selected' : '' }}>Sí cumple</option>
        <option value="0" {{ request('cumple') === '0' ? 'selected' : '' }}>No cumple</option>
    </select>
    <button type="submit" class="btn-mcc btn-mcc-navy btn-mcc-sm">
        <i class="fa-solid fa-filter"></i> Filtrar
    </button>
    <a href="{{ route('matriz-competencias.index') }}" class="btn-mcc btn-mcc-sm" style="background:#0891b2;color:#fff">
        <i class="fa-solid fa-rotate"></i>
    </a>
</div>
</form>

{{-- Tabla --}}
<div class="mcc-table-wrap">
    @if($trabajadores->isEmpty())
    <div class="mcc-empty">
        <i class="fa-solid fa-users" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.3"></i>
        No hay trabajadores registrados.
        @if($puedeGestionar)
            <a href="#" onclick="abrirModal('modal-nuevo')" style="color:var(--mcc-blue)">Agregar el primero</a>
        @endif
    </div>
    @else
    <table class="mcc-table">
        <thead>
            <tr>
                <th>N°</th>
                <th>NOMBRES</th>
                <th>APELLIDOS</th>
                <th>RUT</th>
                <th>CARGO</th>
                <th>CONTRATO</th>
                <th>NIVEL DE ESTUDIOS</th>
                <th>ESPECIALIDAD</th>
                <th>CERT. TÍTULO</th>
                <th>CURRICULUM</th>
                <th>CUMPLE</th>
                @if($puedeGestionar)<th>ACCIONES</th>@endif
            </tr>
        </thead>
        <tbody>
        @foreach($trabajadores as $i => $t)
        <tr>
            <td style="text-align:center;color:#6b7280">{{ $i + 1 }}</td>
            <td style="font-weight:600;color:var(--mcc-navy)">{{ $t->nombres }}</td>
            <td style="font-weight:600;color:var(--mcc-navy)">{{ $t->apellidos }}</td>
            <td style="text-align:center">{{ $t->rut ?? '—' }}</td>
            <td>{{ $t->cargo ?? '—' }}</td>
            <td>{{ $t->contrato ?? '—' }}</td>
            <td>{{ $t->nivel_estudios ?? '—' }}</td>
            <td>{{ $t->especialidad ?? '—' }}</td>
            <td style="text-align:center">
                @if($t->cert_titulo_ruta)
                <div style="display:flex;align-items:center;justify-content:center;gap:4px">
                    <button onclick="verDoc('{{ route('matriz-competencias.ver', [$t->id, 'cert']) }}','{{ addslashes($t->cert_titulo_nombre) }}','{{ $t->cert_titulo_mime }}')"
                        style="background:var(--mcc-blue);color:#fff;border:none;border-radius:4px;padding:2px 7px;font-size:.72rem;cursor:pointer;white-space:nowrap">
                        <i class="fa-solid fa-eye"></i> Ver
                    </button>
                    <a href="{{ route('matriz-competencias.descargar', [$t->id, 'cert']) }}"
                       style="color:#6b7280;font-size:.72rem" title="Descargar">
                        <i class="fa-solid fa-download"></i>
                    </a>
                </div>
                @else
                    <span style="color:#9ca3af">—</span>
                @endif
            </td>
            <td style="text-align:center">
                @if($t->cv_ruta)
                <div style="display:flex;align-items:center;justify-content:center;gap:4px">
                    <button onclick="verDoc('{{ route('matriz-competencias.ver', [$t->id, 'cv']) }}','{{ addslashes($t->cv_nombre) }}','{{ $t->cv_mime }}')"
                        style="background:var(--mcc-blue);color:#fff;border:none;border-radius:4px;padding:2px 7px;font-size:.72rem;cursor:pointer;white-space:nowrap">
                        <i class="fa-solid fa-eye"></i> Ver
                    </button>
                    <a href="{{ route('matriz-competencias.descargar', [$t->id, 'cv']) }}"
                       style="color:#6b7280;font-size:.72rem" title="Descargar">
                        <i class="fa-solid fa-download"></i>
                    </a>
                </div>
                @else
                    <span style="color:#9ca3af">—</span>
                @endif
            </td>
            <td style="text-align:center">
                <span class="{{ $t->cumple ? 'cumple-si' : 'cumple-no' }}">
                    {{ $t->cumple ? 'SÍ' : 'NO' }}
                </span>
            </td>
            @if($puedeGestionar)
            <td style="text-align:center;white-space:nowrap">
                <button class="btn-mcc-icon btn-mcc-edit" onclick="abrirEditar({{ $t->id }})" title="Editar">
                    <i class="fa-solid fa-pencil" style="font-size:.65rem"></i>
                </button>
                <button class="btn-mcc-icon btn-mcc-del" style="margin-left:3px"
                        onclick="confirmarEliminar({{ $t->id }}, '{{ addslashes($t->nombres . ' ' . $t->apellidos) }}')" title="Eliminar">
                    <i class="fa-solid fa-trash" style="font-size:.65rem"></i>
                </button>
            </td>
            @endif
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Modal Nuevo --}}
<div id="modal-nuevo" class="mcc-modal-overlay" onclick="if(event.target===this)cerrarModal('modal-nuevo')">
    <div class="mcc-modal">
        <div class="mcc-modal-head">
            <h3><i class="fa-solid fa-user-plus" style="margin-right:6px"></i>Nuevo Trabajador</h3>
            <button class="mcc-modal-close" onclick="cerrarModal('modal-nuevo')">&times;</button>
        </div>
        <div class="mcc-modal-body">
            <p class="mcc-section-title">Datos personales y laborales</p>
            <div class="mcc-form-grid" style="grid-template-columns:repeat(3,1fr)">
                <div class="mcc-form-group" style="grid-column:span 1">
                    <label>Nombres <span style="color:red">*</span></label>
                    <input type="text" id="n-nombres" placeholder="Juan Carlos">
                </div>
                <div class="mcc-form-group" style="grid-column:span 1">
                    <label>Apellidos <span style="color:red">*</span></label>
                    <input type="text" id="n-apellidos" placeholder="Pérez González">
                </div>
                <div class="mcc-form-group">
                    <label>RUT</label>
                    <input type="text" id="n-rut" placeholder="12.345.678-9">
                </div>
                <div class="mcc-form-group" style="grid-column:span 2">
                    <label>Cargo</label>
                    <input type="text" id="n-cargo" placeholder="Técnico Mecánico">
                </div>
                <div class="mcc-form-group">
                    <label>Contrato</label>
                    <input type="text" id="n-contrato" placeholder="Mantenimiento Pozos">
                </div>
                <div class="mcc-form-group" style="grid-column:span 2">
                    <label>Nivel de Estudios</label>
                    <input type="text" id="n-nivel" placeholder="Técnico Superior, Universitario...">
                </div>
                <div class="mcc-form-group">
                    <label>Especialidad</label>
                    <input type="text" id="n-especialidad" placeholder="Ingeniería Mecánica">
                </div>
                <div class="mcc-form-group">
                    <label>Cumple</label>
                    <select id="n-cumple">
                        <option value="0">NO</option>
                        <option value="1">SÍ</option>
                    </select>
                </div>
            </div>
            <p class="mcc-section-title" style="margin-top:4px">Documentos</p>
            <div class="mcc-file-row">
                <span class="mcc-file-label"><i class="fa-solid fa-graduation-cap" style="margin-right:5px"></i>Cert. de Título</span>
                <input type="file" id="n-cert" style="display:none" accept=".pdf,.jpg,.jpeg,.png">
                <button type="button" onclick="document.getElementById('n-cert').click()"
                    class="btn-mcc btn-mcc-sm btn-mcc-navy">
                    <i class="fa-solid fa-paperclip"></i> Elegir
                </button>
                <span id="n-cert-nombre" class="mcc-file-name" style="color:#9ca3af">Ningún archivo</span>
            </div>
            <div class="mcc-file-row">
                <span class="mcc-file-label"><i class="fa-solid fa-file-lines" style="margin-right:5px"></i>Curriculum Vitae</span>
                <input type="file" id="n-cv" style="display:none" accept=".pdf,.doc,.docx">
                <button type="button" onclick="document.getElementById('n-cv').click()"
                    class="btn-mcc btn-mcc-sm btn-mcc-navy">
                    <i class="fa-solid fa-paperclip"></i> Elegir
                </button>
                <span id="n-cv-nombre" class="mcc-file-name" style="color:#9ca3af">Ningún archivo</span>
            </div>
        </div>
        <div class="mcc-modal-foot">
            <button class="btn-mcc btn-mcc-gray" onclick="cerrarModal('modal-nuevo')">Cancelar</button>
            <button class="btn-mcc btn-mcc-green" onclick="crearTrabajador()">
                <i class="fa-solid fa-plus"></i> Crear
            </button>
        </div>
    </div>
</div>

{{-- Modal Editar --}}
<div id="modal-editar" class="mcc-modal-overlay" onclick="if(event.target===this)cerrarModal('modal-editar')">
    <div class="mcc-modal">
        <div class="mcc-modal-head">
            <h3 id="modal-editar-titulo">Editar Trabajador</h3>
            <button class="mcc-modal-close" onclick="cerrarModal('modal-editar')">&times;</button>
        </div>
        <div class="mcc-modal-body">
            <input type="hidden" id="e-id">
            <p class="mcc-section-title">Datos personales y laborales</p>
            <div class="mcc-form-grid" style="grid-template-columns:repeat(3,1fr)">
                <div class="mcc-form-group" style="grid-column:span 1">
                    <label>Nombres <span style="color:red">*</span></label>
                    <input type="text" id="e-nombres">
                </div>
                <div class="mcc-form-group" style="grid-column:span 1">
                    <label>Apellidos <span style="color:red">*</span></label>
                    <input type="text" id="e-apellidos">
                </div>
                <div class="mcc-form-group">
                    <label>RUT</label>
                    <input type="text" id="e-rut">
                </div>
                <div class="mcc-form-group" style="grid-column:span 2">
                    <label>Cargo</label>
                    <input type="text" id="e-cargo">
                </div>
                <div class="mcc-form-group">
                    <label>Contrato</label>
                    <input type="text" id="e-contrato">
                </div>
                <div class="mcc-form-group" style="grid-column:span 2">
                    <label>Nivel de Estudios</label>
                    <input type="text" id="e-nivel">
                </div>
                <div class="mcc-form-group">
                    <label>Especialidad</label>
                    <input type="text" id="e-especialidad">
                </div>
                <div class="mcc-form-group">
                    <label>Cumple</label>
                    <select id="e-cumple">
                        <option value="0">NO</option>
                        <option value="1">SÍ</option>
                    </select>
                </div>
            </div>
            <p class="mcc-section-title" style="margin-top:4px">Documentos</p>
            <div class="mcc-file-row">
                <span class="mcc-file-label"><i class="fa-solid fa-graduation-cap" style="margin-right:5px"></i>Cert. de Título</span>
                <input type="file" id="e-cert" style="display:none" accept=".pdf,.jpg,.jpeg,.png">
                <button type="button" onclick="document.getElementById('e-cert').click()"
                    class="btn-mcc btn-mcc-sm btn-mcc-navy">
                    <i class="fa-solid fa-paperclip"></i> Reemplazar
                </button>
                <span id="e-cert-nombre" class="mcc-file-name" style="color:#9ca3af">Sin archivo</span>
                <a id="e-cert-link" href="#" target="_blank" style="display:none;font-size:.73rem;color:var(--mcc-blue)">
                    <i class="fa-solid fa-download"></i> Actual
                </a>
                <button id="e-cert-del" type="button" onclick="eliminarArchivo('cert')" style="display:none"
                    class="btn-mcc-icon btn-mcc-del" style="width:26px;height:26px" title="Eliminar documento">
                    <i class="fa-solid fa-trash" style="font-size:.65rem"></i>
                </button>
            </div>
            <div class="mcc-file-row">
                <span class="mcc-file-label"><i class="fa-solid fa-file-lines" style="margin-right:5px"></i>Curriculum Vitae</span>
                <input type="file" id="e-cv" style="display:none" accept=".pdf,.doc,.docx">
                <button type="button" onclick="document.getElementById('e-cv').click()"
                    class="btn-mcc btn-mcc-sm btn-mcc-navy">
                    <i class="fa-solid fa-paperclip"></i> Reemplazar
                </button>
                <span id="e-cv-nombre" class="mcc-file-name" style="color:#9ca3af">Sin archivo</span>
                <a id="e-cv-link" href="#" target="_blank" style="display:none;font-size:.73rem;color:var(--mcc-blue)">
                    <i class="fa-solid fa-download"></i> Actual
                </a>
                <button id="e-cv-del" type="button" onclick="eliminarArchivo('cv')" style="display:none"
                    class="btn-mcc-icon btn-mcc-del" style="width:26px;height:26px" title="Eliminar documento">
                    <i class="fa-solid fa-trash" style="font-size:.65rem"></i>
                </button>
            </div>
        </div>
        <div class="mcc-modal-foot">
            <button class="btn-mcc btn-mcc-gray" onclick="cerrarModal('modal-editar')">Cancelar</button>
            <button class="btn-mcc btn-mcc-blue" onclick="guardarEditar()">
                <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
            </button>
        </div>
    </div>
</div>

{{-- Modal Importar --}}
<div id="modal-importar" class="mcc-modal-overlay" onclick="if(event.target===this)cerrarModal('modal-importar')">
    <div class="mcc-modal" style="max-width:500px">
        <div class="mcc-modal-head">
            <h3><i class="fa-solid fa-file-excel" style="color:var(--mcc-green);margin-right:6px"></i>Importar desde Excel</h3>
            <button class="mcc-modal-close" onclick="cerrarModal('modal-importar')">&times;</button>
        </div>
        <div class="mcc-modal-body">
            <p style="font-size:.83rem;color:#374151;margin:0 0 12px">
                Importa trabajadores desde el archivo <strong>Matriz_control_competencias.xlsx</strong>.<br>
                Se leen: Nombres, Apellidos, RUT, Cargo, Contrato, Nivel de Estudios, Especialidad, Cumple.
            </p>
            <div style="background:#fefce8;border:1px solid #fde68a;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:.78rem;color:#92400e">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:5px"></i>
                Los trabajadores existentes (mismo RUT) no se modificarán. Los documentos deben cargarse manualmente.
            </div>
            <div class="mcc-form-group">
                <label>Archivo Excel (.xlsx / .xlsm)</label>
                <input type="file" id="imp-archivo" accept=".xlsx,.xls,.xlsm"
                       style="display:block;width:100%;padding:6px;border:1px solid var(--mcc-border);border-radius:4px;font-size:.8rem;margin-top:4px">
            </div>
            <div id="imp-resultado" style="display:none;margin-top:12px;padding:10px 14px;border-radius:6px;font-size:.82rem"></div>
        </div>
        <div class="mcc-modal-foot">
            <button class="btn-mcc btn-mcc-gray" onclick="cerrarModal('modal-importar')">Cancelar</button>
            <button id="imp-btn" class="btn-mcc btn-mcc-green" onclick="ejecutarImport()">
                <i class="fa-solid fa-upload"></i> Importar
            </button>
        </div>
    </div>
</div>

{{-- Modal Visualizador de documentos --}}
<div id="modal-viewer" class="mcc-viewer-overlay" onclick="if(event.target===this)cerrarViewer()">
    <div class="mcc-viewer-box">
        <div class="mcc-viewer-head">
            <span id="viewer-titulo">Documento</span>
            <div class="mcc-viewer-head-btns">
                <a id="viewer-dl-link" href="#" download
                   style="background:#374151;color:#e2e8f0;border:none;border-radius:4px;padding:4px 12px;font-size:.78rem;text-decoration:none;display:inline-flex;align-items:center;gap:5px">
                    <i class="fa-solid fa-download"></i> Descargar
                </a>
                <button onclick="cerrarViewer()"
                        style="background:#dc2626;color:#fff;border:none;border-radius:4px;padding:4px 12px;font-size:.78rem;cursor:pointer">
                    <i class="fa-solid fa-xmark"></i> Cerrar
                </button>
            </div>
        </div>
        <div id="viewer-content" style="flex:1;overflow:hidden;display:flex">
            {{-- contenido dinámico --}}
        </div>
    </div>
</div>

{{-- Modal Confirmar Eliminar --}}
<div id="modal-confirm" class="mcc-modal-overlay" onclick="if(event.target===this)cerrarModal('modal-confirm')">
    <div class="mcc-modal" style="max-width:420px;margin-top:120px">
        <div class="mcc-modal-head">
            <h3><i class="fa-solid fa-triangle-exclamation" style="color:var(--mcc-red);margin-right:6px"></i>Confirmar eliminación</h3>
            <button class="mcc-modal-close" onclick="cerrarModal('modal-confirm')">&times;</button>
        </div>
        <div class="mcc-modal-body">
            <p id="mcc-confirm-msg" style="color:#374151;font-size:.88rem;margin:0"></p>
        </div>
        <div class="mcc-modal-foot">
            <button class="btn-mcc btn-mcc-gray" onclick="cerrarModal('modal-confirm')">Cancelar</button>
            <button id="mcc-confirm-ok" class="btn-mcc" style="background:var(--mcc-red);color:#fff">
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
    store:   '{{ route('matriz-competencias.store') }}',
    base:    '{{ url('matriz-competencias') }}',
    importar:'{{ route('matriz-competencias.importar') }}',
    index:   '{{ route('matriz-competencias.index') }}',
};

function notif(type, msg) {
    const d = document.createElement('div');
    d.className = 'mcc-toast ' + (type === 'ok' ? 'ok' : 'err');
    d.textContent = msg;
    document.getElementById('mcc-toasts').appendChild(d);
    setTimeout(() => d.remove(), 3500);
}
function abrirModal(id)  { document.getElementById(id).classList.add('visible'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('visible'); }
async function mccFetch(url, opts = {}) {
    const headers = { 'X-CSRF-TOKEN': window.CSRF_TOKEN, ...(opts.headers || {}) };
    if (!(opts.body instanceof FormData)) headers['Content-Type'] = 'application/json';
    return fetch(url, { ...opts, headers });
}

// Mostrar nombre archivos seleccionados
['n-cert','n-cv','e-cert','e-cv'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', function() {
        document.getElementById(id + '-nombre').textContent = this.files[0]?.name || 'Ningún archivo';
    });
});

// ── Nuevo trabajador ──────────────────────────────────────────────────────────
function abrirModal(id) { document.getElementById(id).classList.add('visible'); }

async function crearTrabajador() {
    const nombres   = document.getElementById('n-nombres').value.trim();
    const apellidos = document.getElementById('n-apellidos').value.trim();
    if (!nombres || !apellidos) { notif('err', 'Nombres y apellidos son obligatorios'); return; }

    const fd = new FormData();
    fd.append('nombres',        nombres);
    fd.append('apellidos',      apellidos);
    fd.append('rut',            document.getElementById('n-rut').value.trim());
    fd.append('cargo',          document.getElementById('n-cargo').value.trim());
    fd.append('contrato',       document.getElementById('n-contrato').value.trim());
    fd.append('nivel_estudios', document.getElementById('n-nivel').value.trim());
    fd.append('especialidad',   document.getElementById('n-especialidad').value.trim());
    fd.append('cumple',         document.getElementById('n-cumple').value);
    const cert = document.getElementById('n-cert').files[0];
    const cv   = document.getElementById('n-cv').files[0];
    if (cert) fd.append('cert_titulo', cert);
    if (cv)   fd.append('cv', cv);

    const res  = await mccFetch(ROUTES.store, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) { notif('ok', 'Trabajador creado'); cerrarModal('modal-nuevo'); setTimeout(() => location.reload(), 600); }
    else notif('err', data.message || 'Error al crear');
}

// ── Editar ────────────────────────────────────────────────────────────────────
async function abrirEditar(id) {
    const res  = await mccFetch(`${ROUTES.base}/${id}/datos`);
    const data = await res.json();

    document.getElementById('e-id').value        = data.id;
    document.getElementById('modal-editar-titulo').textContent = `Editar: ${data.nombres} ${data.apellidos}`;
    document.getElementById('e-nombres').value      = data.nombres;
    document.getElementById('e-apellidos').value    = data.apellidos;
    document.getElementById('e-rut').value          = data.rut           || '';
    document.getElementById('e-cargo').value        = data.cargo         || '';
    document.getElementById('e-contrato').value     = data.contrato      || '';
    document.getElementById('e-nivel').value        = data.nivel_estudios || '';
    document.getElementById('e-especialidad').value = data.especialidad  || '';
    document.getElementById('e-cumple').value       = data.cumple ? '1' : '0';

    const certNombre = document.getElementById('e-cert-nombre');
    const cvNombre   = document.getElementById('e-cv-nombre');
    const certLink   = document.getElementById('e-cert-link');
    const cvLink     = document.getElementById('e-cv-link');

    const certDel = document.getElementById('e-cert-del');
    const cvDel   = document.getElementById('e-cv-del');

    if (data.cert_titulo_nombre) {
        certNombre.textContent    = data.cert_titulo_nombre;
        certLink.href             = `${ROUTES.base}/${id}/descargar/cert`;
        certLink.style.display    = 'inline';
        certDel.style.display     = 'inline-flex';
    } else {
        certNombre.textContent    = 'Sin archivo';
        certLink.style.display    = 'none';
        certDel.style.display     = 'none';
    }
    if (data.cv_nombre) {
        cvNombre.textContent  = data.cv_nombre;
        cvLink.href           = `${ROUTES.base}/${id}/descargar/cv`;
        cvLink.style.display  = 'inline';
        cvDel.style.display   = 'inline-flex';
    } else {
        cvNombre.textContent  = 'Sin archivo';
        cvLink.style.display  = 'none';
        cvDel.style.display   = 'none';
    }

    abrirModal('modal-editar');
}

async function guardarEditar() {
    const id = document.getElementById('e-id').value;
    const fd = new FormData();
    fd.append('_method',        'PUT');
    fd.append('nombres',        document.getElementById('e-nombres').value.trim());
    fd.append('apellidos',      document.getElementById('e-apellidos').value.trim());
    fd.append('rut',            document.getElementById('e-rut').value.trim());
    fd.append('cargo',          document.getElementById('e-cargo').value.trim());
    fd.append('contrato',       document.getElementById('e-contrato').value.trim());
    fd.append('nivel_estudios', document.getElementById('e-nivel').value.trim());
    fd.append('especialidad',   document.getElementById('e-especialidad').value.trim());
    fd.append('cumple',         document.getElementById('e-cumple').value);
    const cert = document.getElementById('e-cert').files[0];
    const cv   = document.getElementById('e-cv').files[0];
    if (cert) fd.append('cert_titulo', cert);
    if (cv)   fd.append('cv', cv);

    const res  = await mccFetch(`${ROUTES.base}/${id}`, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) { notif('ok', 'Cambios guardados'); cerrarModal('modal-editar'); setTimeout(() => location.reload(), 600); }
    else notif('err', data.message || 'Error al guardar');
}

// ── Eliminar ──────────────────────────────────────────────────────────────────
function confirmarEliminar(id, nombre) {
    document.getElementById('mcc-confirm-msg').textContent = `¿Eliminar al trabajador "${nombre}"? También se eliminarán sus documentos.`;
    document.getElementById('mcc-confirm-ok').onclick = async () => {
        const res  = await mccFetch(`${ROUTES.base}/${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) { cerrarModal('modal-confirm'); notif('ok', 'Trabajador eliminado'); setTimeout(() => location.reload(), 600); }
        else notif('err', 'Error al eliminar');
    };
    abrirModal('modal-confirm');
}

// ── Importar ──────────────────────────────────────────────────────────────────
async function ejecutarImport() {
    const archivo = document.getElementById('imp-archivo').files[0];
    if (!archivo) { notif('err', 'Selecciona un archivo Excel'); return; }

    const btn = document.getElementById('imp-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importando...';

    const fd = new FormData();
    fd.append('archivo_excel', archivo);

    try {
        const res  = await mccFetch(ROUTES.importar, { method: 'POST', body: fd });
        const data = await res.json();
        const div  = document.getElementById('imp-resultado');
        div.style.display = 'block';
        if (data.success) {
            div.style.cssText = 'display:block;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:6px;font-size:.82rem;margin-top:12px';
            div.innerHTML = `<i class="fa-solid fa-circle-check"></i> <strong>${data.mensaje}</strong>`;
            setTimeout(() => { cerrarModal('modal-importar'); location.reload(); }, 2500);
        } else {
            div.style.cssText = 'display:block;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:6px;font-size:.82rem;margin-top:12px';
            div.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> ${data.message || data.error || 'Error al importar'}`;
        }
    } catch (e) {
        notif('err', 'Error de conexión');
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa-solid fa-upload"></i> Importar';
    }
}

// ── Eliminar archivo individual ───────────────────────────────────────────────
function eliminarArchivo(tipo) {
    const id     = document.getElementById('e-id').value;
    const label  = tipo === 'cert' ? 'Certificado de Título' : 'Curriculum Vitae';
    sgcConfirm(`¿Eliminar el <strong>${label}</strong>?`, async () => {
        const res  = await mccFetch(`${ROUTES.base}/${id}/archivo/${tipo}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            notif('ok', 'Documento eliminado');
            const nombre = document.getElementById(`e-${tipo}-nombre`);
            const link   = document.getElementById(`e-${tipo}-link`);
            const del    = document.getElementById(`e-${tipo}-del`);
            nombre.textContent = 'Sin archivo';
            link.style.display = 'none';
            del.style.display  = 'none';
            setTimeout(() => location.reload(), 1500);
        } else {
            notif('err', 'Error al eliminar documento');
        }
    });
}

// ── Visualizador de documentos ────────────────────────────────────────────────
function verDoc(url, nombre, mime) {
    document.getElementById('viewer-titulo').textContent = nombre;
    document.getElementById('viewer-dl-link').href = url.replace('/ver/', '/descargar/');

    const cont    = document.getElementById('viewer-content');
    const esImage = mime && mime.startsWith('image/');

    if (esImage) {
        cont.innerHTML = `<div style="flex:1;overflow:auto;display:flex;align-items:center;justify-content:center;background:#334155">
            <img src="${url}" style="max-width:100%;max-height:100%;object-fit:contain" alt="${escHtml(nombre)}">
        </div>`;
    } else {
        cont.innerHTML = `<iframe src="${url}" style="width:100%;height:100%;border:none;flex:1"></iframe>`;
    }

    document.getElementById('modal-viewer').classList.add('visible');
}

function cerrarViewer() {
    document.getElementById('modal-viewer').classList.remove('visible');
    document.getElementById('viewer-content').innerHTML = '';
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
