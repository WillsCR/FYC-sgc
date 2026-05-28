@extends('layouts.app')
@section('title', 'Control de Instrumentos')

@push('styles')
<style>
/* ── Layout principal ───────────────────────────────── */
.ci-body { padding: 20px; max-width: 1600px; margin: 0 auto; }

.ci-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 18px;
}
.ci-header h2 { font-size: 1.1rem; color: var(--navy); margin-bottom: 2px; }
.ci-header p  { font-size: .78rem; color: var(--text-muted); }

/* ── KPIs ───────────────────────────────────────────── */
.ci-kpis {
    display: grid; grid-template-columns: repeat(4, minmax(0,1fr));
    gap: 10px; margin-bottom: 16px;
}
.ci-kpi {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); padding: 12px 16px;
    border-top: 3px solid var(--navy);
}
.ci-kpi.verde   { border-top-color: #16A34A; }
.ci-kpi.amarillo{ border-top-color: #D97706; }
.ci-kpi.rojo    { border-top-color: #DC2626; }
.ci-kpi-val { font-size: 1.6rem; font-weight: 700; color: var(--navy); line-height: 1; margin-bottom: 4px; }
.ci-kpi-val.green  { color: #16A34A; }
.ci-kpi-val.amber  { color: #D97706; }
.ci-kpi-val.red    { color: #DC2626; }
.ci-kpi-lbl { font-size: .65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .06em; font-weight: 600; }

/* ── Filtros ────────────────────────────────────────── */
.ci-filtros {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); padding: 12px 16px;
    display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;
    margin-bottom: 14px;
}
.filtro-group { display: flex; flex-direction: column; gap: 4px; min-width: 130px; flex: 1; }
.filtro-group label { font-size: .72rem; font-weight: 600; color: var(--text-secondary); }
.filtro-group select,
.filtro-group input {
    padding: 7px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: .8rem; font-family: var(--font); outline: none;
    background: var(--body-bg); color: var(--text-primary);
}
.filtro-group select:focus,
.filtro-group input:focus { border-color: var(--blue-accent); }

/* ── Tabla ──────────────────────────────────────────── */
.ci-table-wrap {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); overflow: hidden;
    overflow-x: auto;
}
.ci-table { width: 100%; border-collapse: collapse; font-size: .76rem; white-space: nowrap; }
.ci-table thead th {
    background: var(--navy); color: #fff;
    padding: 7px 10px; text-align: center;
    font-size: .63rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    border-right: 1px solid rgba(255,255,255,.12);
    white-space: nowrap; vertical-align: middle;
}
.ci-table thead tr:last-child th {
    background: #1B4F72; color: rgba(255,255,255,.9);
    font-size: .61rem; font-weight: 600;
    letter-spacing: .04em; text-align: left;
    border-top: 1px solid rgba(255,255,255,.08);
}
.ci-table td {
    padding: 8px 10px; border-bottom: 1px solid var(--border);
    color: var(--text-secondary); vertical-align: middle;
    border-right: 1px solid #f0f4f9;
}
.ci-table tr:last-child td { border-bottom: none; }
.ci-table tr:hover td { background: var(--surface-2); }

/* Semáforo */
.ci-dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
.ci-dot-verde    { background: #16A34A; }
.ci-dot-amarillo { background: #D97706; }
.ci-dot-rojo     { background: #DC2626; }

/* Badges de certificado */
.cert-badge {
    display: inline-block; padding: 2px 7px; border-radius: 10px;
    font-size: .62rem; font-weight: 700; margin: 1px;
}
.cert-cal  { background: #DCFCE7; color: #15803D; }
.cert-calib { background: #FEF3C7; color: #B45309; }

/* Badge proyecto */
.proy-badge {
    display: inline-block; padding: 2px 8px; border-radius: 10px;
    font-size: .62rem; font-weight: 700; background: #EFF6FF; color: var(--navy);
    max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    vertical-align: middle;
}

/* Fecha coloreada */
.fecha-vencida   { color: #DC2626; font-weight: 600; }
.fecha-alerta    { color: #D97706; font-weight: 600; }
.fecha-vigente   { color: #15803D; }

/* ── Leyenda semáforo ───────────────────────────────── */
.ci-leyenda {
    display: flex; gap: 14px; align-items: center;
    font-size: .71rem; color: var(--text-muted); flex-wrap: wrap;
    margin-top: 10px;
}
.ci-leyenda-item { display: flex; align-items: center; gap: 5px; }

/* ── Modales ────────────────────────────────────────── */
.modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5);
    z-index: 600; align-items: center; justify-content: center; padding: 20px;
}
.modal-overlay.activo { display: flex; }
.modal-box {
    background: #fff; border-radius: var(--radius-lg);
    width: 100%; max-width: 700px; max-height: 90vh;
    overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.3);
    animation: fadeIn .18s ease;
}
.modal-box.modal-sm { max-width: 420px; }
.modal-box.modal-xl { max-width: 900px; }
.modal-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; border-bottom: 1px solid var(--border);
    background: var(--navy); border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    position: sticky; top: 0; z-index: 1;
}
.modal-head h3 { font-size: .95rem; font-weight: 700; color: #fff; margin: 0; }
.modal-close {
    background: none; border: none; color: rgba(255,255,255,.7);
    font-size: 1.3rem; cursor: pointer; line-height: 1; padding: 2px 6px;
    border-radius: 4px; transition: all .12s;
}
.modal-close:hover { background: rgba(255,255,255,.15); color: #fff; }
.modal-content-pad { padding: 20px; }

/* ── Formulario modal ───────────────────────────────── */
.form-section { margin-bottom: 18px; }
.form-section-title {
    font-size: .7rem; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .07em;
    padding-bottom: 6px; border-bottom: 1px solid var(--border);
    margin-bottom: 12px;
}
.form-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group label { font-size: .72rem; font-weight: 600; color: var(--text-secondary); }
.form-group input,
.form-group select,
.form-group textarea {
    padding: 7px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: .82rem; font-family: var(--font); outline: none;
    background: var(--body-bg); color: var(--text-primary);
    transition: border-color .15s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color: var(--blue-accent); }
.form-check { display: flex; align-items: center; gap: 8px; margin: 6px 0; cursor: pointer; }
.form-check input[type=checkbox] { width: 15px; height: 15px; cursor: pointer; accent-color: var(--navy); }
.form-check label { font-size: .82rem; color: var(--text-secondary); cursor: pointer; }

.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 20px; border-top: 1px solid var(--border); background: var(--body-bg);
    position: sticky; bottom: 0; border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}

/* ── Botones de acción (heredados del diseño del proyecto) ── */
.btn-accion {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 12px; border-radius: var(--radius-sm);
    font-size: .75rem; font-weight: 600; cursor: pointer;
    border: 1px solid; text-decoration: none; transition: all .12s;
    line-height: 1; background: transparent;
}
.btn-primary-ci { color: #fff; border-color: var(--navy); background: var(--navy); }
.btn-primary-ci:hover { background: var(--navy-mid); border-color: var(--navy-mid); }
.btn-secondary-ci { color: var(--text-secondary); border-color: var(--border); background: transparent; }
.btn-secondary-ci:hover { border-color: var(--navy); color: var(--navy); }
.btn-success-ci { color: #15803D; border-color: #15803D; background: transparent; }
.btn-success-ci:hover { background: #15803D; color: #fff; }
.btn-edit  { color: var(--navy); border-color: var(--navy); }
.btn-edit:hover  { background: var(--navy); color: #fff; }
.btn-files { color: #0369A1; border-color: #0369A1; }
.btn-files:hover { background: #0369A1; color: #fff; }
.btn-del   { color: #DC2626; border-color: #DC2626; }
.btn-del:hover   { background: #DC2626; color: #fff; }

/* ── Toast notificaciones ───────────────────────────── */
#ci-toasts { position: fixed; top: 68px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
.ci-toast {
    padding: 11px 16px; border-radius: var(--radius-md); font-size: .82rem;
    font-weight: 600; display: flex; align-items: center; gap: 8px;
    box-shadow: var(--shadow-md); animation: slideIn .2s ease; min-width: 260px;
}
.ci-toast-success { background: #DCFCE7; color: #15803D; border-left: 4px solid #15803D; }
.ci-toast-error   { background: #FEE2E2; color: #DC2626; border-left: 4px solid #DC2626; }
.ci-toast-info    { background: #EFF6FF; color: var(--navy); border-left: 4px solid var(--navy); }

/* ── Tabla permisos ─────────────────────────────────── */
.permisos-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.permisos-table th {
    background: var(--surface-2); padding: 8px 12px; text-align: left;
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    color: var(--text-secondary); border-bottom: 2px solid var(--border);
}
.permisos-table td {
    padding: 9px 12px; border-bottom: 1px solid var(--border);
    vertical-align: middle; color: var(--text-secondary);
}
.permisos-table td:not(:first-child) { text-align: center; }
.permisos-table input[type=checkbox] { width: 15px; height: 15px; accent-color: var(--navy); cursor: pointer; }

/* ── Archivos (gestionar) ───────────────────────────── */
.cert-section { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden; }
.cert-section-header {
    padding: 10px 14px; font-size: .75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    display: flex; align-items: center; gap: 6px;
}
.cert-section-header.calidad   { background: #DCFCE7; color: #15803D; }
.cert-section-header.calibracion { background: #FEF3C7; color: #B45309; }
.cert-section-body { padding: 12px 14px; }
.archivo-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm);
    margin-bottom: 6px; background: var(--body-bg);
    font-size: .78rem;
}
.archivo-nombre { font-weight: 600; color: var(--text-primary); }
.archivo-meta   { font-size: .7rem; color: var(--text-muted); }

/* ── Paginación (adaptar el paginador de Laravel) ─── */
.ci-paginacion { margin-top: 14px; }
.ci-paginacion nav { display: flex; justify-content: flex-end; }

@keyframes slideIn {
    from { transform: translateX(30px); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (max-width: 900px) {
    .ci-kpis { grid-template-columns: repeat(2, 1fr); }
    .form-row-3 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 600px) {
    .ci-kpis { grid-template-columns: 1fr 1fr; }
    .form-row-2, .form-row-3 { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div id="ci-toasts"></div>

<div class="ci-body">

    {{-- ── CABECERA ─────────────────────────────────────────── --}}
    <div class="ci-header">
        <div>
            <h2><i class="fa-solid fa-gauge"></i> Control de Instrumentos</h2>
            <p>Programa de Verificación y Calibración de Equipos</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
            @if($puedeImportar)
            <button class="btn-accion btn-success-ci" onclick="abrirModal('modal-importar')">
                <i class="fa-solid fa-file-excel"></i> Importar Excel
            </button>
            @endif
            @if($puedeGestionar)
                <button class="btn-accion btn-primary-ci" onclick="abrirModal('modal-nuevo')">
                    <i class="fa-solid fa-plus"></i> Nuevo Registro
                </button>
            @endif
        </div>
    </div>

    {{-- ── KPIs ─────────────────────────────────────────────── --}}
    <div class="ci-kpis">
        <div class="ci-kpi">
            <div class="ci-kpi-val">{{ $kpis['total'] }}</div>
            <div class="ci-kpi-lbl"><i class="fa-solid fa-gauge" style="margin-right:4px"></i>Total equipos</div>
        </div>
        <div class="ci-kpi verde">
            <div class="ci-kpi-val green">{{ $kpis['vigentes'] }}</div>
            <div class="ci-kpi-lbl"><span class="ci-dot ci-dot-verde" style="display:inline-block;margin-right:4px"></span>Vigentes</div>
        </div>
        <div class="ci-kpi amarillo">
            <div class="ci-kpi-val amber">{{ $kpis['por_vencer'] }}</div>
            <div class="ci-kpi-lbl"><span class="ci-dot ci-dot-amarillo" style="display:inline-block;margin-right:4px"></span>Por vencer (30 días)</div>
        </div>
        <div class="ci-kpi rojo">
            <div class="ci-kpi-val red">{{ $kpis['vencidos'] }}</div>
            <div class="ci-kpi-lbl"><span class="ci-dot ci-dot-rojo" style="display:inline-block;margin-right:4px"></span>Vencidos / Sin fecha</div>
        </div>
    </div>

    {{-- ── FILTROS ──────────────────────────────────────────── --}}
    <div class="ci-filtros">
        <div class="filtro-group" style="flex:2;min-width:200px">
            <label>Buscar</label>
            <input type="text" id="inp-search" placeholder="Descripción, marca, modelo, N° cert..."
                   value="{{ $search }}" onkeydown="if(event.key==='Enter') aplicarFiltros()">
        </div>
        <div class="filtro-group">
            <label>Proyecto</label>
            <select id="sel-proyecto">
                <option value="">Todos los proyectos</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}" {{ $proyecto == $area->id ? 'selected' : '' }}>
                        {{ $area->descripcion }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="filtro-group">
            <label>Estado calibración</label>
            <select id="sel-estado">
                <option value="">Todos</option>
                <option value="vigente"    {{ $estado === 'vigente'    ? 'selected' : '' }}>Vigente</option>
                <option value="por_vencer" {{ $estado === 'por_vencer' ? 'selected' : '' }}>Por vencer</option>
                <option value="vencido"    {{ $estado === 'vencido'    ? 'selected' : '' }}>Vencido</option>
            </select>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end">
            <button class="btn-accion btn-primary-ci" onclick="aplicarFiltros()">
                <i class="fa-solid fa-search"></i> Filtrar
            </button>
            <a href="{{ route('control-instrumentos.index') }}" class="btn-accion btn-secondary-ci">
                <i class="fa-solid fa-xmark"></i> Limpiar
            </a>
        </div>
    </div>

    {{-- ── TABLA ────────────────────────────────────────────── --}}
    <div class="ci-table-wrap">
        <table class="ci-table">
            <thead>
                <tr>
                    <th colspan="8">CARACTERÍSTICAS DEL EQUIPO</th>
                    <th colspan="2">FRECUENCIA</th>
                    <th colspan="4">CALIBRACIÓN DEL EQUIPO</th>
                    <th rowspan="2" style="min-width:120px;vertical-align:middle">RESPONSABLE</th>
                    <th rowspan="2" style="min-width:90px;vertical-align:middle">ACCIONES</th>
                </tr>
                <tr>
                    <th style="width:34px">N°</th>
                    <th style="min-width:100px">PROYECTO</th>
                    <th style="min-width:160px">TIPO DE CERTIFICADO</th>
                    <th style="min-width:180px">DESCRIPCIÓN</th>
                    <th style="min-width:65px">MARCA</th>
                    <th style="min-width:65px">MODELO</th>
                    <th style="min-width:80px">N° SERIE</th>
                    <th style="min-width:58px">N° INT.</th>
                    <th style="min-width:68px">CALIBRACIÓN</th>
                    <th style="min-width:65px">VERIFICACIÓN</th>
                    <th style="min-width:110px">N° CERT CALIB.</th>
                    <th style="min-width:82px">ÚLTIMA</th>
                    <th style="min-width:82px">PRÓXIMA</th>
                    <th style="width:46px;text-align:center">SEM.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programas as $p)
                    @php
                        $color = \App\Helpers\ControlInstrumentosHelper::obtenerColorSemaforo($p->proxima);
                        $dotClass = match($color) {
                            '#28a745' => 'ci-dot-verde',
                            '#ffc107' => 'ci-dot-amarillo',
                            default   => 'ci-dot-rojo',
                        };
                        $fechaClass = match($color) {
                            '#28a745' => 'fecha-vigente',
                            '#ffc107' => 'fecha-alerta',
                            default   => 'fecha-vencida',
                        };
                    @endphp
                    <tr>
                        <td style="text-align:center;color:var(--text-muted)">{{ $programas->firstItem() + $loop->index }}</td>
                        <td>
                            <span class="proy-badge" title="{{ $p->area->descripcion ?? '' }}">
                                {{ $p->area->descripcion ?? '—' }}
                            </span>
                        </td>
                        {{-- TIPO DE CERTIFICADO: texto + links descarga inline --}}
                        <td style="white-space:normal;line-height:1.5">
                            @php
                                $partes = [];
                                if ($p->cert_calidad)    $partes[] = 'Certificado de calidad';
                                if ($p->cert_calibracion) $partes[] = 'Certificado de calibración';
                            @endphp
                            <span style="font-size:.72rem;color:var(--text-secondary)">
                                {{ implode(' / ', $partes) ?: '—' }}
                            </span>
                            @foreach($p->archivosCalidad as $arch)
                                <br><a href="{{ route('archivos-equipos.descargar', $arch->id) }}"
                                   style="font-size:.7rem;color:#0369A1;text-decoration:none"
                                   target="_blank" title="{{ $arch->archivo_nombre }}">
                                    <i class="fa-solid fa-download" style="font-size:.62rem"></i> Descargar calidad
                                </a>
                            @endforeach
                            @foreach($p->archivosCalibra as $arch)
                                <br><a href="{{ route('archivos-equipos.descargar', $arch->id) }}"
                                   style="font-size:.7rem;color:#B45309;text-decoration:none"
                                   target="_blank" title="{{ $arch->archivo_nombre }}">
                                    <i class="fa-solid fa-download" style="font-size:.62rem"></i> Descargar calibración
                                </a>
                            @endforeach
                        </td>
                        <td style="white-space:normal;min-width:180px">
                            <strong style="color:var(--text-primary)">{{ $p->descripcion }}</strong>
                        </td>
                        <td>{{ $p->marca ?? '—' }}</td>
                        <td>{{ $p->modelo ?? '—' }}</td>
                        <td>{{ $p->serie ?? '—' }}</td>
                        <td style="text-align:center">{{ $p->interno ?? '—' }}</td>
                        <td>{{ \App\Helpers\ControlInstrumentosHelper::obtenerFreqCalib($p->calibracion) }}</td>
                        <td>{{ \App\Helpers\ControlInstrumentosHelper::obtenerFreqVerif($p->verificacion) }}</td>
                        <td>{{ $p->certificado_calibracion ?? '—' }}</td>
                        <td>{{ $p->ultima ? $p->ultima->format('d-m-Y') : '—' }}</td>
                        <td class="{{ $fechaClass }}">
                            {{ $p->proxima ? $p->proxima->format('d-m-Y') : '—' }}
                        </td>
                        <td style="text-align:center">
                            <span class="ci-dot {{ $dotClass }}"
                                  title="{{ \App\Helpers\ControlInstrumentosHelper::obtenerEstadoVigencia($p->proxima) }}"></span>
                        </td>
                        {{-- RESPONSABLE --}}
                        <td style="white-space:normal;font-size:.74rem;color:var(--text-secondary)">
                            {{ $p->responsable ?? '—' }}
                        </td>
                        <td style="text-align:center">
                            <div style="display:flex;gap:4px;justify-content:center">
                                @if($puedeGestionar)
                                    <button class="btn-accion btn-edit" style="padding:3px 7px"
                                            onclick="editarPrograma({{ $p->id }})" title="Editar">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                @endif
                                <button class="btn-accion btn-files" style="padding:3px 7px"
                                        onclick="verArchivos({{ $p->id }})" title="Gestionar archivos">
                                    <i class="fa-solid fa-paperclip"></i>
                                </button>
                                @if($puedeGestionar)
                                    <button class="btn-accion btn-del" style="padding:3px 7px"
                                            onclick="confirmarEliminar({{ $p->id }}, '{{ addslashes($p->descripcion) }}')" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" style="text-align:center;padding:40px;color:var(--text-muted)">
                            <i class="fa-solid fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px"></i>
                            No hay registros.
                            @if($puedeGestionar)
                                @if($puedeImportar)<a href="#" onclick="abrirModal('modal-importar')" style="color:var(--blue-accent)">Importar Excel</a> o @endif
                                <a href="#" onclick="abrirModal('modal-nuevo')" style="color:var(--blue-accent)">agregar manualmente</a>.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Leyenda + paginación --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-top:12px">
        <div class="ci-leyenda">
            <div class="ci-leyenda-item"><span class="ci-dot ci-dot-verde"></span> Vigente (&gt;30 días)</div>
            <div class="ci-leyenda-item"><span class="ci-dot ci-dot-amarillo"></span> Por vencer (≤30 días)</div>
            <div class="ci-leyenda-item"><span class="ci-dot ci-dot-rojo"></span> Vencido / Sin fecha</div>
        </div>
        <div class="ci-paginacion">{{ $programas->links() }}</div>
    </div>

</div>{{-- /ci-body --}}

{{-- ═══════════════ MODALES ═══════════════ --}}

{{-- ── Modal: Confirmación ─────────────────────────────── --}}
<div id="modal-confirm" class="modal-overlay" onclick="if(event.target===this)cerrarModal('modal-confirm')">
    <div class="modal-box modal-sm">
        <div class="modal-head">
            <h3>¿Está seguro?</h3>
            <button class="modal-close" onclick="cerrarModal('modal-confirm')">&times;</button>
        </div>
        <div class="modal-content-pad">
            <p id="confirm-msg" style="color:var(--text-secondary);margin-bottom:20px;font-size:.88rem"></p>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button class="btn-accion btn-secondary-ci" onclick="cerrarModal('modal-confirm')">Cancelar</button>
                <button id="confirm-ok" class="btn-accion btn-del">Eliminar</button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal: Nuevo Registro ────────────────────────────── --}}
<div id="modal-nuevo" class="modal-overlay" onclick="if(event.target===this)cerrarModal('modal-nuevo')">
    <div class="modal-box modal-xl">
        <div class="modal-head">
            <h3><i class="fa-solid fa-plus"></i> Nuevo Registro</h3>
            <button class="modal-close" onclick="cerrarModal('modal-nuevo')">&times;</button>
        </div>
        <form id="form-nuevo" novalidate>
            @csrf
            <div class="modal-content-pad">

                <div class="form-section">
                    <div class="form-section-title"><i class="fa-solid fa-cog"></i> Características del equipo</div>
                    <div class="form-row" style="grid-template-columns:2fr 1fr 1fr 1fr 1fr">
                        <div class="form-group">
                            <label>Descripción *</label>
                            <input type="text" name="descripcion" required placeholder="Ej: Amperímetro de tenazas">
                        </div>
                        <div class="form-group">
                            <label>Marca</label>
                            <input type="text" name="marca" placeholder="Ej: Fluke">
                        </div>
                        <div class="form-group">
                            <label>Modelo</label>
                            <input type="text" name="modelo">
                        </div>
                        <div class="form-group">
                            <label>Serie</label>
                            <input type="text" name="serie">
                        </div>
                        <div class="form-group">
                            <label>N° Interno</label>
                            <input type="text" name="interno">
                        </div>
                    </div>
                    <div class="form-row-2" style="margin-top:12px">
                        <div class="form-group">
                            <label>Proyecto / Contrato *</label>
                            <select name="id_contrato" required>
                                <option value="">Seleccione...</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tipo de certificado</label>
                            <div style="display:flex;gap:20px;padding-top:4px">
                                <label class="form-check">
                                    <input type="checkbox" name="cert_calidad" value="1" id="n_cert_cal"
                                           onchange="toggleArchivoNuevo('calidad', this.checked)">
                                    <label for="n_cert_cal">Certificado de Calidad</label>
                                </label>
                                <label class="form-check">
                                    <input type="checkbox" name="cert_calibracion" value="1" id="n_cert_calib"
                                           onchange="toggleArchivoNuevo('calibracion', this.checked)">
                                    <label for="n_cert_calib">Certificado de Calibración</label>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title"><i class="fa-solid fa-calendar"></i> Frecuencia</div>
                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Calibración *</label>
                            <select name="calibracion" required>
                                <option value="">Seleccione...</option>
                                <option value="1">Anual</option>
                                <option value="2">Mensual</option>
                                <option value="3">No aplica</option>
                                <option value="4">Semestral</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Verificación</label>
                            <select name="verificacion">
                                <option value="">Seleccione...</option>
                                <option value="1">Mensual</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title"><i class="fa-solid fa-flask"></i> Datos de calibración</div>
                    <div class="form-row-3">
                        <div class="form-group">
                            <label>N° Certificado</label>
                            <input type="text" name="num_cert_calibracion" placeholder="Ej: CCE 288-2025">
                        </div>
                        <div class="form-group">
                            <label>Última calibración</label>
                            <input type="date" name="fecha_ultima">
                        </div>
                        <div class="form-group">
                            <label>Próxima calibración</label>
                            <input type="date" name="fecha_proxima">
                        </div>
                    </div>
                </div>

                <div id="sect-archivos-nuevo" style="display:none">
                    <div class="form-section">
                        <div class="form-section-title"><i class="fa-solid fa-file-pdf"></i> Archivos</div>
                        <div class="form-row-2">
                            <div id="bloque-cal-nuevo" class="form-group" style="display:none">
                                <label>Certificado de Calidad (PDF/IMG)</label>
                                <input type="file" name="archivo_calidad" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div id="bloque-calib-nuevo" class="form-group" style="display:none">
                                <label>Certificado de Calibración (PDF/IMG)</label>
                                <input type="file" name="archivo_calibracion" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label>Responsable</label>
                        <input type="text" name="responsable" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observaciones" rows="2" placeholder="Notas adicionales..."></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-accion btn-secondary-ci" onclick="cerrarModal('modal-nuevo')">Cancelar</button>
                <button type="submit" class="btn-accion btn-primary-ci" id="btn-guardar-nuevo">
                    <i class="fa-solid fa-save"></i> Guardar Registro
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Editar (contenido dinámico) ─────────────────── --}}
<div id="modal-editar" class="modal-overlay" onclick="if(event.target===this)cerrarModal('modal-editar')">
    <div class="modal-box modal-xl">
        <div class="modal-head">
            <h3><i class="fa-solid fa-pencil"></i> Editar Registro</h3>
            <button class="modal-close" onclick="cerrarModal('modal-editar')">&times;</button>
        </div>
        <div id="modal-editar-body" style="min-height:200px">
            <div style="display:flex;align-items:center;justify-content:center;padding:40px;color:var(--text-muted)">
                <i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Cargando...
            </div>
        </div>
    </div>
</div>

{{-- ── Modal: Archivos (contenido dinámico) ───────────────── --}}
<div id="modal-archivos" class="modal-overlay" onclick="if(event.target===this)cerrarModal('modal-archivos')">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fa-solid fa-paperclip"></i> Archivos del Registro</h3>
            <button class="modal-close" onclick="cerrarModal('modal-archivos')">&times;</button>
        </div>
        <div id="modal-archivos-body" style="min-height:120px">
            <div style="display:flex;align-items:center;justify-content:center;padding:40px;color:var(--text-muted)">
                <i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Cargando...
            </div>
        </div>
    </div>
</div>

{{-- ── Modal: Importar Excel ───────────────────────────────── --}}
<div id="modal-importar" class="modal-overlay" onclick="if(event.target===this)cerrarModal('modal-importar')">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fa-solid fa-file-excel"></i> Importar desde Excel</h3>
            <button class="modal-close" onclick="cerrarModal('modal-importar')">&times;</button>
        </div>
        <form id="form-importar" novalidate>
            @csrf
            <div class="modal-content-pad">
                <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-md);padding:12px 16px;margin-bottom:16px;font-size:.8rem;color:var(--text-secondary)">
                    <strong style="color:var(--navy)"><i class="fa-solid fa-circle-info"></i> Formato esperado</strong><br>
                    Sube el archivo completo <strong>Programa_verificación_control_Rev_XX.xlsx</strong> —
                    el sistema leerá automáticamente la hoja <strong>"Seguimiento"</strong>.<br>
                    Si el archivo no tiene esa hoja, se usará la hoja activa.
                    Los datos se esperan a partir de la fila 11, columnas C–S.
                </div>

                <div class="form-group" style="margin-bottom:14px">
                    <label>Archivo Excel (.xlsx, .xls) *</label>
                    <input type="file" name="archivo_excel" accept=".xlsx,.xls" required>
                </div>

                <label class="form-check">
                    <input type="checkbox" name="borrar_existentes" value="1" id="chk-borrar">
                    <label for="chk-borrar" style="color:#DC2626;font-weight:600">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Eliminar todos los registros existentes antes de importar
                    </label>
                </label>

                <div id="import-resultado" style="display:none;margin-top:14px;padding:12px;border-radius:var(--radius-md);font-size:.82rem"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-accion btn-secondary-ci" onclick="cerrarModal('modal-importar')">Cancelar</button>
                <button type="submit" class="btn-accion btn-success-ci" id="btn-importar">
                    <i class="fa-solid fa-upload"></i> Importar datos
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal: Permisos ─────────────────────────────────────── --}}
@if($usuario && $usuario->esAdmin())
<div id="modal-permisos" class="modal-overlay" onclick="if(event.target===this)cerrarModal('modal-permisos')">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fa-solid fa-lock"></i> Permisos de Acceso</h3>
            <button class="modal-close" onclick="cerrarModal('modal-permisos')">&times;</button>
        </div>
        <div class="modal-content-pad">
            <table class="permisos-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Ver</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $u)
                        <tr>
                            <td>
                                <strong>{{ $u->nombre }}</strong><br>
                                <span style="font-size:.7rem;color:var(--text-muted)">{{ $u->email }}</span>
                            </td>
                            <td>
                                <input type="checkbox" data-uid="{{ $u->id }}" data-tipo="ver"
                                       class="perm-check" {{ $u->ver_control_instrumentos ? 'checked' : '' }}>
                            </td>
                            <td>
                                <input type="checkbox" data-uid="{{ $u->id }}" data-tipo="editar"
                                       class="perm-check" {{ $u->editar_control_instrumentos ? 'checked' : '' }}>
                            </td>
                            <td>
                                <input type="checkbox" data-uid="{{ $u->id }}" data-tipo="eliminar"
                                       class="perm-check" {{ $u->eliminar_control_instrumentos ? 'checked' : '' }}>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button class="btn-accion btn-secondary-ci" onclick="cerrarModal('modal-permisos')">Cerrar</button>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

// ── Helpers de modal ──────────────────────────────────────
function abrirModal(id) {
    document.getElementById(id).classList.add('activo');
    document.body.style.overflow = 'hidden';
}
function cerrarModal(id) {
    document.getElementById(id).classList.remove('activo');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.activo').forEach(m => m.classList.remove('activo'));
        document.body.style.overflow = '';
    }
});

// ── Notificaciones toast ──────────────────────────────────
function notif(tipo, msg) {
    const c = document.getElementById('ci-toasts');
    const d = document.createElement('div');
    const icons = { success: 'circle-check', error: 'circle-xmark', info: 'circle-info' };
    d.className = `ci-toast ci-toast-${tipo}`;
    d.innerHTML = `<i class="fa-solid fa-${icons[tipo]||'circle-info'}"></i> ${msg}`;
    c.appendChild(d);
    setTimeout(() => d.remove(), 3500);
}

// ── Filtros ───────────────────────────────────────────────
function aplicarFiltros() {
    const p = new URLSearchParams();
    const s = document.getElementById('inp-search').value.trim();
    const pr = document.getElementById('sel-proyecto').value;
    const est = document.getElementById('sel-estado').value;
    if (s)   p.set('search',   s);
    if (pr)  p.set('proyecto', pr);
    if (est) p.set('estado',   est);
    window.location.href = '/control-instrumentos' + (p.toString() ? '?' + p.toString() : '');
}

// ── Nuevo: toggle archivos ────────────────────────────────
function toggleArchivoNuevo(tipo, checked) {
    const calActive   = document.getElementById('n_cert_cal').checked;
    const calibActive = document.getElementById('n_cert_calib').checked;
    document.getElementById('sect-archivos-nuevo').style.display =
        (calActive || calibActive) ? 'block' : 'none';
    document.getElementById('bloque-cal-nuevo').style.display   = calActive  ? 'block' : 'none';
    document.getElementById('bloque-calib-nuevo').style.display = calibActive ? 'block' : 'none';
}

// ── Formulario Nuevo ──────────────────────────────────────
document.getElementById('form-nuevo').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-guardar-nuevo');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

    const fd = new FormData(this);
    try {
        const res  = await fetch('/control-instrumentos', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF } });
        const data = await res.json();
        if (data.success) {
            cerrarModal('modal-nuevo');
            notif('success', data.message);
            setTimeout(() => location.reload(), 900);
        } else {
            const err = data.errors ? Object.values(data.errors).flat().join(' | ') : data.message;
            notif('error', err || 'Error al guardar');
        }
    } catch { notif('error', 'Error de conexión'); }

    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-save"></i> Guardar Registro';
});

// ── Editar (carga dinámica) ───────────────────────────────
async function editarPrograma(id) {
    abrirModal('modal-editar');
    const body = document.getElementById('modal-editar-body');
    body.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;padding:40px;color:var(--text-muted)"><i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Cargando...</div>';
    try {
        const res = await fetch(`/control-instrumentos/${id}/edit`, { headers: { 'X-CSRF-TOKEN': CSRF } });
        const html = await res.text();
        body.innerHTML = html;
        ejecutarScripts(body);
    } catch { notif('error', 'No se pudo cargar el formulario'); }
}

// ── Archivos (carga dinámica) ─────────────────────────────
async function verArchivos(id) {
    abrirModal('modal-archivos');
    const body = document.getElementById('modal-archivos-body');
    body.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;padding:40px;color:var(--text-muted)"><i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Cargando...</div>';
    try {
        const res = await fetch(`/control-instrumentos/${id}/archivos`, { headers: { 'X-CSRF-TOKEN': CSRF } });
        const html = await res.text();
        body.innerHTML = html;
        ejecutarScripts(body);
    } catch { notif('error', 'No se pudo cargar los archivos'); }
}

// ── Confirm eliminar ──────────────────────────────────────
let _eliminarId = null;
function confirmarEliminar(id, desc) {
    _eliminarId = id;
    document.getElementById('confirm-msg').textContent =
        `¿Desea eliminar "${desc}"? Esta acción no se puede deshacer.`;
    abrirModal('modal-confirm');
}
document.getElementById('confirm-ok').addEventListener('click', async () => {
    if (!_eliminarId) return;
    cerrarModal('modal-confirm');
    try {
        const fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('_method', 'DELETE');
        const res  = await fetch(`/control-instrumentos/${_eliminarId}`, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            notif('success', data.message);
            document.getElementById(`programa-row-${_eliminarId}`)?.remove();
            setTimeout(() => location.reload(), 1200);
        } else {
            notif('error', data.message || 'No se pudo eliminar');
        }
    } catch { notif('error', 'Error de conexión'); }
    _eliminarId = null;
});

// ── Submit del form EDITADO (event delegation) ────────────
document.addEventListener('submit', async function(e) {
    if (e.target.id === 'form-editar-prog') {
        e.preventDefault();
        const id  = e.target.dataset.id;
        const btn = e.target.querySelector('[type=submit]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
        const fd = new FormData(e.target);
        try {
            const res  = await fetch(`/control-instrumentos/${id}`, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF } });
            const data = await res.json();
            if (data.success) {
                cerrarModal('modal-editar');
                notif('success', data.message);
                setTimeout(() => location.reload(), 900);
            } else {
                const err = data.errors ? Object.values(data.errors).flat().join(' | ') : data.message;
                notif('error', err || 'Error al guardar');
            }
        } catch { notif('error', 'Error de conexión'); }
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-save"></i> Guardar Cambios';
    }
});

// ── Importar Excel ────────────────────────────────────────
document.getElementById('form-importar').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-importar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importando...';
    const result = document.getElementById('import-resultado');
    result.style.display = 'none';

    const fd = new FormData(this);
    try {
        const res  = await fetch('/control-instrumentos/importar', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF } });
        const data = await res.json();
        if (data.success) {
            result.style.display = 'block';
            result.style.background = '#DCFCE7';
            result.style.color = '#15803D';
            result.innerHTML = `<i class="fa-solid fa-circle-check"></i> <strong>${data.mensaje}</strong>`;
            setTimeout(() => location.reload(), 2000);
        } else {
            result.style.display = 'block';
            result.style.background = '#FEE2E2';
            result.style.color = '#DC2626';
            result.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> ${data.message}`;
        }
    } catch { notif('error', 'Error de conexión'); }

    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-upload"></i> Importar datos';
});

// ── Permisos ──────────────────────────────────────────────
document.querySelectorAll('.perm-check').forEach(chk => {
    chk.addEventListener('change', async function() {
        const fd = new FormData();
        fd.append('usuario_id', this.dataset.uid);
        fd.append('tipo', this.dataset.tipo);
        fd.append('estado', this.checked ? 1 : 0);
        try {
            const res = await fetch('/control-instrumentos/actualizar-permisos', {
                method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': CSRF }
            });
            const data = await res.json();
            if (data.success) notif('success', 'Permisos actualizados');
            else notif('error', 'Error al actualizar permisos');
        } catch { notif('error', 'Error de conexión'); }
    });
});

// ── Ejecutar scripts en HTML dinámico ────────────────────
function ejecutarScripts(container) {
    container.querySelectorAll('script').forEach(old => {
        const n = document.createElement('script');
        [...old.attributes].forEach(a => n.setAttribute(a.name, a.value));
        n.textContent = old.textContent;
        old.parentNode.replaceChild(n, old);
    });
}
</script>
@endpush
