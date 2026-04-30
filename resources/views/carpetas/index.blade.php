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
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px; margin-bottom: 24px;
}
.subcarpeta-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 6px; padding: 12px;
    display: flex; flex-direction: column; align-items: center;
    gap: 8px; cursor: pointer; text-decoration: none;
    color: var(--text-primary); font-size: .8rem; font-weight: 500;
    transition: all .15s; text-align: center;
}
.subcarpeta-card:hover {
    border-color: var(--blue-accent); background: var(--surface-2);
    transform: translateY(-2px); box-shadow: 0 2px 8px rgba(0,0,0,.08);
}

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
.viewer-modal { position: fixed; inset: 0; background: rgba(0,0,0,.9); z-index: 300; display: none; align-items: center; justify-content: center; }
.viewer-modal.visible { display: flex; }
.viewer-container { position: relative; width: 95vw; height: 95vh; display: flex; flex-direction: column; }
.viewer-close { position: absolute; top: -44px; right: 0; background: #fff; border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center; transition: all .2s; }
.viewer-close:hover { background: #f0f0f0; transform: scale(1.1); }
.viewer-container iframe, .viewer-container img { width: 100%; height: 100%; border: none; border-radius: 6px; object-fit: contain; }

@media (max-width: 768px) {
    .subcarpetas-grid { grid-template-columns: repeat(2, 1fr); }
    .content-header, .content-scroll { padding: 12px 16px; }
    .archivos-table th:nth-child(3), .archivos-table td:nth-child(3) { display: none; }
}
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
                @if($padreId || ! $esAdmin)
                    <a href="{{ $backHref }}" class="breadcrumb-item">← Atrás</a>
                    <span class="breadcrumb-sep">·</span>
                @endif
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

            @if(session('ok'))
                <div class="alert-ok">✅ {{ session('ok') }}</div>
            @endif
            @if($errors->any())
                <div class="alert-err">❌ {{ $errors->first() }}</div>
            @endif

            @if(isset($carpetaActual))

                {{-- Subcarpetas --}}
                @if(isset($subcarpetas) && $subcarpetas->count() > 0)
                <div class="section-label">📂 Subcarpetas ({{ $subcarpetas->count() }})</div>
                <div class="subcarpetas-grid">
                    @foreach($subcarpetas as $sub)
                    <a href="{{ route('carpetas.show', $sub->id) }}" class="subcarpeta-card">
                        📁 {{ $sub->descripcion }}
                    </a>
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
                            <tr>
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
                                        <button class="btn-accion btn-ver"
                                                onclick="abrirVisor('{{ route('archivos.ver', $archivo->id) }}', '{{ addslashes($archivo->nombre) }}', '{{ $ext }}')">
                                            👁 Ver
                                        </button>
                                        <a href="{{ route('archivos.descargar', $archivo->id) }}" class="btn-accion btn-dl">
                                            ⬇ Descargar
                                        </a>
                                        @endif
                                        @if(isset($permisos) && $permisos['eliminar'])
                                        <form method="POST" action="{{ route('archivos.eliminar', $archivo->id) }}"
                                              style="display:inline"
                                              onsubmit="return confirm('¿Eliminar {{ addslashes($archivo->nombre) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-accion btn-del">🗑 Eliminar</button>
                                        </form>
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

{{-- Modal: Subir archivo --}}
<div class="modal-overlay" id="modal-upload">
    <div class="modal">
        <div class="modal-title">📤 Subir documento</div>
        <form method="POST" action="{{ route('archivos.subir') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_carpeta" value="{{ isset($carpetaActual) ? $carpetaActual->id : '' }}">
            <div class="drop-zone" id="drop-zone" onclick="document.getElementById('file-input').click()">
                <div style="font-size:2.4rem;margin-bottom:8px">📎</div>
                <div style="font-size:.84rem;color:var(--text-muted)">
                    Arrastra archivo aquí o <strong>haz clic</strong>
                </div>
                <div id="file-name" style="margin-top:10px;font-size:.78rem;color:var(--navy);font-weight:500"></div>
                <input type="file" name="archivo" id="file-input" onchange="mostrarNombre(this)">
            </div>
            <div style="font-size:.73rem;color:var(--text-muted);margin-bottom:6px">
                PDF, Word, Excel, imágenes · Máx. 20 MB
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalSubir()">Cancelar</button>
                <button type="submit" class="btn-upload">Subir</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Nueva carpeta --}}
<div class="modal-overlay" id="modal-carpeta">
    <div class="modal">
        <div class="modal-title">📁 Nueva carpeta</div>
        <form method="POST" action="{{ isset($carpetaActual) ? route('carpetas.store', $carpetaActual->id) : '#' }}">
            @csrf
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:.8rem;font-weight:600;color:var(--navy);margin-bottom:8px">Nombre</label>
                <input type="text" name="descripcion" id="carpeta-nombre" placeholder="Ej: Auditorías 2025" required
                       style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:4px;font-size:.85rem;outline:none;box-sizing:border-box"
                       onfocus="this.style.borderColor='var(--blue-accent)'"
                       onblur="this.style.borderColor='var(--border)'">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalCarpeta()">Cancelar</button>
                <button type="submit" class="btn-upload">Crear</button>
            </div>
        </form>
    </div>
</div>

{{-- Visor --}}
<div class="viewer-modal" id="viewer-modal">
    <div class="viewer-container">
        <button class="viewer-close" onclick="cerrarVisor()">✕</button>
        <div id="viewer-content" style="width:100%;height:100%"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function abrirModalSubir()  { document.getElementById('modal-upload').classList.add('visible'); }
function cerrarModalSubir() {
    document.getElementById('modal-upload').classList.remove('visible');
    document.getElementById('file-name').textContent = '';
    document.getElementById('file-input').value = '';
}

function abrirModalCarpeta() {
    document.getElementById('carpeta-nombre').value = '';
    document.getElementById('modal-carpeta').classList.add('visible');
    setTimeout(() => document.getElementById('carpeta-nombre').focus(), 100);
}
function cerrarModalCarpeta() { document.getElementById('modal-carpeta').classList.remove('visible'); }

function mostrarNombre(input) {
    document.getElementById('file-name').textContent = input.files[0] ? '📄 ' + input.files[0].name : '';
}

// Drag & drop
var dz = document.getElementById('drop-zone');
dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('drag-over'); });
dz.addEventListener('dragleave', () => dz.classList.remove('drag-over'));
dz.addEventListener('drop', e => {
    e.preventDefault(); dz.classList.remove('drag-over');
    if (e.dataTransfer.files.length) {
        document.getElementById('file-input').files = e.dataTransfer.files;
        mostrarNombre(document.getElementById('file-input'));
    }
});

// Cerrar modales al hacer clic fuera
['modal-upload','modal-carpeta'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('visible');
    });
});

// Visor
var EXTS_IMG = ['jpg','jpeg','png','gif','webp','bmp','svg'];
var EXTS_TXT = ['txt','csv','xml','json','html','htm'];

function abrirVisor(url, nombre, ext) {
    var content = document.getElementById('viewer-content');
    content.innerHTML = '';

    if (EXTS_IMG.indexOf(ext) !== -1) {
        var img = document.createElement('img');
        img.src = url;
        img.style.cssText = 'max-width:100%;max-height:100%;object-fit:contain;border-radius:6px';
        content.appendChild(img);
    } else {
        var iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.style.cssText = 'width:100%;height:100%;border:none;border-radius:6px';
        content.appendChild(iframe);
    }

    document.getElementById('viewer-modal').classList.add('visible');
}

function cerrarVisor() {
    document.getElementById('viewer-modal').classList.remove('visible');
    document.getElementById('viewer-content').innerHTML = '';
}

document.getElementById('viewer-modal').addEventListener('click', function(e) {
    if (e.target === this) cerrarVisor();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarVisor(); });
</script>
@endpush
