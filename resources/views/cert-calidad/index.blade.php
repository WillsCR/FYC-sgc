@extends('layouts.app')
@section('title', 'Certificados de Calidad')

@push('styles')
<style>
:root {
    --cc-navy:   #0F6E56;
    --cc-blue:   #0891b2;
    --cc-green:  #16a34a;
    --cc-orange: #d97706;
    --cc-red:    #dc2626;
    --cc-border: #d1d5db;
    --cc-radius: 6px;
}

/* ── Page ── */
.cc-page { padding: 0; font-family: 'Inter', sans-serif; font-size: .82rem; }

/* ── Header ── */
.cc-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 24px 12px; background: #fff;
    border-bottom: 1px solid var(--cc-border);
    flex-wrap: wrap; gap: 10px;
}
.cc-header-title { font-size: 1rem; font-weight: 700; color: var(--cc-navy); letter-spacing: .01em; }
.cc-header-btns  { display: flex; gap: 8px; }

/* ── Filtros ── */
.cc-filters {
    background: #f8fafc; border-bottom: 1px solid var(--cc-border);
    padding: 10px 24px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.cc-filters input[type="text"],
.cc-filters select {
    height: 34px; padding: 0 10px;
    border: 1px solid var(--cc-border); border-radius: var(--cc-radius);
    font-size: .8rem; color: #374151; background: #fff;
}
.cc-filters input[type="text"] { width: 160px; }
.cc-filter-label { font-size: .72rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
.cc-filters label.cc-chk-label { font-size: .78rem; color: #374151; display: flex; align-items: center; gap: 5px; cursor: pointer; }

/* ── Botones ── */
.btn-cc {
    display: inline-flex; align-items: center; gap: 5px;
    height: 34px; padding: 0 14px;
    border: none; border-radius: var(--cc-radius);
    font-size: .8rem; font-weight: 600; cursor: pointer; transition: opacity .15s; white-space: nowrap;
}
.btn-cc:hover { opacity: .88; }
.btn-cc-green  { background: #16a34a; color: #fff; }
.btn-cc-navy   { background: var(--cc-navy); color: #fff; }
.btn-cc-blue   { background: var(--cc-blue); color: #fff; }
.btn-cc-gray   { background: #6b7280; color: #fff; }
.btn-cc-icon {
    width: 30px; height: 30px; padding: 0;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; border-radius: var(--cc-radius);
    cursor: pointer; transition: opacity .15s;
}
.btn-cc-icon:hover { opacity: .85; }
.btn-cc-edit { background: #eab308; color: #fff; }
.btn-cc-del  { background: #dc2626; color: #fff; }

/* ── Tabla ── */
.cc-table-wrap { padding: 16px 24px 40px; overflow-x: auto; }
.cc-table { width: 100%; border-collapse: collapse; font-size: .77rem; }
.cc-table th {
    background: var(--cc-navy); color: #fff;
    font-weight: 600; font-size: .68rem; letter-spacing: .04em;
    text-transform: uppercase; padding: 9px 10px;
    text-align: center; white-space: nowrap;
    border: 1px solid rgba(255,255,255,.1);
}
.cc-table td {
    padding: 7px 10px; border: 1px solid #e5e7eb;
    vertical-align: middle; background: #fff;
}
.cc-table tbody tr:hover td { background: #f8fafc; }
.cc-table td.td-desc   { color: #1e293b; font-weight: 500; }
.cc-table td.td-center { text-align: center; }

/* Badge aplica / critico */
.badge-si   { background: #dcfce7; color: #166534; border-radius: 10px; padding: 1px 8px; font-size: .68rem; font-weight: 700; }
.badge-no   { background: #f3f4f6; color: #6b7280; border-radius: 10px; padding: 1px 8px; font-size: .68rem; }

/* Semaforo */
.semaforo {
    display: inline-block; width: 18px; height: 18px;
    border-radius: 50%; border: 2px solid rgba(0,0,0,.15);
}
.semaforo-verde   { background: #16a34a; }
.semaforo-naranja { background: #f59e0b; }
.semaforo-rojo    { background: #dc2626; }
.semaforo-gris    { background: #9ca3af; }

/* Fecha vencida */
.fecha-vencida { background: #dc2626; color: #fff; border-radius: 4px; padding: 2px 6px; }
.fecha-proxima { background: #fef3c7; color: #92400e; border-radius: 4px; padding: 2px 6px; }

/* ── Modal ── */
.cc-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 1000;
    align-items: flex-start; justify-content: center;
    padding: 30px 20px; overflow-y: auto;
}
.cc-modal-overlay.visible { display: flex; }
.cc-modal {
    background: #fff; border-radius: 8px;
    box-shadow: 0 8px 40px rgba(0,0,0,.22);
    width: 100%; max-width: 780px; position: relative;
}
.cc-modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--cc-border);
}
.cc-modal-head h3 { font-size: .95rem; font-weight: 700; color: var(--cc-navy); margin: 0; }
.cc-modal-close { background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #6b7280; }
.cc-modal-body  { padding: 18px 20px; }
.cc-modal-foot  { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 20px; border-top: 1px solid var(--cc-border); }

/* Form grid */
.cc-form-grid { display: grid; gap: 10px; margin-bottom: 14px; }
.cc-form-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
.cc-form-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
.cc-form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
.cc-form-group label { display: block; font-size: .72rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.cc-form-group input[type="text"],
.cc-form-group input[type="date"],
.cc-form-group textarea,
.cc-form-group select {
    width: 100%; padding: 6px 8px;
    border: 1px solid var(--cc-border); border-radius: 4px;
    font-size: .78rem; color: #1e293b; background: #fff; box-sizing: border-box;
}
.cc-form-group input[type="text"] { height: 32px; }
.cc-form-group input[type="date"] { height: 32px; }
.cc-form-group input:focus, .cc-form-group textarea:focus { outline: none; border-color: var(--cc-blue); }
.cc-form-group .cc-chk-row { display: flex; align-items: center; gap: 8px; height: 32px; }
.cc-form-group .cc-chk-row label { margin: 0; font-size: .8rem; }

/* Toast */
#cc-toasts { position: fixed; bottom: 24px; right: 24px; display: flex; flex-direction: column; gap: 8px; z-index: 9999; }
.cc-toast { padding: 10px 18px; border-radius: 6px; font-size: .82rem; font-weight: 500; color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,.2); animation: ccSlide .25s ease; }
.cc-toast.ok  { background: #16a34a; }
.cc-toast.err { background: #dc2626; }
@keyframes ccSlide { from { transform: translateX(50px); opacity: 0; } to { transform: none; opacity: 1; } }

.cc-empty { text-align: center; padding: 40px; color: #9ca3af; font-size: .85rem; }

/* Separador de sección */
.cc-section-title { font-size: .78rem; font-weight: 700; color: var(--cc-navy); margin: 14px 0 10px; padding-bottom: 5px; border-bottom: 2px solid #e5e7eb; }
</style>
@endpush

@section('content')
<div class="cc-page">
<div id="cc-toasts"></div>

{{-- ── Header ── --}}
<div class="cc-header">
    <div class="cc-header-title">
        <i class="fa-solid fa-certificate" style="color:var(--cc-navy);margin-right:6px"></i>
        LISTA MAESTRA — CERTIFICADOS DE CALIDAD
    </div>
    <div class="cc-header-btns">
        @if($puedeGestionar)
        <button class="btn-cc btn-cc-green" onclick="abrirModal('modal-importar')">
            <i class="fa-solid fa-file-excel"></i> Importar Excel
        </button>
        <button class="btn-cc btn-cc-blue" onclick="abrirModalNuevo()">
            <i class="fa-solid fa-plus"></i> Nuevo Registro
        </button>
        @endif
        <a href="{{ route('panel') }}" class="btn-cc btn-cc-navy" style="background:#374151">
            <i class="fa-solid fa-house"></i> Panel Principal
        </a>
    </div>
</div>

{{-- ── Filtros ── --}}
<form id="form-filtros" method="GET" action="{{ route('cert-calidad.index') }}">
<div class="cc-filters">
    <span class="cc-filter-label">Filtrar:</span>
    <input type="text" name="descripcion" placeholder="Descripción" value="{{ request('descripcion') }}">
    <input type="text" name="tipo"        placeholder="Tipo certificado" value="{{ request('tipo') }}" style="width:140px">
    <input type="text" name="contrato"    placeholder="Contrato" value="{{ request('contrato') }}" style="width:120px">
    <input type="text" name="marca"       placeholder="Marca" value="{{ request('marca') }}" style="width:100px">
    <label class="cc-chk-label">
        <input type="checkbox" name="solo_aplica"  value="1" {{ request('solo_aplica')  ? 'checked' : '' }}>
        Solo Aplica
    </label>
    <label class="cc-chk-label">
        <input type="checkbox" name="solo_critico" value="1" {{ request('solo_critico') ? 'checked' : '' }}>
        Solo Crítico
    </label>
    <button type="submit" class="btn-cc btn-cc-navy" style="height:34px;padding:0 12px">
        <i class="fa-solid fa-filter"></i>
    </button>
    <a href="{{ route('cert-calidad.index') }}" class="btn-cc btn-cc-navy" style="background:#0891b2;height:34px;text-decoration:none">
        <i class="fa-solid fa-rotate"></i>
    </a>
</div>
</form>

{{-- ── Tabla ── --}}
<div class="cc-table-wrap">
    @php $total = $registros->count(); @endphp
    <div style="font-size:.75rem;color:#6b7280;margin-bottom:8px">
        {{ $total }} registro{{ $total !== 1 ? 's' : '' }} encontrado{{ $total !== 1 ? 's' : '' }}
    </div>

    @if($registros->isEmpty())
    <div class="cc-empty">
        <i class="fa-solid fa-certificate" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.3"></i>
        No hay certificados registrados.
        @if($puedeGestionar)
            <br><a href="#" onclick="abrirModalNuevo()" style="color:var(--cc-blue)">Agregar el primero</a> o importar desde Excel.
        @endif
    </div>
    @else
    <table class="cc-table">
        <thead>
            <tr>
                <th style="width:40px">N°</th>
                <th>DESCRIPCIÓN DEL EQUIPO/HERRAMIENTA</th>
                <th>TIPO CERTIFICADO</th>
                <th>CONTRATO</th>
                <th style="width:60px">APLICA</th>
                <th style="width:60px">CRÍTICO</th>
                <th>MARCA</th>
                <th>MODELO</th>
                <th>PROCEDIMIENTO ASOCIADO</th>
                <th>VENCIMIENTO</th>
                <th style="width:60px">ESTADO</th>
                <th>OBSERVACIONES</th>
                <th>ARCHIVO</th>
                @if($puedeGestionar)
                <th style="width:80px">ACCIONES</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @foreach($registros as $r)
        @php
            $sem = $r->semaforo;
            $fvClass = $sem === 'rojo' ? 'fecha-vencida' : ($sem === 'naranja' ? 'fecha-proxima' : '');
        @endphp
        <tr>
            <td class="td-center" style="color:#9ca3af;font-weight:600">{{ $r->numero ?? '—' }}</td>
            <td class="td-desc">{{ $r->descripcion }}</td>
            <td style="white-space:nowrap">{{ $r->tipo_certificado ?? '—' }}</td>
            <td>{{ $r->contrato ?? '—' }}</td>
            <td class="td-center">
                @if($r->aplica)
                    <span class="badge-si">Sí</span>
                @else
                    <span class="badge-no">No</span>
                @endif
            </td>
            <td class="td-center">
                @if($r->critico)
                    <span class="badge-si" style="background:#fee2e2;color:#991b1b">Sí</span>
                @else
                    <span class="badge-no">No</span>
                @endif
            </td>
            <td>{{ $r->marca ?? '—' }}</td>
            <td>{{ $r->modelo ?? '—' }}</td>
            <td style="font-size:.72rem;color:#374151">{{ $r->procedimiento_asociado ?? '—' }}</td>
            <td class="td-center" style="white-space:nowrap">
                @if($r->vencimiento)
                    <span class="{{ $fvClass }}">{{ $r->vencimiento->format('d-m-Y') }}</span>
                @else
                    <span style="color:#9ca3af">—</span>
                @endif
            </td>
            <td class="td-center">
                <span class="semaforo semaforo-{{ $sem }}" title="{{ $sem }}"></span>
            </td>
            <td style="font-size:.72rem;color:#374151;max-width:180px">{{ $r->observaciones ?? '—' }}</td>
            <td class="td-center">
                @if($r->archivo_ruta)
                    <a href="{{ route('cert-calidad.descargar', $r->id) }}"
                       style="color:var(--cc-blue);font-weight:600;text-decoration:none;font-size:.75rem">
                        <i class="fa-solid fa-download"></i> Descargar
                    </a>
                @else
                    <span style="color:#9ca3af">—</span>
                @endif
            </td>
            @if($puedeGestionar)
            <td class="td-center" style="white-space:nowrap">
                <button class="btn-cc-icon btn-cc-edit"
                        onclick="abrirModalEditar({{ $r->id }})" title="Editar">
                    <i class="fa-solid fa-pencil" style="font-size:.65rem"></i>
                </button>
                <button class="btn-cc-icon btn-cc-del" style="margin-left:3px"
                        onclick="confirmarEliminar({{ $r->id }}, '{{ addslashes(Str::limit($r->descripcion, 50)) }}')" title="Eliminar">
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

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- Modal: Nuevo / Editar registro --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div id="modal-form" class="cc-modal-overlay"
     onclick="if(event.target===this)cerrarModal('modal-form')">
    <div class="cc-modal">
        <div class="cc-modal-head">
            <h3 id="modal-form-titulo"><i class="fa-solid fa-certificate" style="margin-right:6px"></i>Nuevo Certificado</h3>
            <button class="cc-modal-close" onclick="cerrarModal('modal-form')">&times;</button>
        </div>
        <div class="cc-modal-body">
            <input type="hidden" id="form-id">

            <p class="cc-section-title">Información del Equipo / Herramienta</p>
            <div class="cc-form-grid cols-4">
                <div class="cc-form-group">
                    <label>N°</label>
                    <input type="text" id="f-numero" placeholder="1">
                </div>
                <div class="cc-form-group" style="grid-column:span 2">
                    <label>Descripción <span style="color:red">*</span></label>
                    <input type="text" id="f-descripcion" placeholder="Ej: Balanza electrónica">
                </div>
                <div class="cc-form-group">
                    <label>Contrato</label>
                    <input type="text" id="f-contrato" placeholder="Ej: Pozos">
                </div>
                <div class="cc-form-group" style="grid-column:span 2">
                    <label>Tipo de Certificado</label>
                    <input type="text" id="f-tipo" placeholder="Ej: Calidad / Calibración">
                </div>
                <div class="cc-form-group">
                    <label>Marca</label>
                    <input type="text" id="f-marca" placeholder="Ej: Mettler">
                </div>
                <div class="cc-form-group">
                    <label>Modelo</label>
                    <input type="text" id="f-modelo" placeholder="Ej: XS205">
                </div>
            </div>

            <div class="cc-form-grid cols-4">
                <div class="cc-form-group">
                    <label>Aplica</label>
                    <div class="cc-chk-row">
                        <input type="checkbox" id="f-aplica" style="width:auto;height:auto">
                        <label for="f-aplica">Sí aplica</label>
                    </div>
                </div>
                <div class="cc-form-group">
                    <label>Crítico</label>
                    <div class="cc-chk-row">
                        <input type="checkbox" id="f-critico" style="width:auto;height:auto">
                        <label for="f-critico">Es crítico</label>
                    </div>
                </div>
                <div class="cc-form-group" style="grid-column:span 2">
                    <label>Fecha de Vencimiento</label>
                    <input type="date" id="f-vencimiento">
                </div>
            </div>

            <div class="cc-form-grid cols-2">
                <div class="cc-form-group">
                    <label>Procedimiento Asociado</label>
                    <input type="text" id="f-procedimiento" placeholder="Ej: PRO-CAL-001">
                </div>
                <div class="cc-form-group">
                    <label>Archivo de Certificado</label>
                    <div style="display:flex;align-items:center;gap:6px">
                        <input type="file" id="f-archivo" style="display:none" accept=".pdf,.jpg,.jpeg,.png">
                        <button type="button" onclick="document.getElementById('f-archivo').click()"
                            style="height:32px;padding:0 10px;border:1px solid var(--cc-border);border-radius:4px;cursor:pointer;font-size:.78rem;background:#f8fafc">
                            <i class="fa-solid fa-paperclip"></i> Elegir archivo
                        </button>
                        <span id="f-archivo-nombre" style="font-size:.72rem;color:#6b7280"></span>
                    </div>
                    <div id="f-archivo-actual" style="font-size:.72rem;color:var(--cc-blue);margin-top:4px"></div>
                </div>
            </div>

            <div class="cc-form-grid cols-2">
                <div class="cc-form-group" style="grid-column:span 2">
                    <label>Observaciones</label>
                    <textarea id="f-observaciones" rows="2" placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>
        </div>
        <div class="cc-modal-foot">
            <button class="btn-cc btn-cc-gray" onclick="cerrarModal('modal-form')">Cancelar</button>
            <button id="btn-guardar" class="btn-cc btn-cc-green" onclick="guardarRegistro()">
                <i class="fa-solid fa-floppy-disk"></i> Guardar
            </button>
        </div>
    </div>
</div>

{{-- Modal: Importar Excel --}}
<div id="modal-importar" class="cc-modal-overlay"
     onclick="if(event.target===this)cerrarModal('modal-importar')">
    <div class="cc-modal" style="max-width:500px">
        <div class="cc-modal-head">
            <h3><i class="fa-solid fa-file-excel" style="color:#16a34a;margin-right:6px"></i>Importar desde Excel</h3>
            <button class="cc-modal-close" onclick="cerrarModal('modal-importar')">&times;</button>
        </div>
        <div class="cc-modal-body">
            <p style="font-size:.83rem;color:#374151;margin:0 0 14px">
                Importa registros desde el archivo <strong>Lista_maestra_certificados_calidad.xlsx</strong>.<br>
                Se procesará la hoja <strong>"LM Certificados"</strong>.
                Los duplicados (misma descripción + contrato) se omitirán automáticamente.
            </p>
            <div style="background:#fefce8;border:1px solid #fde68a;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:.78rem;color:#92400e">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:5px"></i>
                Los registros existentes no se modificarán. Solo se agregarán entradas nuevas.
            </div>
            <div class="cc-form-group">
                <label>Seleccionar archivo Excel (.xlsx / .xlsm)</label>
                <input type="file" id="imp-archivo" accept=".xlsx,.xls,.xlsm"
                       style="display:block;width:100%;padding:6px;border:1px solid var(--cc-border);border-radius:4px;font-size:.8rem;margin-top:4px">
            </div>
            <div id="imp-resultado" style="display:none;margin-top:12px;padding:10px 14px;border-radius:6px;font-size:.82rem"></div>
        </div>
        <div class="cc-modal-foot">
            <button class="btn-cc btn-cc-gray" onclick="cerrarModal('modal-importar')">Cancelar</button>
            <button id="imp-btn" class="btn-cc btn-cc-green" onclick="ejecutarImport()">
                <i class="fa-solid fa-upload"></i> Importar
            </button>
        </div>
    </div>
</div>

{{-- Modal: Confirmar eliminar --}}
<div id="modal-confirm" class="cc-modal-overlay"
     onclick="if(event.target===this)cerrarModal('modal-confirm')">
    <div class="cc-modal" style="max-width:420px;margin-top:120px">
        <div class="cc-modal-head">
            <h3><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;margin-right:6px"></i>Confirmar eliminación</h3>
            <button class="cc-modal-close" onclick="cerrarModal('modal-confirm')">&times;</button>
        </div>
        <div class="cc-modal-body">
            <p id="cc-confirm-msg" style="color:#374151;font-size:.88rem;margin:0"></p>
        </div>
        <div class="cc-modal-foot">
            <button class="btn-cc btn-cc-gray" onclick="cerrarModal('modal-confirm')">Cancelar</button>
            <button id="cc-confirm-ok" class="btn-cc" style="background:#dc2626;color:#fff">
                <i class="fa-solid fa-trash"></i> Eliminar
            </button>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
const CC_ROUTES = {
    store:   '{{ route('cert-calidad.store') }}',
    base:    '{{ url('cert-calidad') }}',
    importar:'{{ route('cert-calidad.importar') }}',
    index:   '{{ route('cert-calidad.index') }}',
};

// ── Helpers ──────────────────────────────────────────────────────────────────
function notif(type, msg) {
    const d = document.createElement('div');
    d.className = 'cc-toast ' + (type === 'ok' ? 'ok' : 'err');
    d.textContent = msg;
    document.getElementById('cc-toasts').appendChild(d);
    setTimeout(() => d.remove(), 3500);
}
function abrirModal(id)  { document.getElementById(id).classList.add('visible'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('visible'); }
async function ccFetch(url, opts = {}) {
    const headers = { 'X-CSRF-TOKEN': window.CSRF_TOKEN, ...(opts.headers || {}) };
    if (!(opts.body instanceof FormData)) headers['Content-Type'] = 'application/json';
    return fetch(url, { ...opts, headers });
}

// ── Nuevo registro ────────────────────────────────────────────────────────────
function abrirModalNuevo() {
    document.getElementById('form-id').value = '';
    document.getElementById('modal-form-titulo').innerHTML = '<i class="fa-solid fa-plus" style="margin-right:6px"></i>Nuevo Certificado';
    document.getElementById('btn-guardar').innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar';
    ['f-numero','f-descripcion','f-contrato','f-tipo','f-marca','f-modelo','f-procedimiento','f-observaciones'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('f-aplica').checked = false;
    document.getElementById('f-critico').checked = false;
    document.getElementById('f-vencimiento').value = '';
    document.getElementById('f-archivo').value = '';
    document.getElementById('f-archivo-nombre').textContent = '';
    document.getElementById('f-archivo-actual').innerHTML = '';
    abrirModal('modal-form');
}

// ── Editar registro ───────────────────────────────────────────────────────────
async function abrirModalEditar(id) {
    const res  = await ccFetch(`${CC_ROUTES.base}/${id}/datos`);
    const data = await res.json();

    document.getElementById('form-id').value = data.id;
    document.getElementById('modal-form-titulo').innerHTML = '<i class="fa-solid fa-pencil" style="margin-right:6px"></i>Editar Certificado';
    document.getElementById('f-numero').value       = data.numero       || '';
    document.getElementById('f-descripcion').value  = data.descripcion  || '';
    document.getElementById('f-contrato').value     = data.contrato     || '';
    document.getElementById('f-tipo').value         = data.tipo_certificado || '';
    document.getElementById('f-marca').value        = data.marca        || '';
    document.getElementById('f-modelo').value       = data.modelo       || '';
    document.getElementById('f-vencimiento').value  = data.vencimiento  || '';
    document.getElementById('f-procedimiento').value = data.procedimiento_asociado || '';
    document.getElementById('f-observaciones').value = data.observaciones || '';
    document.getElementById('f-aplica').checked     = !!data.aplica;
    document.getElementById('f-critico').checked    = !!data.critico;
    document.getElementById('f-archivo').value      = '';
    document.getElementById('f-archivo-nombre').textContent = '';
    document.getElementById('f-archivo-actual').innerHTML   = data.archivo_nombre
        ? `<i class="fa-solid fa-paperclip"></i> Actual: <strong>${data.archivo_nombre}</strong>`
        : '';
    document.getElementById('btn-guardar').innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar cambios';

    abrirModal('modal-form');
}

// ── Guardar (crear o actualizar) ──────────────────────────────────────────────
async function guardarRegistro() {
    const id = document.getElementById('form-id').value;
    const descripcion = document.getElementById('f-descripcion').value.trim();
    if (!descripcion) { notif('err', 'La descripción es obligatoria'); return; }

    const fd = new FormData();
    fd.append('descripcion',          descripcion);
    fd.append('numero',               document.getElementById('f-numero').value.trim());
    fd.append('contrato',             document.getElementById('f-contrato').value.trim());
    fd.append('tipo_certificado',     document.getElementById('f-tipo').value.trim());
    fd.append('marca',                document.getElementById('f-marca').value.trim());
    fd.append('modelo',               document.getElementById('f-modelo').value.trim());
    fd.append('vencimiento',          document.getElementById('f-vencimiento').value);
    fd.append('procedimiento_asociado', document.getElementById('f-procedimiento').value.trim());
    fd.append('observaciones',        document.getElementById('f-observaciones').value.trim());
    fd.append('aplica',               document.getElementById('f-aplica').checked ? '1' : '0');
    fd.append('critico',              document.getElementById('f-critico').checked ? '1' : '0');
    const archivo = document.getElementById('f-archivo').files[0];
    if (archivo) fd.append('archivo', archivo);

    const url    = id ? `${CC_ROUTES.base}/${id}` : CC_ROUTES.store;
    const method = 'POST';
    if (id) fd.append('_method', 'POST');

    const btn = document.getElementById('btn-guardar');
    btn.disabled = true;
    try {
        const res  = await ccFetch(url, { method, body: fd });
        const data = await res.json();
        if (data.success) {
            notif('ok', data.message || 'Guardado correctamente');
            cerrarModal('modal-form');
            setTimeout(() => location.reload(), 600);
        } else {
            notif('err', data.message || 'Error al guardar');
        }
    } catch(e) {
        notif('err', 'Error de conexión');
    }
    btn.disabled = false;
}

// ── Eliminar ──────────────────────────────────────────────────────────────────
function confirmarEliminar(id, desc) {
    document.getElementById('cc-confirm-msg').textContent = `¿Eliminar el certificado "${desc}"? Esta acción no se puede deshacer.`;
    document.getElementById('cc-confirm-ok').onclick = async () => {
        const res  = await ccFetch(`${CC_ROUTES.base}/${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            cerrarModal('modal-confirm');
            notif('ok', 'Certificado eliminado');
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
        const res  = await ccFetch(CC_ROUTES.importar, { method: 'POST', body: fd });
        const data = await res.json();
        const div  = document.getElementById('imp-resultado');
        div.style.display = 'block';
        if (data.success) {
            div.style.cssText = 'display:block;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:6px;font-size:.82rem;margin-top:12px';
            div.innerHTML = `<i class="fa-solid fa-circle-check"></i> <strong>${data.mensaje}</strong>`;
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa-solid fa-upload"></i> Importar';
            setTimeout(() => { cerrarModal('modal-importar'); location.reload(); }, 2500);
        } else {
            div.style.cssText = 'display:block;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:6px;font-size:.82rem;margin-top:12px';
            div.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> ${data.message || 'Error al importar'}`;
            btn.disabled  = false;
            btn.innerHTML = '<i class="fa-solid fa-upload"></i> Importar';
        }
    } catch(e) {
        notif('err', 'Error de conexión');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa-solid fa-upload"></i> Importar';
    }
}

// Mostrar nombre de archivo seleccionado
document.getElementById('f-archivo').addEventListener('change', function () {
    document.getElementById('f-archivo-nombre').textContent = this.files[0]?.name || '';
});
</script>
@endpush
