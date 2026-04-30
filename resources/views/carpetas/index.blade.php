@extends('layouts.app')

@section('title', 'Gestión Documental')

@push('styles')
<style>
.doc-layout { display: flex; flex-direction: column; min-height: calc(100vh - 90px); }
.content-panel { display: flex; flex-direction: column; flex: 1; background: var(--body-bg); }
.content-header { background: var(--surface); border-bottom: 1px solid var(--border); padding: 16px 24px; }
.breadcrumb { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
.breadcrumb-item { font-size: .78rem; color: var(--text-muted); text-decoration: none; }
.breadcrumb-item:hover { color: var(--navy); }
.breadcrumb-item.activo { color: var(--navy); font-weight: 600; }
.breadcrumb-sep { color: var(--text-muted); font-size: .78rem; }
.content-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.content-title { font-size: 1.1rem; font-weight: 700; color: var(--navy); flex: 1; }
.btn-upload {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; background: var(--navy); color: #fff;
    border: none; border-radius: 4px; font-size: .82rem;
    font-weight: 600; cursor: pointer; transition: background .15s;
    text-decoration: none;
}
.btn-upload:hover { background: #0a2147; }
.btn-new-folder {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; background: var(--surface); color: var(--navy);
    border: 1px solid var(--border); border-radius: 4px; font-size: .82rem;
    font-weight: 600; cursor: pointer; transition: all .15s;
}
.btn-new-folder:hover { background: var(--surface-2); border-color: var(--navy); }
.content-scroll { flex: 1; padding: 20px 24px; }
.section-label {
    font-size: .75rem; font-weight: 700; color: var(--navy);
    text-transform: uppercase; letter-spacing: .05em;
    margin-bottom: 12px; margin-top: 20px;
}
.section-label:first-child { margin-top: 0; }

/* Subcarpetas */
.subcarpetas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px; margin-bottom: 24px;
}
.subcarpeta-wrap { position: relative; }
.subcarpeta-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 6px; padding: 12px 12px 10px;
    display: flex; flex-direction: column; align-items: center;
    gap: 8px; cursor: pointer; text-decoration: none;
    color: var(--text-primary); font-size: .8rem; font-weight: 500;
    transition: all .15s; text-align: center; width: 100%; box-sizing: border-box;
}
.subcarpeta-card:hover {
    border-color: var(--blue-accent); background: var(--surface-2);
    transform: translateY(-2px); box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.subcarpeta-del {
    position: absolute; top: 6px; right: 6px;
    background: #FEF2F2; border: 1px solid #FECACA; color: #DC2626;
    border-radius: 4px; width: 22px; height: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; cursor: pointer; opacity: 0;
    transition: opacity .15s; z-index: 2; line-height: 1;
}
.subcarpeta-wrap:hover .subcarpeta-del { opacity: 1; }
.subcarpeta-del:hover { background: #DC2626; color: #fff; }

/* Tabla documentos */
.archivos-table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; overflow: hidden; }
.archivos-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.archivos-table th {
    background: var(--navy); color: #fff; padding: 11px 14px;
    text-align: left; font-size: .7rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .04em;
}
.archivos-table td { padding: 11px 14px; border-bottom: 1px solid var(--border); color: var(--text-secondary); vertical-align: middle; }
.archivos-table tr:last-child td { border-bottom: none; }
.archivos-table tr:hover td { background: var(--surface-2); }
.archivo-nombre { font-weight: 500; color: var(--text-primary); display: block; }
.archivo-ext { display: inline-block; font-size: .65rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; }
.ext-pdf   { background: #FCEBEB; color: #991B1B; }
.ext-doc   { background: #EFF6FF; color: #1D4ED8; }
.ext-xls   { background: #F0FDF4; color: #15803D; }
.ext-img   { background: #FDF4FF; color: #7C3AED; }
.ext-other { background: var(--surface-2); color: var(--text-muted); }

/* Botones acción */
.btn-accion { display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 4px; font-size: .75rem; font-weight: 600; cursor: pointer; border: 1px solid; text-decoration: none; transition: all .12s; }
.btn-ver { color: var(--blue-accent); border-color: var(--blue-accent); background: transparent; }
.btn-dl  { color: var(--navy);        border-color: var(--navy);        background: transparent; }
.btn-del { color: var(--danger);      border-color: var(--danger);      background: transparent; }
.btn-ver:hover { background: var(--blue-accent); color: #fff; }
.btn-dl:hover  { background: var(--navy);        color: #fff; }
.btn-del:hover { background: var(--danger);      color: #fff; }

/* Alertas */
.alert-ok  { background: #DCFCE7; border-left: 4px solid #16A34A; color: #166534; padding: 11px 14px; border-radius: 4px; font-size: .82rem; margin-bottom: 16px; }
.alert-err { background: #FCEBEB; border-left: 4px solid #DC2626; color: #991B1B; padding: 11px 14px; border-radius: 4px; font-size: .82rem; margin-bottom: 16px; }

/* Estado vacío */
.empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
.empty-icon  { font-size: 2.8rem; margin-bottom: 12px; }
.empty-text  { font-size: .85rem; }

/* Modales */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200; align-items: center; justify-content: center; }
.modal-overlay.visible { display: flex; }
.modal { background: var(--surface); border-radius: 8px; padding: 28px; width: 100%; max-width: 500px; box-shadow: 0 24px 64px rgba(0,0,0,.2); }
.modal-title { font-size: 1rem; font-weight: 700; color: var(--navy); margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
.drop-zone { border: 2px dashed var(--border); border-radius: 6px; padding: 32px; text-align: center; cursor: pointer; transition: all .15s; margin-bottom: 16px; }
.drop-zone:hover, .drop-zone.drag-over { border-color: var(--blue-accent); background: var(--surface-2); }
.drop-zone input[type=file] { display: none; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; }
.btn-cancel { padding: 9px 18px; border-radius: 4px; border: 1px solid var(--border); background: transparent; font-size: .82rem; cursor: pointer; color: var(--text-secondary); font-weight: 500; }
.btn-cancel:hover { background: var(--surface-2); }

/* Visor */
.viewer-modal { position: fixed; inset: 0; background: rgba(0,0,0,.88); z-index: 300; display: none; align-items: center; justify-content: center; }
.viewer-modal.visible { display: flex; }
.viewer-container { position: relative; width: 96vw; height: 96vh; display: flex; flex-direction: column; border-radius: 8px; overflow: hidden; }
.viewer-header { background: #1a1a2e; color: #fff; padding: 10px 16px; display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.viewer-titulo { flex: 1; font-size: .85rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.viewer-close { background: rgba(255,255,255,.12); border: none; color: #fff; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: background .15s; flex-shrink: 0; }
.viewer-close:hover { background: rgba(255,255,255,.25); }
.viewer-body { flex: 1; position: relative; background: #111; }
.viewer-loading { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #aaa; font-size: .85rem; gap: 12px; }
.viewer-spinner { width: 36px; height: 36px; border: 3px solid rgba(255,255,255,.15); border-top-color: #fff; border-radius: 50%; animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.viewer-body iframe { width: 100%; height: 100%; border: none; display: block; }
.viewer-body img { width: 100%; height: 100%; object-fit: contain; display: block; }

@media (max-width: 768px) {
    .subcarpetas-grid { grid-template-columns: repeat(2, 1fr); }
    .content-header, .content-scroll { padding: 12px 16px; }
    .archivos-table th:nth-child(3), .archivos-table td:nth-child(3) { display: none; }
}

/* ── Toasts ─────────────────────────────────────────────── */
#toast-container {
    position: fixed; top: 20px; right: 20px; z-index: 9999;
    display: flex; flex-direction: column; gap: 10px; pointer-events: none;
}
.toast {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 14px 18px; border-radius: 8px;
    background: var(--surface); box-shadow: 0 8px 32px rgba(0,0,0,.18);
    border-left: 4px solid; min-width: 280px; max-width: 380px;
    pointer-events: all; animation: toastIn .25s ease;
    font-size: .84rem;
}
.toast.saliendo { animation: toastOut .25s ease forwards; }
.toast-ok    { border-color: #16A34A; }
.toast-error { border-color: #DC2626; }
.toast-info  { border-color: var(--navy); }
.toast-icono { font-size: 1.1rem; line-height: 1.4; flex-shrink: 0; }
.toast-cuerpo { flex: 1; }
.toast-titulo { font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
.toast-msg    { color: var(--text-secondary); font-size: .8rem; line-height: 1.4; }
.toast-cerrar { background: none; border: none; cursor: pointer; color: var(--text-muted);
                font-size: 1rem; padding: 0; line-height: 1; flex-shrink: 0; }
.toast-cerrar:hover { color: var(--text-primary); }
@keyframes toastIn  { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
@keyframes toastOut { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(20px); } }

/* ── Barra de progreso upload ───────────────────────────── */
.upload-progress { margin-top: 14px; display: none; }
.upload-progress-bar-wrap {
    background: var(--surface-2); border-radius: 99px;
    height: 8px; overflow: hidden; margin-bottom: 6px;
}
.upload-progress-bar {
    height: 100%; background: var(--navy);
    border-radius: 99px; transition: width .2s ease; width: 0%;
}
.upload-progress-label { font-size: .73rem; color: var(--text-muted); text-align: center; }

/* ── Modal confirm eliminar ─────────────────────────────── */
.modal-icon { font-size: 2.2rem; text-align: center; margin-bottom: 10px; }
.modal-body-text { color: var(--text-secondary); font-size: .88rem; text-align: center;
                   margin-bottom: 6px; line-height: 1.5; }
.modal-body-sub  { color: var(--text-muted); font-size: .78rem; text-align: center;
                   margin-bottom: 20px; }
.btn-danger { padding: 9px 20px; border-radius: 4px; border: none;
              background: #DC2626; color: #fff; font-size: .82rem;
              font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-danger:hover { background: #B91C1C; }
.btn-danger:disabled { opacity: .5; cursor: not-allowed; }

/* Fila eliminada */
.fila-eliminando td { opacity: .4; transition: opacity .3s; }

/* ── Checkboxes de selección ────────────────────────────── */
.cb-check {
    width: 16px; height: 16px; cursor: pointer;
    accent-color: var(--navy); flex-shrink: 0;
}
.th-check { width: 36px; text-align: center !important; padding: 11px 8px !important; }
.td-check { width: 36px; text-align: center; padding: 11px 8px !important; }
tr.seleccionada td { background: #EFF6FF !important; }

/* ── Barra flotante de acciones en lote ─────────────────── */
.bulk-bar {
    position: fixed; bottom: -80px; left: 50%; transform: translateX(-50%);
    background: var(--navy); color: #fff;
    padding: 12px 20px; border-radius: 40px;
    display: flex; align-items: center; gap: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.25);
    font-size: .84rem; font-weight: 500;
    transition: bottom .25s cubic-bezier(.34,1.56,.64,1);
    z-index: 150; white-space: nowrap;
}
.bulk-bar.visible { bottom: 28px; }
.bulk-count {
    background: rgba(255,255,255,.2); border-radius: 99px;
    padding: 2px 10px; font-weight: 700; font-size: .8rem;
}
.bulk-btn-del {
    background: #DC2626; border: none; color: #fff;
    padding: 7px 16px; border-radius: 99px; font-size: .82rem;
    font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;
    transition: background .15s;
}
.bulk-btn-del:hover { background: #B91C1C; }
.bulk-btn-cancel {
    background: rgba(255,255,255,.15); border: none; color: #fff;
    padding: 7px 14px; border-radius: 99px; font-size: .82rem;
    cursor: pointer; transition: background .15s;
}
.bulk-btn-cancel:hover { background: rgba(255,255,255,.25); }
</style>
@endpush

@section('content')
<div class="doc-layout">
    <div class="content-panel">
        <div class="content-header">

            {{-- Breadcrumb --}}
            @if(isset($breadcrumb) && count($breadcrumb) > 0)
            <div class="breadcrumb">
                @php
                    $padreId   = isset($carpetaActual) && $carpetaActual->id_padre > 0 ? $carpetaActual->id_padre : null;
                    $backHref  = $esAdmin && $padreId ? route('carpetas.show', $padreId) : route('panel');
                @endphp
            
                <a href="{{ route('panel') }}" class="breadcrumb-item">Inicio</a>
                @foreach($breadcrumb as $i => $migaja)
                    <span class="breadcrumb-sep">›</span>
                    @if($i === count($breadcrumb) - 1)
                        <span class="breadcrumb-item activo">{{ $migaja['descripcion'] }}</span>
                    @elseif($esAdmin)
                        <a href="{{ route('carpetas.show', $migaja['id']) }}" class="breadcrumb-item">{{ $migaja['descripcion'] }}</a>
                    @else
                        <span class="breadcrumb-item">{{ $migaja['descripcion'] }}</span>
                    @endif
                @endforeach
            </div>
            @endif

            <div class="content-actions">
                <div class="content-title">
                    {{ isset($carpetaActual) ? $carpetaActual->descripcion : 'Gestión Documental' }}
                </div>
                @if(isset($permisos) && $permisos['crear'])
                <button class="btn-new-folder" onclick="abrirModalCarpeta()">
                    📂 Nueva carpeta
                </button>
                @endif
                @if(isset($permisos) && $permisos['carga'])
                <button class="btn-upload" onclick="abrirModalSubir()">
                    📤 Subir archivo
                </button>
                @endif
            </div>
        </div>

        <div class="content-scroll">


            @if(isset($carpetaActual))

                {{-- Subcarpetas --}}
                @if(isset($subcarpetas) && $subcarpetas->count() > 0)
                <div class="section-label">📂 Carpetas ({{ $subcarpetas->count() }})</div>
                <div class="subcarpetas-grid">
                    @foreach($subcarpetas as $sub)
                    <div class="subcarpeta-wrap">
                        <a href="{{ route('carpetas.show', $sub->id) }}" class="subcarpeta-card">
                            📁 {{ $sub->descripcion }}
                        </a>
                        @if(isset($permisos) && $permisos['eliminar'])
                        <button class="subcarpeta-del" title="Eliminar carpeta"
                                onclick="pedirEliminarCarpeta({{ $sub->id }}, '{{ addslashes($sub->descripcion) }}', this.closest('.subcarpeta-wrap'))">
                            🗑
                        </button>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Documentos --}}
                <div class="section-label">
                    📄 Documentos
                    @if(isset($contenido))
                        <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted)">({{ $contenido->count() }})</span>
                    @endif
                </div>

                @if(isset($contenido) && $contenido->count() > 0)
                <div class="archivos-table-wrap">
                    <table class="archivos-table">
                        <thead>
                            <tr>
                                @if(isset($permisos) && $permisos['eliminar'])
                                <th class="th-check">
                                    <input type="checkbox" class="cb-check" id="cb-todos" onchange="toggleTodos(this)">
                                </th>
                                @endif
                                <th>Nombre</th>
                                <th style="width:80px">Tipo</th>
                                <th style="width:95px">Fecha</th>
                                <th style="width:170px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contenido as $archivo)
                            @php
                                $ext = $archivo->extension;
                                $extClass = match(true) {
                                    $ext === 'pdf'                              => 'ext-pdf',
                                    in_array($ext, ['doc','docx'])             => 'ext-doc',
                                    in_array($ext, ['xls','xlsx','xlsm'])      => 'ext-xls',
                                    in_array($ext, ['jpg','jpeg','png','gif','webp']) => 'ext-img',
                                    default                                    => 'ext-other',
                                };
                            @endphp
                            <tr data-id="{{ $archivo->id }}">
                                @if(isset($permisos) && $permisos['eliminar'])
                                <td class="td-check">
                                    <input type="checkbox" class="cb-check cb-archivo"
                                           value="{{ $archivo->id }}"
                                           onchange="toggleSeleccion(this)">
                                </td>
                                @endif
                                <td>
                                    <span class="archivo-nombre">{{ $archivo->nombre }}</span>
                                    @if($archivo->es_legacy)
                                        <span style="font-size:.68rem;color:var(--text-muted)">Archivo legacy</span>
                                    @endif
                                </td>
                                <td><span class="archivo-ext {{ $extClass }}">{{ strtoupper($ext ?: 'FILE') }}</span></td>
                                <td style="font-size:.75rem;white-space:nowrap;color:var(--text-muted)">
                                    {{ $archivo->creada_el ? \Carbon\Carbon::parse($archivo->creada_el)->format('d/m/Y') : '—' }}
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                                        @if(isset($permisos) && $permisos['descarga'])
                                        @php
                                            $esVisualizaable = in_array($ext, ['pdf','jpg','jpeg','png','gif','webp']);
                                        @endphp
                                        @if($esVisualizaable)
                                        <button class="btn-accion btn-ver"
                                                onclick="abrirVisor('{{ route('archivos.ver', $archivo->id) }}', '{{ addslashes($archivo->nombre) }}', '{{ $ext }}')">
                                            👁 Ver
                                        </button>
                                        @endif
                                        <a href="{{ route('archivos.descargar', $archivo->id) }}" class="btn-accion btn-dl">
                                            ⬇ Descargar
                                        </a>
                                        @endif
                                        @if(isset($permisos) && $permisos['eliminar'])
                                        <button class="btn-accion btn-del"
                                                onclick="pedirEliminar({{ $archivo->id }}, '{{ addslashes($archivo->nombre) }}', this.closest('tr'))">
                                            🗑 Eliminar
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <div class="empty-text">Sin documentos en esta carpeta</div>
                    @if(isset($permisos) && $permisos['carga'])
                        <div style="margin-top:14px">
                            <button class="btn-upload" onclick="abrirModalSubir()">Subir archivo</button>
                        </div>
                    @endif
                </div>
                @endif

            @else
            <div class="empty-state">
                <div class="empty-icon">📁</div>
                <div class="empty-text">Selecciona una carpeta para ver su contenido.</div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Contenedor de toasts --}}
<div id="toast-container"></div>

@if(isset($permisos) && $permisos['eliminar'])
{{-- Barra flotante de selección múltiple --}}
<div class="bulk-bar" id="bulk-bar">
    <span><span class="bulk-count" id="bulk-count">0</span> seleccionados</span>
    <button class="bulk-btn-del" onclick="eliminarSeleccionados()">
        🗑 Eliminar seleccionados
    </button>
    <button class="bulk-btn-cancel" onclick="limpiarSeleccion()">Cancelar</button>
</div>
@endif

{{-- Modal: Subir archivo --}}
<div class="modal-overlay" id="modal-upload">
    <div class="modal">
        <div class="modal-title">📤 Subir documento</div>
        <div class="drop-zone" id="drop-zone" onclick="document.getElementById('file-input').click()">
            <div style="font-size:2.4rem;margin-bottom:8px">📎</div>
            <div style="font-size:.84rem;color:var(--text-muted)">
                Arrastra archivo aquí o <strong>haz clic para seleccionar</strong>
            </div>
            <div id="file-name" style="margin-top:10px;font-size:.78rem;color:var(--navy);font-weight:500"></div>
            <input type="file" name="archivo" id="file-input" onchange="mostrarNombreArchivo(this)">
        </div>
        <div style="font-size:.73rem;color:var(--text-muted);margin-bottom:10px">
            PDF, Word, Excel, PowerPoint, imágenes, ZIP · Máx. 20 MB
        </div>
        <div class="upload-progress" id="upload-progress">
            <div class="upload-progress-bar-wrap">
                <div class="upload-progress-bar" id="upload-bar"></div>
            </div>
            <div class="upload-progress-label" id="upload-label">Subiendo...</div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" id="btn-cancelar-upload" onclick="cerrarModalSubir()">Cancelar</button>
            <button type="button" class="btn-upload" id="btn-subir" onclick="ejecutarSubida()">📤 Subir</button>
        </div>
    </div>
</div>

{{-- Modal: Nueva carpeta --}}
<div class="modal-overlay" id="modal-carpeta">
    <div class="modal">
        <div class="modal-title">📁 Nueva carpeta</div>
        <form method="POST" action="{{ isset($carpetaActual) ? route('carpetas.store', $carpetaActual->id) : '#' }}">
            @csrf
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:.8rem;font-weight:600;color:var(--navy);margin-bottom:8px">Nombre de la carpeta</label>
                <input type="text" name="descripcion" id="carpeta-nombre" placeholder="Ej: Auditorías 2025" required
                       style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:4px;font-size:.85rem;outline:none;box-sizing:border-box"
                       onfocus="this.style.borderColor='var(--blue-accent)'"
                       onblur="this.style.borderColor='var(--border)'">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalCarpeta()">Cancelar</button>
                <button type="submit" class="btn-upload">📁 Crear carpeta</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Confirmar eliminación --}}
<div class="modal-overlay" id="modal-eliminar">
    <div class="modal" style="max-width:420px">
        <div class="modal-icon">🗑️</div>
        <div class="modal-body-text" id="eliminar-texto">¿Eliminar este archivo?</div>
        <div class="modal-body-sub">Esta acción no se puede deshacer.</div>
        <div class="modal-actions" style="justify-content:center">
            <button type="button" class="btn-cancel" onclick="cerrarModalEliminar()">Cancelar</button>
            <button type="button" class="btn-danger" id="btn-confirmar-eliminar" onclick="confirmarEliminar()">Eliminar</button>
        </div>
    </div>
</div>

{{-- Visor de documentos --}}
<div class="viewer-modal" id="viewer-modal">
    <div class="viewer-container">
        <div class="viewer-header">
            <span style="font-size:1rem">📄</span>
            <span class="viewer-titulo" id="viewer-titulo">Documento</span>
            <button class="viewer-close" onclick="cerrarVisor()">✕</button>
        </div>
        <div class="viewer-body">
            <div class="viewer-loading" id="viewer-loading">
                <div class="viewer-spinner"></div>
                <span>Cargando...</span>
            </div>
            <div id="viewer-content" style="width:100%;height:100%;display:none"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ═══════════════════════════════════════════════════════════
// TOAST NOTIFICATIONS
// ═══════════════════════════════════════════════════════════
function toast(mensaje, tipo, titulo) {
    var config = {
        ok:    { icono: '✅', titulo: titulo || 'Listo',  cls: 'toast-ok'    },
        error: { icono: '❌', titulo: titulo || 'Error',  cls: 'toast-error' },
        info:  { icono: 'ℹ️', titulo: titulo || 'Info',   cls: 'toast-info'  },
    };
    var c   = config[tipo] || config.info;
    var el  = document.createElement('div');
    el.className = 'toast ' + c.cls;
    el.innerHTML =
        '<span class="toast-icono">' + c.icono + '</span>' +
        '<div class="toast-cuerpo">' +
            '<div class="toast-titulo">' + c.titulo + '</div>' +
            '<div class="toast-msg">'   + mensaje   + '</div>' +
        '</div>' +
        '<button class="toast-cerrar" onclick="quitarToast(this.parentElement)">✕</button>';
    document.getElementById('toast-container').appendChild(el);
    setTimeout(() => quitarToast(el), 4500);
}
function quitarToast(el) {
    if (!el || !el.parentElement) return;
    el.classList.add('saliendo');
    setTimeout(() => el.remove(), 250);
}

// ═══════════════════════════════════════════════════════════
// MODAL: SUBIR ARCHIVO
// ═══════════════════════════════════════════════════════════
var carpetaId = {{ isset($carpetaActual) ? $carpetaActual->id : 'null' }};

function abrirModalSubir() {
    resetModalSubir();
    document.getElementById('modal-upload').classList.add('visible');
}
function cerrarModalSubir() {
    document.getElementById('modal-upload').classList.remove('visible');
    resetModalSubir();
}
function resetModalSubir() {
    document.getElementById('file-name').textContent = '';
    document.getElementById('file-input').value = '';
    document.getElementById('upload-progress').style.display = 'none';
    document.getElementById('upload-bar').style.width = '0%';
    document.getElementById('upload-label').textContent = 'Subiendo...';
    document.getElementById('btn-subir').disabled = false;
    document.getElementById('btn-subir').textContent = '📤 Subir';
    document.getElementById('btn-cancelar-upload').disabled = false;
}
function mostrarNombreArchivo(input) {
    document.getElementById('file-name').textContent = input.files[0] ? '📄 ' + input.files[0].name : '';
}

function ejecutarSubida() {
    var fileInput = document.getElementById('file-input');
    if (!fileInput.files.length) {
        toast('Selecciona un archivo antes de continuar.', 'error');
        return;
    }
    if (!carpetaId) {
        toast('No hay una carpeta seleccionada.', 'error');
        return;
    }

    var formData = new FormData();
    formData.append('archivo',    fileInput.files[0]);
    formData.append('id_carpeta', carpetaId);
    formData.append('_token',     window.CSRF_TOKEN);

    var btn   = document.getElementById('btn-subir');
    var prog  = document.getElementById('upload-progress');
    var bar   = document.getElementById('upload-bar');
    var label = document.getElementById('upload-label');

    btn.disabled = true;
    btn.textContent = 'Subiendo...';
    document.getElementById('btn-cancelar-upload').disabled = true;
    prog.style.display = 'block';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route("archivos.subir") }}');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            var pct = Math.round((e.loaded / e.total) * 100);
            bar.style.width   = pct + '%';
            label.textContent = pct < 100 ? pct + '% subido...' : 'Procesando...';
        }
    });

    xhr.addEventListener('load', function() {
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.ok) {
                bar.style.width   = '100%';
                label.textContent = '¡Listo!';
                cerrarModalSubir();
                toast('"' + data.documento.nombre + '" subido correctamente.', 'ok', 'Archivo guardado');
                setTimeout(() => location.reload(), 1200);
            } else {
                toast(data.error || 'No se pudo subir el archivo.', 'error', 'Error al subir');
                resetModalSubir();
            }
        } catch(e) {
            toast('Respuesta inesperada del servidor.', 'error', 'Error');
            resetModalSubir();
        }
    });

    xhr.addEventListener('error', function() {
        toast('Error de conexión. Intenta nuevamente.', 'error', 'Sin conexión');
        resetModalSubir();
    });

    xhr.send(formData);
}

// Drag & drop
(function() {
    var dz = document.getElementById('drop-zone');
    if (!dz) return;
    dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('drag-over'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
    dz.addEventListener('drop', e => {
        e.preventDefault(); dz.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            document.getElementById('file-input').files = e.dataTransfer.files;
            mostrarNombreArchivo(document.getElementById('file-input'));
        }
    });
})();

// ═══════════════════════════════════════════════════════════
// MODAL: NUEVA CARPETA
// ═══════════════════════════════════════════════════════════
function abrirModalCarpeta() {
    document.getElementById('carpeta-nombre').value = '';
    document.getElementById('modal-carpeta').classList.add('visible');
    setTimeout(() => document.getElementById('carpeta-nombre').focus(), 100);
}
function cerrarModalCarpeta() {
    document.getElementById('modal-carpeta').classList.remove('visible');
}

// ═══════════════════════════════════════════════════════════
// SELECCIÓN MÚLTIPLE
// ═══════════════════════════════════════════════════════════
function toggleSeleccion(cb) {
    var fila = cb.closest('tr');
    fila.classList.toggle('seleccionada', cb.checked);
    actualizarBulkBar();
}

function toggleTodos(cbTodos) {
    document.querySelectorAll('.cb-archivo').forEach(function(cb) {
        cb.checked = cbTodos.checked;
        cb.closest('tr').classList.toggle('seleccionada', cbTodos.checked);
    });
    actualizarBulkBar();
}

function actualizarBulkBar() {
    var seleccionados = document.querySelectorAll('.cb-archivo:checked').length;
    var bar = document.getElementById('bulk-bar');
    var cbTodos = document.getElementById('cb-todos');
    var total   = document.querySelectorAll('.cb-archivo').length;

    if (bar) {
        document.getElementById('bulk-count').textContent = seleccionados;
        bar.classList.toggle('visible', seleccionados > 0);
    }
    if (cbTodos) {
        cbTodos.indeterminate = seleccionados > 0 && seleccionados < total;
        cbTodos.checked       = seleccionados > 0 && seleccionados === total;
    }
}

function limpiarSeleccion() {
    document.querySelectorAll('.cb-archivo').forEach(cb => {
        cb.checked = false;
        cb.closest('tr').classList.remove('seleccionada');
    });
    var cbTodos = document.getElementById('cb-todos');
    if (cbTodos) { cbTodos.checked = false; cbTodos.indeterminate = false; }
    actualizarBulkBar();
}

function eliminarSeleccionados() {
    var ids = Array.from(document.querySelectorAll('.cb-archivo:checked')).map(cb => cb.value);
    if (!ids.length) return;

    var n   = ids.length;
    var txt = n === 1 ? '¿Eliminar 1 archivo seleccionado?' : '¿Eliminar ' + n + ' archivos seleccionados?';
    document.getElementById('eliminar-texto').textContent = txt;
    var btn = document.getElementById('btn-confirmar-eliminar');
    btn.disabled    = false;
    btn.textContent = 'Eliminar';
    btn.onclick     = function() { ejecutarBulkDelete(ids); };
    document.getElementById('modal-eliminar').classList.add('visible');
}

function ejecutarBulkDelete(ids) {
    var btn = document.getElementById('btn-confirmar-eliminar');
    btn.disabled    = true;
    btn.textContent = 'Eliminando...';

    fetch('{{ route("archivos.eliminar.lote") }}', {
        method: 'DELETE',
        headers: {
            'Content-Type':     'application/json',
            'X-CSRF-TOKEN':     window.CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept':           'application/json',
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(r => r.json())
    .then(data => {
        cerrarModalEliminar();
        if (data.ok) {
            // Eliminar filas del DOM
            ids.forEach(function(id) {
                var fila = document.querySelector('tr[data-id="' + id + '"]');
                if (fila) {
                    fila.style.transition = 'opacity .25s';
                    fila.style.opacity    = '0';
                    setTimeout(() => fila.remove(), 260);
                }
            });
            limpiarSeleccion();
            toast(data.mensaje, 'ok', 'Archivos eliminados');
            // Recargar si no quedan filas
            setTimeout(function() {
                var tbody = document.querySelector('.archivos-table tbody');
                if (tbody && !tbody.querySelector('tr')) location.reload();
            }, 500);
        } else {
            toast(data.error || 'No se pudieron eliminar los archivos.', 'error', 'Error');
        }
    })
    .catch(() => {
        cerrarModalEliminar();
        toast('Error de conexión. Intenta nuevamente.', 'error', 'Sin conexión');
    });
}

// ═══════════════════════════════════════════════════════════
// ELIMINAR ARCHIVO (AJAX)
// ═══════════════════════════════════════════════════════════
var _eliminarId   = null;
var _eliminarFila = null;

function pedirEliminar(id, nombre, fila) {
    _eliminarId   = id;
    _eliminarFila = fila;
    document.getElementById('eliminar-texto').textContent = '¿Eliminar "' + nombre + '"?';
    var btn = document.getElementById('btn-confirmar-eliminar');
    btn.disabled = false;
    btn.textContent = 'Eliminar';
    btn.onclick = confirmarEliminar;
    document.getElementById('modal-eliminar').classList.add('visible');
}
function cerrarModalEliminar() {
    document.getElementById('modal-eliminar').classList.remove('visible');
    _eliminarId = null; _eliminarFila = null;
}

function confirmarEliminar() {
    if (!_eliminarId) return;
    var btn = document.getElementById('btn-confirmar-eliminar');
    btn.disabled = true;
    btn.textContent = 'Eliminando...';
    if (_eliminarFila) _eliminarFila.classList.add('fila-eliminando');

    fetch('/archivos/' + _eliminarId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN':      window.CSRF_TOKEN,
            'X-Requested-With':  'XMLHttpRequest',
            'Accept':            'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        cerrarModalEliminar();
        if (data.ok) {
            if (_eliminarFila) {
                _eliminarFila.style.transition = 'opacity .3s, max-height .4s';
                _eliminarFila.style.opacity = '0';
                setTimeout(() => {
                    _eliminarFila && _eliminarFila.remove();
                    // Si no quedan filas, recargar para mostrar estado vacío
                    var tbody = document.querySelector('.archivos-table tbody');
                    if (tbody && !tbody.querySelector('tr')) location.reload();
                }, 320);
            }
            toast('Archivo eliminado correctamente.', 'ok', 'Eliminado');
        } else {
            if (_eliminarFila) _eliminarFila.classList.remove('fila-eliminando');
            toast(data.error || 'No se pudo eliminar el archivo.', 'error', 'Error');
        }
    })
    .catch(() => {
        cerrarModalEliminar();
        if (_eliminarFila) _eliminarFila.classList.remove('fila-eliminando');
        toast('Error de conexión. Intenta nuevamente.', 'error', 'Sin conexión');
    });
}

// ═══════════════════════════════════════════════════════════
// ELIMINAR CARPETA (AJAX)
// ═══════════════════════════════════════════════════════════
var _eliminarCarpetaId   = null;
var _eliminarCarpetaWrap = null;

function pedirEliminarCarpeta(id, nombre, wrap) {
    _eliminarCarpetaId   = id;
    _eliminarCarpetaWrap = wrap;
    document.getElementById('eliminar-texto').textContent = '¿Eliminar la carpeta "' + nombre + '"?';
    var btn = document.getElementById('btn-confirmar-eliminar');
    btn.disabled = false;
    btn.textContent = 'Eliminar';
    btn.onclick = confirmarEliminarCarpeta;
    document.getElementById('modal-eliminar').classList.add('visible');
}
function confirmarEliminarCarpeta() {
    if (!_eliminarCarpetaId) return;
    var btn = document.getElementById('btn-confirmar-eliminar');
    btn.disabled = true;
    btn.textContent = 'Eliminando...';

    fetch('/carpetas/' + _eliminarCarpetaId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN':     window.CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept':           'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        cerrarModalEliminar();
        if (data.ok) {
            if (_eliminarCarpetaWrap) {
                _eliminarCarpetaWrap.style.transition = 'opacity .3s';
                _eliminarCarpetaWrap.style.opacity = '0';
                setTimeout(() => { _eliminarCarpetaWrap && _eliminarCarpetaWrap.remove(); }, 320);
            }
            toast(data.mensaje, 'ok', 'Carpeta eliminada');
        } else {
            toast(data.error || 'No se pudo eliminar la carpeta.', 'error', 'Error');
        }
    })
    .catch(() => {
        cerrarModalEliminar();
        toast('Error de conexión. Intenta nuevamente.', 'error', 'Sin conexión');
    });
}

// ═══════════════════════════════════════════════════════════
// VISOR DE DOCUMENTOS
// ═══════════════════════════════════════════════════════════
var EXTS_IMG = ['jpg','jpeg','png','gif','webp','png'];

function abrirVisor(url, nombre, ext) {
    var content  = document.getElementById('viewer-content');
    var loading  = document.getElementById('viewer-loading');
    var titulo   = document.getElementById('viewer-titulo');

    content.innerHTML = '';
    content.style.display = 'none';
    loading.style.display  = 'flex';
    titulo.textContent = nombre;
    document.getElementById('viewer-modal').classList.add('visible');

    var el;
    if (['jpg','jpeg','png','gif','webp'].indexOf(ext) !== -1) {
        el = document.createElement('img');
        el.style.cssText = 'width:100%;height:100%;object-fit:contain';
        el.onload  = function() { loading.style.display='none'; content.style.display='block'; };
        el.onerror = function() { loading.innerHTML = '<span style="font-size:2rem">⚠️</span><span>No se pudo cargar la imagen</span>'; };
        el.src = url;
    } else {
        // PDF u otros visualizables
        el = document.createElement('iframe');
        el.style.cssText = 'width:100%;height:100%;border:none';
        el.onload = function() { loading.style.display='none'; content.style.display='block'; };
        el.src = url;
    }
    content.appendChild(el);
}

function cerrarVisor() {
    document.getElementById('viewer-modal').classList.remove('visible');
    var content = document.getElementById('viewer-content');
    content.innerHTML = '';
    content.style.display = 'none';
    document.getElementById('viewer-loading').style.display = 'flex';
    document.getElementById('viewer-loading').innerHTML =
        '<div class="viewer-spinner"></div><span>Cargando...</span>';
}

// ═══════════════════════════════════════════════════════════
// CIERRE DE MODALES AL HACER CLIC FUERA
// ═══════════════════════════════════════════════════════════
['modal-upload','modal-carpeta','modal-eliminar'].forEach(id => {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('visible');
    });
});
document.getElementById('viewer-modal').addEventListener('click', function(e) {
    if (e.target === this) cerrarVisor();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarVisor(); });

// ═══════════════════════════════════════════════════════════
// FLASH MESSAGES (de redirecciones con session 'ok')
// ═══════════════════════════════════════════════════════════
@if(session('ok'))
    toast('{{ addslashes(session('ok')) }}', 'ok');
@endif
@if($errors->any())
    toast('{{ addslashes($errors->first()) }}', 'error', 'Error de validación');
@endif
</script>
@endpush
