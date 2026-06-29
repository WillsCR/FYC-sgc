@extends('layouts.app')
@section('title', 'Control Flota')

@push('styles')
<style>
:root {
    --fl-navy:   #1E3A5F;
    --fl-blue:   #0891b2;
    --fl-green:  #16a34a;
    --fl-orange: #d97706;
    --fl-red:    #dc2626;
    --fl-border: #d1d5db;
    --fl-radius: 6px;
}

/* ── Page ── */
.fl-page { padding: 0; font-family: 'Inter', sans-serif; font-size: .82rem; }

/* ── Header ── */
.fl-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 24px 12px; background: #fff;
    border-bottom: 1px solid var(--fl-border);
    flex-wrap: wrap; gap: 10px;
}
.fl-header-title { font-size: 1rem; font-weight: 700; color: var(--fl-navy); letter-spacing: .01em; }
.fl-header-btns  { display: flex; gap: 8px; flex-wrap: wrap; }

/* ── Tarjetas resumen ── */
.fl-stats {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 10px; padding: 14px 24px 0;
}
.fl-stat-card {
    background: #fff; border: 1px solid var(--fl-border); border-radius: 8px;
    padding: 12px 16px; display: flex; align-items: center; gap: 12px;
}
.fl-stat-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.fl-stat-val  { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.fl-stat-lbl  { font-size: .7rem; color: #6b7280; margin-top: 2px; }
.fl-stat-total   .fl-stat-icon { background: #dbeafe; color: #1d4ed8; }
.fl-stat-venc    .fl-stat-icon { background: #fee2e2; color: #dc2626; }
.fl-stat-pv      .fl-stat-icon { background: #fef3c7; color: #d97706; }
.fl-stat-km      .fl-stat-icon { background: #f3e8ff; color: #7c3aed; }
.fl-stat-total   .fl-stat-val  { color: #1d4ed8; }
.fl-stat-venc    .fl-stat-val  { color: #dc2626; }
.fl-stat-pv      .fl-stat-val  { color: #d97706; }
.fl-stat-km      .fl-stat-val  { color: #7c3aed; }

/* ── Tabs ── */
.fl-tabs-bar {
    display: flex; gap: 0; padding: 12px 24px 0;
    border-bottom: 1px solid var(--fl-border);
    background: #fff;
}
.fl-tab {
    padding: 8px 18px; font-size: .8rem; font-weight: 600;
    color: #6b7280; border: none; background: none; cursor: pointer;
    border-bottom: 3px solid transparent; margin-bottom: -1px; transition: color .15s;
}
.fl-tab:hover { color: var(--fl-navy); }
.fl-tab.active { color: var(--fl-navy); border-bottom-color: var(--fl-navy); }
.fl-tab-panel { display: none; }
.fl-tab-panel.active { display: block; }

/* ── Filtros ── */
.fl-filters {
    background: #f8fafc; border-bottom: 1px solid var(--fl-border);
    padding: 10px 24px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.fl-filters input[type="text"],
.fl-filters select {
    height: 34px; padding: 0 10px;
    border: 1px solid var(--fl-border); border-radius: var(--fl-radius);
    font-size: .8rem; color: #374151; background: #fff;
}
.fl-filters input[type="text"] { width: 160px; }
.fl-filter-label { font-size: .72rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }

/* ── Botones ── */
.btn-fl {
    display: inline-flex; align-items: center; gap: 5px;
    height: 34px; padding: 0 14px;
    border: none; border-radius: var(--fl-radius);
    font-size: .8rem; font-weight: 600; cursor: pointer; transition: opacity .15s; white-space: nowrap;
    text-decoration: none;
}
.btn-fl:hover { opacity: .88; }
.btn-fl-green  { background: #16a34a; color: #fff; }
.btn-fl-navy   { background: var(--fl-navy); color: #fff; }
.btn-fl-blue   { background: var(--fl-blue); color: #fff; }
.btn-fl-gray   { background: #6b7280; color: #fff; }
.btn-fl-icon {
    width: 30px; height: 30px; padding: 0;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; border-radius: var(--fl-radius);
    cursor: pointer; transition: opacity .15s;
}
.btn-fl-icon:hover { opacity: .85; }
.btn-fl-edit { background: #eab308; color: #fff; }
.btn-fl-del  { background: #dc2626; color: #fff; }

/* ── Tabla ── */
.fl-table-wrap { padding: 14px 24px 40px; overflow-x: auto; }
.fl-table { width: 100%; border-collapse: collapse; font-size: .75rem; }
.fl-table th {
    background: var(--fl-navy); color: #fff;
    font-weight: 600; font-size: .65rem; letter-spacing: .04em;
    text-transform: uppercase; padding: 8px 8px;
    text-align: center; white-space: nowrap;
    border: 1px solid rgba(255,255,255,.1);
}
.fl-table td {
    padding: 6px 8px; border: 1px solid #e5e7eb;
    vertical-align: middle; background: #fff;
}
.fl-table tbody tr:hover td { background: #f8fafc; }
.fl-table td.td-center { text-align: center; }
.fl-table td.td-equipo { color: #1e293b; font-weight: 600; white-space: nowrap; }

/* Semáforo */
.semaforo {
    display: inline-block; width: 16px; height: 16px;
    border-radius: 50%; border: 2px solid rgba(0,0,0,.15);
    vertical-align: middle;
}
.semaforo-verde   { background: #16a34a; }
.semaforo-naranja { background: #f59e0b; }
.semaforo-rojo    { background: #dc2626; }
.semaforo-gris    { background: #9ca3af; }

/* Celda de cert en tabla */
.cert-cell { min-width: 82px; text-align: center; }
.cert-fecha { font-size: .68rem; display: block; white-space: nowrap; }
.cert-dias  { font-size: .63rem; color: #9ca3af; display: block; }
.cert-bg-verde   { background: #f0fdf4; }
.cert-bg-naranja { background: #fffbeb; }
.cert-bg-rojo    { background: #fef2f2; }
.cert-bg-gris    { background: #f9fafb; }

/* Km */
.km-bar-wrap { background: #f3f4f6; border-radius: 10px; height: 8px; min-width: 60px; }
.km-bar      { height: 8px; border-radius: 10px; transition: width .3s; }

/* Modal */
.fl-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 1000;
    align-items: flex-start; justify-content: center;
    padding: 30px 20px; overflow-y: auto;
}
.fl-modal-overlay.visible { display: flex; }
.fl-modal {
    background: #fff; border-radius: 8px;
    box-shadow: 0 8px 40px rgba(0,0,0,.22);
    width: 100%; max-width: 1020px; position: relative;
}
.fl-modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--fl-border);
}
.fl-modal-head h3 { font-size: .95rem; font-weight: 700; color: var(--fl-navy); margin: 0; }
.fl-modal-close { background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #6b7280; }
.fl-modal-body  { padding: 18px 20px; }
.fl-modal-foot  { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 20px; border-top: 1px solid var(--fl-border); }

/* Form */
.fl-form-grid { display: grid; gap: 10px; margin-bottom: 12px; }
.fl-form-grid.cols-5 { grid-template-columns: repeat(5, 1fr); }
.fl-form-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
.fl-form-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
.fl-form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
.fl-form-group label { display: block; font-size: .71rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.fl-form-group input[type="text"],
.fl-form-group input[type="date"],
.fl-form-group input[type="number"],
.fl-form-group textarea,
.fl-form-group select {
    width: 100%; padding: 6px 8px;
    border: 1px solid var(--fl-border); border-radius: 4px;
    font-size: .78rem; color: #1e293b; background: #fff; box-sizing: border-box;
    height: 32px;
}
.fl-form-group textarea { height: auto; resize: vertical; }
.fl-form-group input:focus, .fl-form-group textarea:focus { outline: none; border-color: var(--fl-blue); }
.fl-section-title { font-size: .78rem; font-weight: 700; color: var(--fl-navy); margin: 14px 0 10px; padding-bottom: 5px; border-bottom: 2px solid #e5e7eb; }

/* Toast */
#fl-toasts { position: fixed; bottom: 24px; right: 24px; display: flex; flex-direction: column; gap: 8px; z-index: 9999; }
.fl-toast { padding: 10px 18px; border-radius: 6px; font-size: .82rem; font-weight: 500; color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,.2); animation: flSlide .25s ease; }
.fl-toast.ok  { background: #16a34a; }
.fl-toast.err { background: #dc2626; }
@keyframes flSlide { from { transform: translateX(50px); opacity: 0; } to { transform: none; opacity: 1; } }

.fl-empty { text-align: center; padding: 40px; color: #9ca3af; font-size: .85rem; }

@media (max-width: 900px) {
    .fl-stats { grid-template-columns: repeat(2, 1fr); }
    .fl-form-grid.cols-5 { grid-template-columns: repeat(2, 1fr); }
    .fl-form-grid.cols-4 { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@section('content')
<div class="fl-page">
<div id="fl-toasts"></div>

{{-- ── Header ── --}}
<div class="fl-header">
    <div class="fl-header-title">
        <i class="fa-solid fa-truck" style="color:var(--fl-navy);margin-right:6px"></i>
        CONTROL DE FLOTA
    </div>
    <div class="fl-header-btns">
        @if($usuario->esAdmin())
        <button class="btn-fl btn-fl-green" onclick="abrirModal('modal-importar')">
            <i class="fa-solid fa-file-excel"></i> Importar Excel
        </button>
        @endif
        @if($puedeGestionar)
        <button class="btn-fl btn-fl-navy" onclick="abrirModalNuevo()">
            <i class="fa-solid fa-plus"></i> Nuevo Equipo
        </button>
        @endif
        <a href="{{ route('flota.exportar') }}" class="btn-fl btn-fl-green">
            <i class="fa-solid fa-file-excel"></i> Exportar Excel
        </a>
        <a href="{{ route('panel') }}" class="btn-fl btn-fl-gray">
            <i class="fa-solid fa-house"></i> Panel Principal
        </a>
    </div>
</div>

{{-- ── Tarjetas Resumen ── --}}
<div class="fl-stats">
    <div class="fl-stat-card fl-stat-total">
        <div class="fl-stat-icon"><i class="fa-solid fa-truck"></i></div>
        <div><div class="fl-stat-val">{{ $totalEquipos }}</div><div class="fl-stat-lbl">Total equipos</div></div>
    </div>
    <div class="fl-stat-card fl-stat-venc">
        <div class="fl-stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div><div class="fl-stat-val">{{ $certVencidas }}</div><div class="fl-stat-lbl">Cert. vencidas</div></div>
    </div>
    <div class="fl-stat-card fl-stat-pv">
        <div class="fl-stat-icon"><i class="fa-solid fa-clock"></i></div>
        <div><div class="fl-stat-val">{{ $certPorVencer }}</div><div class="fl-stat-lbl">Por vencer (30 días)</div></div>
    </div>
    <div class="fl-stat-card fl-stat-km">
        <div class="fl-stat-icon"><i class="fa-solid fa-gauge-high"></i></div>
        <div><div class="fl-stat-val">{{ $kmCriticos }}</div><div class="fl-stat-lbl">Km críticos</div></div>
    </div>
</div>

{{-- ── Filtros ── --}}
<form id="form-filtros" method="GET" action="{{ route('flota.index') }}">
<div class="fl-filters" style="margin-top:12px">
    <span class="fl-filter-label">Filtrar:</span>
    <input type="text" name="buscar" placeholder="Equipo, patente, marca…" value="{{ request('buscar') }}" style="width:200px">
    <input type="text" name="area"   placeholder="Área" value="{{ request('area') }}" style="width:130px">
    <button type="submit" class="btn-fl btn-fl-navy" style="height:34px;padding:0 12px">
        <i class="fa-solid fa-filter"></i>
    </button>
    <a href="{{ route('flota.index') }}" class="btn-fl btn-fl-blue" style="height:34px">
        <i class="fa-solid fa-rotate"></i>
    </a>
</div>
</form>

{{-- ── Tabs ── --}}
<div class="fl-tabs-bar">
    <button class="fl-tab active" onclick="cambiarTab('tab-cert',this)">
        <i class="fa-solid fa-certificate"></i> Certificaciones
    </button>
    <button class="fl-tab" onclick="cambiarTab('tab-km',this)">
        <i class="fa-solid fa-gauge-high"></i> Kilometrajes
    </button>
</div>

{{-- ══ TAB CERTIFICACIONES ══ --}}
<div id="tab-cert" class="fl-tab-panel active">
<div class="fl-table-wrap">
    <div style="font-size:.75rem;color:#6b7280;margin-bottom:8px">
        {{ $equipos->count() }} equipo{{ $equipos->count() !== 1 ? 's' : '' }}
    </div>
    @if($equipos->isEmpty())
    <div class="fl-empty">
        <i class="fa-solid fa-truck" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.3"></i>
        No hay equipos registrados.
        @if($puedeGestionar)
            <br><a href="#" onclick="abrirModalNuevo()" style="color:var(--fl-blue)">Agregar el primero</a>.
        @endif
    </div>
    @else
    <div style="overflow-x:auto">
    <table class="fl-table">
        <thead>
            <tr>
                <th rowspan="2" style="min-width:110px">EQUIPO</th>
                <th rowspan="2">PATENTE</th>
                <th rowspan="2">ÁREA</th>
                <th rowspan="2" style="width:50px">EST.</th>
                @foreach(\App\Models\FlotaEquipo::certFields() as $campo => $etiqueta)
                <th style="min-width:80px">{{ strtoupper($etiqueta) }}</th>
                @endforeach
                @if($puedeGestionar)
                <th rowspan="2" style="width:70px">ACCIONES</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @foreach($equipos as $eq)
        @php $sem = $eq->semaforo_cert; @endphp
        <tr>
            <td class="td-equipo">{{ $eq->equipo }}</td>
            <td class="td-center" style="white-space:nowrap">{{ $eq->patente ?? '—' }}</td>
            <td style="white-space:nowrap">{{ $eq->area ?? '—' }}</td>
            <td class="td-center">
                <span class="semaforo semaforo-{{ $sem }}" title="{{ ucfirst($sem) }}"></span>
            </td>
            @foreach(array_keys(\App\Models\FlotaEquipo::certFields()) as $campo)
            @php
                $s   = $eq->semaforoCert($campo);
                $f   = $eq->$campo;
                $dias = $eq->diasRestantesCert($campo);
            @endphp
            <td class="cert-cell cert-bg-{{ $s }}">
                @if($f)
                    <span class="cert-fecha" style="{{ $s==='rojo'?'color:#dc2626;font-weight:700':($s==='naranja'?'color:#92400e':'') }}">
                        {{ $f->format('d/m/Y') }}
                    </span>
                    <span class="cert-dias">
                        @if($dias < 0)
                            <span style="color:#dc2626">Vencido</span>
                        @elseif($dias === 0)
                            <span style="color:#dc2626">Hoy</span>
                        @else
                            {{ $dias }}d
                        @endif
                    </span>
                @else
                    <span style="color:#d1d5db">—</span>
                @endif
            </td>
            @endforeach
            @if($puedeGestionar)
            <td class="td-center">
                <div style="display:flex;gap:4px;justify-content:center">
                    <button class="btn-fl-icon btn-fl-edit" onclick="editarEquipo({{ $eq->id }})" title="Editar">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn-fl-icon btn-fl-del" onclick="eliminarEquipo({{ $eq->id }}, '{{ addslashes($eq->equipo) }}')" title="Eliminar">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
            @endif
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @endif
</div>
</div>

{{-- ══ TAB KILOMETRAJES ══ --}}
<div id="tab-km" class="fl-tab-panel">
<div class="fl-table-wrap">
    @if($equipos->isEmpty())
    <div class="fl-empty">
        <i class="fa-solid fa-gauge-high" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.3"></i>
        No hay equipos registrados.
    </div>
    @else
    <table class="fl-table">
        <thead>
            <tr>
                <th>EQUIPO</th>
                <th>PATENTE</th>
                <th>ÁREA</th>
                <th>KM ACTUAL</th>
                <th>PRÓX. MANTENCIÓN</th>
                <th>KM RESTANTES</th>
                <th style="min-width:120px">PROGRESO</th>
                <th>ESTADO</th>
                <th>RESPONSABLE</th>
                <th>OBSERVACIONES</th>
                @if($puedeGestionar)
                <th style="width:70px">ACCIONES</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @foreach($equipos as $eq)
        @php
            $semKm  = $eq->semaforo_km;
            $kmR    = $eq->km_restantes;
            $pct    = null;
            if ($eq->km_proxima_mantencion && $eq->km_proxima_mantencion > 0 && $eq->km_actual !== null) {
                $pct = min(100, max(0, round(($eq->km_actual / $eq->km_proxima_mantencion) * 100)));
            }
            $barColor = match($semKm) { 'rojo' => '#dc2626', 'naranja' => '#f59e0b', 'verde' => '#16a34a', default => '#9ca3af' };
        @endphp
        <tr>
            <td class="td-equipo">{{ $eq->equipo }}</td>
            <td class="td-center">{{ $eq->patente ?? '—' }}</td>
            <td>{{ $eq->area ?? '—' }}</td>
            <td class="td-center">{{ $eq->km_actual ? number_format($eq->km_actual) : '—' }}</td>
            <td class="td-center">{{ $eq->km_proxima_mantencion ? number_format($eq->km_proxima_mantencion) : '—' }}</td>
            <td class="td-center" style="{{ $semKm==='rojo'?'color:#dc2626;font-weight:700':($semKm==='naranja'?'color:#d97706;font-weight:600':'') }}">
                @if($kmR !== null)
                    {{ $kmR >= 0 ? number_format($kmR) : '—' }}
                    @if($kmR < 0)
                        <span style="font-size:.65rem;color:#dc2626">EXCEDIDO</span>
                    @endif
                @else
                    —
                @endif
            </td>
            <td>
                @if($pct !== null)
                <div class="km-bar-wrap">
                    <div class="km-bar" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
                </div>
                <div style="font-size:.63rem;color:#6b7280;margin-top:2px;text-align:center">{{ $pct }}%</div>
                @else
                    <span style="color:#d1d5db">—</span>
                @endif
            </td>
            <td class="td-center">
                <span class="semaforo semaforo-{{ $semKm }}" title="{{ ucfirst($semKm) }}"></span>
            </td>
            <td style="font-size:.75rem">{{ $eq->responsable ?? '—' }}</td>
            <td style="font-size:.73rem;color:#6b7280;max-width:160px">{{ $eq->observaciones ?? '—' }}</td>
            @if($puedeGestionar)
            <td class="td-center">
                <div style="display:flex;gap:4px;justify-content:center">
                    <button class="btn-fl-icon btn-fl-edit" onclick="editarEquipo({{ $eq->id }})" title="Editar">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn-fl-icon btn-fl-del" onclick="eliminarEquipo({{ $eq->id }}, '{{ addslashes($eq->equipo) }}')" title="Eliminar">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
            @endif
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
</div>

{{-- ══ MODAL NUEVO / EDITAR ══ --}}
<div id="modal-equipo" class="fl-modal-overlay">
<div class="fl-modal">
    <div class="fl-modal-head">
        <h3 id="modal-equipo-title"><i class="fa-solid fa-truck"></i> Nuevo Equipo</h3>
        <button class="fl-modal-close" onclick="cerrarModal('modal-equipo')">&times;</button>
    </div>
    <div class="fl-modal-body">
        <div class="fl-section-title">Datos del equipo</div>
        <div class="fl-form-grid cols-5">
            <div class="fl-form-group" style="grid-column:span 2">
                <label>Equipo <span style="color:#dc2626">*</span></label>
                <input type="text" id="fl-equipo" placeholder="CAMIONETA, CAMION PLUMA…">
            </div>
            <div class="fl-form-group">
                <label>Marca</label>
                <input type="text" id="fl-marca" placeholder="Toyota">
            </div>
            <div class="fl-form-group">
                <label>Modelo</label>
                <input type="text" id="fl-modelo" placeholder="Hilux">
            </div>
            <div class="fl-form-group">
                <label>Patente</label>
                <input type="text" id="fl-patente" placeholder="ABCD12">
            </div>
        </div>
        <div class="fl-form-grid cols-2">
            <div class="fl-form-group">
                <label>Área</label>
                <input type="text" id="fl-area" placeholder="Área o faena">
            </div>
            <div class="fl-form-group">
                <label>Responsable</label>
                <input type="text" id="fl-responsable" placeholder="Nombre responsable">
            </div>
        </div>

        <div class="fl-section-title">Certificaciones — Fechas de vencimiento</div>
        <div class="fl-form-grid cols-4">
            <div class="fl-form-group">
                <label>GPS</label>
                <input type="date" id="fl-fecha_gps">
            </div>
            <div class="fl-form-group">
                <label>SKYNAV</label>
                <input type="date" id="fl-fecha_skynav">
            </div>
            <div class="fl-form-group">
                <label>Revisión Técnica</label>
                <input type="date" id="fl-fecha_revision_tecnica">
            </div>
            <div class="fl-form-group">
                <label>Permiso Circulación</label>
                <input type="date" id="fl-fecha_permiso_circulacion">
            </div>
            <div class="fl-form-group">
                <label>SOAP</label>
                <input type="date" id="fl-fecha_soap">
            </div>
            <div class="fl-form-group">
                <label>Cert. MLP</label>
                <input type="date" id="fl-fecha_cert_mlp">
            </div>
            <div class="fl-form-group">
                <label>Extintor</label>
                <input type="date" id="fl-fecha_extintor">
            </div>
            <div class="fl-form-group">
                <label>Prueba de Carga</label>
                <input type="date" id="fl-fecha_prueba_carga">
            </div>
            <div class="fl-form-group">
                <label>Insp. Camión Pluma</label>
                <input type="date" id="fl-fecha_insp_camion_pluma">
            </div>
            <div class="fl-form-group">
                <label>Insp. Gancho</label>
                <input type="date" id="fl-fecha_insp_gancho">
            </div>
            <div class="fl-form-group">
                <label>Insp. Perforadora</label>
                <input type="date" id="fl-fecha_insp_perforadora">
            </div>
            <div class="fl-form-group">
                <label>Gancho Perforadora</label>
                <input type="date" id="fl-fecha_gancho_perforadora">
            </div>
            <div class="fl-form-group">
                <label>Cable Acero Perf.</label>
                <input type="date" id="fl-fecha_cable_acero_perforadora">
            </div>
            <div class="fl-form-group">
                <label>Wuinche Perf.</label>
                <input type="date" id="fl-fecha_wuinche_perforadora">
            </div>
        </div>

        <div class="fl-section-title">Control de Kilometraje</div>
        <div class="fl-form-grid cols-3">
            <div class="fl-form-group">
                <label>Km Actual</label>
                <input type="number" id="fl-km_actual" min="0" placeholder="0">
            </div>
            <div class="fl-form-group">
                <label>Próxima Mantención (km)</label>
                <input type="number" id="fl-km_proxima_mantencion" min="0" placeholder="0">
            </div>
            <div class="fl-form-group">
                <label>Correo(s) aviso</label>
                <input type="text" id="fl-correo_aviso" placeholder="email@empresa.cl; otro@empresa.cl">
            </div>
        </div>
        <div class="fl-form-grid cols-2">
            <div class="fl-form-group" style="grid-column:span 2">
                <label>Observaciones</label>
                <textarea id="fl-observaciones" rows="2" placeholder="Observaciones adicionales…" style="height:60px"></textarea>
            </div>
        </div>
    </div>
    <div class="fl-modal-foot">
        <button class="btn-fl btn-fl-gray" onclick="cerrarModal('modal-equipo')">Cancelar</button>
        <button class="btn-fl btn-fl-navy" id="btn-guardar-equipo" onclick="guardarEquipo()">
            <i class="fa-solid fa-floppy-disk"></i> Guardar
        </button>
    </div>
</div>
</div>

{{-- ══ MODAL IMPORTAR ══ --}}
<div id="modal-importar" class="fl-modal-overlay">
<div class="fl-modal" style="max-width:520px">
    <div class="fl-modal-head">
        <h3><i class="fa-solid fa-file-excel" style="color:#16a34a"></i> Importar desde Excel</h3>
        <button class="fl-modal-close" onclick="cerrarModal('modal-importar')">&times;</button>
    </div>
    <div class="fl-modal-body">
        <p style="font-size:.8rem;color:#374151;margin-bottom:12px">
            Sube el archivo Excel con el mismo formato que usas habitualmente. Se procesarán las hojas de <b>Certificaciones</b> y <b>Kilometrajes</b>.
        </p>
        <p style="font-size:.75rem;color:#6b7280;margin-bottom:14px">
            Si ya existe un equipo con la misma patente, se actualizará. Si no, se creará uno nuevo.
        </p>
        <div class="fl-form-group">
            <label>Archivo Excel (.xlsx / .xls)</label>
            <input type="file" id="fl-archivo-excel" accept=".xlsx,.xls,.xlsm" style="height:auto;padding:6px">
        </div>
    </div>
    <div class="fl-modal-foot">
        <button class="btn-fl btn-fl-gray" onclick="cerrarModal('modal-importar')">Cancelar</button>
        <button class="btn-fl btn-fl-green" onclick="importarExcel()">
            <i class="fa-solid fa-upload"></i> Importar
        </button>
    </div>
</div>
</div>

</div>{{-- .fl-page --}}

@push('scripts')
<script>
(function () {
    const base   = () => window.APP_BASE || '';
    const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    let _editId  = null;

    // ── Toast ────────────────────────────────────────────────────────────────
    function notif(tipo, msg) {
        const box = document.getElementById('fl-toasts');
        const t   = document.createElement('div');
        t.className = 'fl-toast ' + (tipo === 'success' ? 'ok' : 'err');
        t.textContent = msg;
        box.appendChild(t);
        setTimeout(() => t.remove(), 4000);
    }

    // ── Tabs ─────────────────────────────────────────────────────────────────
    window.cambiarTab = function(panelId, btn) {
        document.querySelectorAll('.fl-tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.fl-tab').forEach(b => b.classList.remove('active'));
        document.getElementById(panelId).classList.add('active');
        btn.classList.add('active');
    };

    // ── Modal helpers ─────────────────────────────────────────────────────────
    window.abrirModal  = function(id) { document.getElementById(id).classList.add('visible'); };
    window.cerrarModal = function(id) { document.getElementById(id).classList.remove('visible'); };

    document.querySelectorAll('.fl-modal-overlay').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) m.classList.remove('visible'); });
    });

    // ── Nuevo equipo ──────────────────────────────────────────────────────────
    window.abrirModalNuevo = function() {
        _editId = null;
        document.getElementById('modal-equipo-title').innerHTML = '<i class="fa-solid fa-plus"></i> Nuevo Equipo';
        limpiarFormEquipo();
        abrirModal('modal-equipo');
    };

    function limpiarFormEquipo() {
        const ids = ['equipo','marca','modelo','patente','area','responsable','correo_aviso','observaciones',
            'km_actual','km_proxima_mantencion',
            'fecha_gps','fecha_skynav','fecha_revision_tecnica','fecha_permiso_circulacion',
            'fecha_soap','fecha_cert_mlp','fecha_extintor','fecha_prueba_carga',
            'fecha_insp_camion_pluma','fecha_insp_gancho','fecha_insp_perforadora',
            'fecha_gancho_perforadora','fecha_cable_acero_perforadora','fecha_wuinche_perforadora'];
        ids.forEach(id => { const el = document.getElementById('fl-' + id); if (el) el.value = ''; });
    }

    // ── Editar equipo ─────────────────────────────────────────────────────────
    window.editarEquipo = async function(id) {
        try {
            const res  = await fetch(base() + '/flota/' + id + '/datos');
            if (!res.ok) { notif('error', 'Error al cargar datos'); return; }
            const data = await res.json();
            _editId = id;
            document.getElementById('modal-equipo-title').innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Editar Equipo';

            const campos = ['equipo','marca','modelo','patente','area','responsable','correo_aviso','observaciones',
                'km_actual','km_proxima_mantencion',
                'fecha_gps','fecha_skynav','fecha_revision_tecnica','fecha_permiso_circulacion',
                'fecha_soap','fecha_cert_mlp','fecha_extintor','fecha_prueba_carga',
                'fecha_insp_camion_pluma','fecha_insp_gancho','fecha_insp_perforadora',
                'fecha_gancho_perforadora','fecha_cable_acero_perforadora','fecha_wuinche_perforadora'];
            campos.forEach(c => {
                const el = document.getElementById('fl-' + c);
                if (el) el.value = data[c] ?? '';
            });
            abrirModal('modal-equipo');
        } catch(e) { notif('error', 'Error de conexión'); }
    };

    // ── Guardar (nuevo o editar) ──────────────────────────────────────────────
    window.guardarEquipo = async function() {
        const equipo = document.getElementById('fl-equipo').value.trim();
        if (!equipo) { notif('error', 'El nombre del equipo es obligatorio'); return; }

        const fd = new FormData();
        fd.append('_token', CSRF);
        const campos = ['equipo','marca','modelo','patente','area','responsable','correo_aviso','observaciones',
            'km_actual','km_proxima_mantencion',
            'fecha_gps','fecha_skynav','fecha_revision_tecnica','fecha_permiso_circulacion',
            'fecha_soap','fecha_cert_mlp','fecha_extintor','fecha_prueba_carga',
            'fecha_insp_camion_pluma','fecha_insp_gancho','fecha_insp_perforadora',
            'fecha_gancho_perforadora','fecha_cable_acero_perforadora','fecha_wuinche_perforadora'];
        campos.forEach(c => {
            const el = document.getElementById('fl-' + c);
            if (el) fd.append(c, el.value);
        });

        const url    = _editId ? base() + '/flota/' + _editId : base() + '/flota';
        const btn    = document.getElementById('btn-guardar-equipo');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando…';

        try {
            const res = await fetch(url, { method: 'POST', body: fd });
            let data;
            try { data = await res.json(); } catch(_) {
                notif('error', 'Error del servidor (HTTP ' + res.status + ')'); return;
            }
            if (data.success || data.id) {
                notif('success', _editId ? 'Equipo actualizado' : 'Equipo creado');
                cerrarModal('modal-equipo');
                setTimeout(() => location.reload(), 700);
            } else {
                notif('error', data.error || data.message || 'HTTP ' + res.status);
            }
        } catch(e) { notif('error', 'Error de red: ' + e.message); }
        finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar';
        }
    };

    // ── Eliminar equipo ───────────────────────────────────────────────────────
    window.eliminarEquipo = function(id, nombre) {
        sgcConfirm('¿Eliminar el equipo "' + nombre + '"? Esta acción no se puede deshacer.', async () => {
            try {
                const fd = new FormData();
                fd.append('_token',  CSRF);
                fd.append('_method', 'DELETE');
                const res  = await fetch(base() + '/flota/' + id, { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    notif('success', 'Equipo eliminado');
                    setTimeout(() => location.reload(), 700);
                } else {
                    notif('error', data.error || 'Error al eliminar');
                }
            } catch(e) { notif('error', 'Error de conexión'); }
        }, { title: 'Eliminar equipo', icon: '<i class="fa-solid fa-trash"></i>', btnText: 'Eliminar', btnColor: '#dc2626' });
    };

    // ── Importar Excel ────────────────────────────────────────────────────────
    window.importarExcel = async function() {
        const input = document.getElementById('fl-archivo-excel');
        if (!input.files[0]) { notif('error', 'Selecciona un archivo primero'); return; }

        const fd = new FormData();
        fd.append('_token',        CSRF);
        fd.append('archivo_excel', input.files[0]);

        try {
            const res = await fetch(base() + '/flota/importar', { method: 'POST', body: fd });
            let data;
            try {
                data = await res.json();
            } catch(_) {
                notif('error', 'Error del servidor (HTTP ' + res.status + ')');
                return;
            }
            if (data.success) {
                notif('success', data.mensaje || 'Importación completada');
                cerrarModal('modal-importar');
                setTimeout(() => location.reload(), 1000);
            } else {
                notif('error', data.error || data.message || 'Error al importar (HTTP ' + res.status + ')');
            }
        } catch(e) { notif('error', 'Error de red: ' + e.message); }
    };
})();
</script>
@endpush
@endsection
