@extends('layouts.app')

@section('title', 'Gestión Documental')

@push('styles')
<style>
/* Estilos sin el panel lateral */
.doc-layout { display: flex; flex-direction: column; height: calc(100vh - 90px); }
.content-panel { display: flex; flex-direction: column; overflow: hidden; background: var(--body-bg); flex: 1; }
.content-header { background: var(--surface); border-bottom: 1px solid var(--border); padding: 16px 24px; flex-shrink: 0; }
.breadcrumb { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
.breadcrumb-item { font-size: .78rem; color: var(--text-muted); text-decoration: none; }
.breadcrumb-item:hover { color: var(--navy); }
.breadcrumb-item.activo { color: var(--navy); font-weight: 600; }
.breadcrumb-sep { color: var(--text-muted); font-size: .78rem; }
.btn-back { display: inline-flex; align-items: center; gap: 4px; font-size: .78rem; font-weight: 600; color: var(--navy); text-decoration: none; padding: 4px 10px; border: 1px solid var(--border); border-radius: 4px; background: var(--surface); transition: all .12s; }
.btn-back:hover { background: var(--surface-2); border-color: var(--navy); }
.content-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 12px; }
.content-title { font-size: 1.1rem; font-weight: 700; color: var(--navy); flex: 1; }
.btn-upload { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--navy); color: #fff; border: none; border-radius: 4px; font-size: .82rem; font-weight: 600; cursor: pointer; transition: background .15s; text-decoration: none; }
.btn-upload:hover { background: #0a2147; }
.content-scroll { flex: 1; overflow-y: auto; padding: 20px 24px; }

.section-label { font-size: .75rem; font-weight: 700; color: var(--navy); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 12px; margin-top: 20px; }

.subcarpetas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; margin-bottom: 24px; }
.subcarpeta-card { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; padding: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer; text-decoration: none; color: var(--text-primary); font-size: .8rem; font-weight: 500; transition: all .15s; text-align: center; }
.subcarpeta-card:hover { border-color: var(--blue-accent); background: var(--surface-2); transform: translateY(-2px); box-shadow: 0 2px 8px rgba(0,0,0,.08); }

.archivos-table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; overflow: hidden; }
.archivos-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.archivos-table th { background: var(--navy); color: #fff; padding: 11px 14px; text-align: left; font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.archivos-table td { padding: 11px 14px; border-bottom: 1px solid var(--border); color: var(--text-secondary); vertical-align: middle; }
.archivos-table tr:last-child td { border-bottom: none; }
.archivos-table tr:hover td { background: var(--surface-2); }
.archivo-nombre { font-weight: 500; color: var(--text-primary); display: block; }
.archivo-ext { display: inline-block; font-size: .65rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; margin-bottom: 2px; }
.ext-pdf { background: #FCEBEB; color: #991B1B; }
.ext-doc { background: #EFF6FF; color: #1D4ED8; }
.ext-xls { background: #F0FDF4; color: #15803D; }
.ext-img { background: #FDF4FF; color: #7C3AED; }
.ext-other { background: var(--surface-2); color: var(--text-muted); }
.btn-accion { display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border-radius: 4px; font-size: .75rem; font-weight: 600; cursor: pointer; border: 1px solid; text-decoration: none; transition: all .12s; }
.btn-ver { color: var(--blue-accent); border-color: var(--blue-accent); background: transparent; }
.btn-dl { color: var(--navy); border-color: var(--navy); background: transparent; }
.btn-del { color: var(--danger); border-color: var(--danger); background: transparent; }
.btn-ver:hover { background: var(--blue-accent); color: #fff; }
.btn-dl:hover { background: var(--navy); color: #fff; }
.btn-del:hover { background: var(--danger); color: #fff; }

.empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
.empty-icon { font-size: 2.8rem; margin-bottom: 12px; }
.empty-text { font-size: .85rem; }
.alert-ok { background: #DCFCE7; border-left: 4px solid #16A34A; color: #166534; padding: 11px 14px; border-radius: 4px; font-size: .82rem; margin-bottom: 16px; }
.alert-err { background: #FCEBEB; border-left: 4px solid #DC2626; color: #991B1B; padding: 11px 14px; border-radius: 4px; font-size: .82rem; margin-bottom: 16px; }

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

.viewer-modal { 
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0; 
    background: rgba(0,0,0,.9); 
    z-index: 300; 
    display: none;
    padding: 0;
    overflow: hidden;
    align-items: center;
    justify-content: center;
}
.viewer-modal.visible { display: flex !important; }

.viewer-container {
    position: absolute;
    width: 95vw;
    height: 95vh;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.viewer-modal iframe { 
    width: 100%;
    height: 100%;
    border: none; 
    border-radius: 6px;
    display: block;
    object-fit: contain;
}

.viewer-modal img { 
    max-width: 100%;
    max-height: 100%;
    border-radius: 6px;
    object-fit: contain;
    display: block;
}
.viewer-close { 
    position: absolute; 
    top: 10px; 
    right: 10px; 
    background: #fff; 
    border: none; 
    width: 40px; 
    height: 40px; 
    border-radius: 50%; 
    cursor: pointer; 
    font-size: 24px; 
    display: flex; 
    align-items: center; 
    justify-content: center;
    z-index: 10;
    transition: all .2s;
    box-shadow: 0 2px 8px rgba(0,0,0,.3);
}

.viewer-close:hover {
    background: #f0f0f0;
    transform: scale(1.1);
}

@media (max-width: 768px) {
    .subcarpetas-grid { grid-template-columns: repeat(2, 1fr); }
    .content-header { padding: 12px 16px; }
    .content-scroll { padding: 16px; }
    
    .viewer-modal {
        padding: 10px;
    }
    
    .viewer-close {
        width: 36px;
        height: 36px;
        font-size: 20px;
        top: 5px;
        right: 5px;
    }
}

@media (max-width: 480px) {
    .viewer-modal {
        padding: 5px;
    }
    
    .viewer-container {
        max-width: 95vw;
        max-height: 95vh;
    }
    
    .viewer-close {
        width: 32px;
        height: 32px;
        font-size: 18px;
    }
}

@media (max-width: 768px) {
    .subcarpetas-grid { grid-template-columns: repeat(2, 1fr); }
    .content-header { padding: 12px 16px; }
    .content-scroll { padding: 16px; }
}
</style>
@endpush

@section('content')

<div class="doc-layout">
    <div class="content-panel">
        <div class="content-header">
            {{-- Breadcrumb sin opción de retroceder al módulo --}}
            @if(isset($breadcrumb) && count($breadcrumb) > 0)
            <div class="breadcrumb">
                @foreach($breadcrumb as $i => $migaja)
                    @if($i > 0) <span class="breadcrumb-sep">›</span> @endif
                    @if($i === count($breadcrumb) - 1)
                        <span class="breadcrumb-item activo">{{ $migaja['nombre'] }}</span>
                    @else
                        <a href="{{ route('carpetas.show', ['modulo' => $modulo, 'id' => $migaja['id']]) }}" class="breadcrumb-item">
                            {{ $migaja['nombre'] }}
                        </a>
                    @endif
                @endforeach
            </div>
            @endif

            <div class="content-actions">
                <div class="content-title">
                    {{ isset($carpetaActual) ? $carpetaActual->descripcion : 'Carpeta' }}
                </div>
                @if(isset($permisos) && $permisos['crear'])
                <button class="btn-upload" onclick="abrirModalCarpeta()" style="background:#f5f5f5;color:#333;border:1px solid #ddd">
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
            {{-- Alertas --}}
            @if(session('ok'))
                <div class="alert-ok">✅ {{ session('ok') }}</div>
            @endif
            @if($errors->any())
                <div class="alert-err">❌ {{ $errors->first() }}</div>
            @endif

            @if(isset($carpetaActual))

                {{-- Subcarpetas --}}
                @if(isset($subcarpetas) && $subcarpetas->count() > 0)
                <div>
                    <div class="section-label">📂 Subcarpetas ({{ $subcarpetas->count() }})</div>
                    <div class="subcarpetas-grid">
                        @foreach($subcarpetas as $sub)
                        <a href="{{ route('carpetas.show', ['modulo' => $modulo, 'id' => $sub->id]) }}" class="subcarpeta-card">
                            📁 {{ $sub->descripcion }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Documentos --}}
                <div>
                    <div class="section-label">
                        📄 Documentos
                        @if(isset($contenido))
                            <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted)">
                                ({{ $contenido->count() }})
                            </span>
                        @endif
                    </div>

                    @if(isset($contenido) && $contenido->count() > 0)
                    <div class="archivos-table-wrap">
                        <table class="archivos-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th style="width:100px">Tipo</th>
                                    <th style="width:90px">Fecha</th>
                                    <th style="width:160px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contenido as $archivo)
                                @php
                                    $doc = $archivo->documento;
                                    $nombre = $doc ? $doc->nombre_original : $archivo->descripcion;
                                    $ext = $doc ? strtolower(pathinfo($doc->nombre_original, PATHINFO_EXTENSION)) : '';
                                    $extClass = match(true) {
                                        $ext === 'pdf' => 'ext-pdf',
                                        in_array($ext,['doc','docx']) => 'ext-doc',
                                        in_array($ext,['xls','xlsx','xlsm']) => 'ext-xls',
                                        in_array($ext,['jpg','jpeg','png','gif','webp']) => 'ext-img',
                                        default => 'ext-other',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="archivo-nombre">{{ $archivo->descripcion ?: 'Sin descripción' }}</div>
                                        <span style="font-size:.7rem;color:var(--text-muted)">{{ $nombre }}</span>
                                    </td>
                                    <td>
                                        <span class="archivo-ext {{ $extClass }}">{{ strtoupper($ext ?: 'FILE') }}</span>
                                    </td>
                                    <td style="font-size:.75rem;white-space:nowrap">
                                        {{ $archivo->creada_el ? $archivo->creada_el->format('d/m/Y') : '—' }}
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                                            @if(isset($permisos) && $permisos['descarga'] && $doc)
                                            <button class="btn-accion btn-ver" onclick="abrirVisor('{{ route('archivos.ver', $doc->id) }}', '{{ addslashes($nombre) }}', '{{ $ext }}')">
                                                👁️ Ver
                                            </button>
                                            <a href="{{ route('archivos.descargar', $doc->id) }}" class="btn-accion btn-dl">
                                                ⬇️ Descargar
                                            </a>
                                            @endif
                                            @if(isset($permisos) && $permisos['eliminar'])
                                            <form method="POST" action="{{ route('archivos.eliminar', $archivo->id) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar este archivo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-accion btn-del">🗑️ Eliminar</button>
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
                </div>

            @else
            <div class="empty-state">
                <div class="empty-icon">📁</div>
                <div class="empty-text">Cargando carpeta...</div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal: Subir archivo --}}
<div class="modal-overlay" id="modal-upload">
    <div class="modal">
        <div class="modal-title">📤 Subir documento</div>
        <form method="POST" action="{{ route('archivos.subir') }}" enctype="multipart/form-data" id="form-upload">
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
                ✅ PDF, Word, Excel, imágenes · 📊 Máx. 20 MB
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
        <form method="POST" action="{{ isset($carpetaActual) ? route('carpetas.store', ['modulo' => $modulo, 'id' => $carpetaActual->id]) : '#' }}" id="form-carpeta">
            @csrf
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:.8rem;font-weight:600;color:var(--navy);margin-bottom:8px">Nombre</label>
                <input type="text" name="descripcion" id="carpeta-nombre" placeholder="Ej: Auditorías 2025" required
                       style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:4px;font-size:.85rem;outline:none;box-sizing:border-box" 
                       onfocus="this.style.borderColor='var(--blue-accent)'" onblur="this.style.borderColor='var(--border)'">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalCarpeta()">Cancelar</button>
                <button type="submit" class="btn-upload">Crear</button>
            </div>
        </form>
    </div>
</div>

{{-- Visor de documentos --}}
<div class="viewer-modal" id="viewer-modal">
    <button class="viewer-close" onclick="cerrarVisor()">✕</button>
    <div id="viewer-content"></div>
</div>

@endsection

@push('scripts')
<style>
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    .alert-floating {
        animation: slideIn 0.3s ease-out;
    }
</style>
<script>
function abrirModalSubir() { 
    document.getElementById('modal-upload').classList.add('visible'); 
}

function cerrarModalSubir() { 
    document.getElementById('modal-upload').classList.remove('visible');
    document.getElementById('form-upload').reset();
    document.getElementById('file-name').textContent = '';
}

function abrirModalCarpeta() { 
    document.getElementById('modal-carpeta').classList.add('visible'); 
}

function cerrarModalCarpeta() { 
    document.getElementById('modal-carpeta').classList.remove('visible');
    document.getElementById('form-carpeta').reset();
}

function mostrarNombre(input) {
    document.getElementById('file-name').textContent = input.files[0] ? '📄 ' + input.files[0].name : '';
}

// Manejar envío del formulario de SUBIDA con AJAX
document.getElementById('form-upload')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    
    btn.disabled = true;
    btn.textContent = '⏳ Subiendo...';

    try {
        const response = await fetch('{{ route("archivos.subir") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const data = await response.json();

        if (response.ok && data.ok) {
            mostrarAlerta('✅ ' + data.mensaje, 'success');
            cerrarModalSubir();
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarAlerta('❌ ' + (data.error || 'Error al subir el archivo'), 'error');
        }
    } catch (error) {
        mostrarAlerta('❌ Error: ' + error.message, 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
});

function mostrarAlerta(mensaje, tipo) {
    const alertClass = tipo === 'success' ? 'alert-ok' : 'alert-err';
    const alertHtml = `<div class="${alertClass} alert-floating" style="position:fixed;top:20px;right:20px;z-index:1000;max-width:400px">${mensaje}</div>`;
    
    const alertEl = document.createElement('div');
    alertEl.innerHTML = alertHtml;
    document.body.appendChild(alertEl);
    
    setTimeout(() => alertEl.remove(), 4000);
}

function abrirVisor(url, nombre, ext) {
    const viewer = document.getElementById('viewer-modal');
    const content = document.getElementById('viewer-content');
    
    const esImagen = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext.toLowerCase());
    
    if (esImagen) {
        content.innerHTML = '<img src="' + url + '" style="max-width:100%;max-height:100%;border-radius:8px;">';
    } else {
        content.innerHTML = '<iframe src="' + url + '" style="width:100%;height:100%;border:none;border-radius:8px;"></iframe>';
    }
    
    viewer.classList.add('visible');
}

function cerrarVisor() {
    document.getElementById('viewer-modal').classList.remove('visible');
    document.getElementById('viewer-content').innerHTML = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarVisor();
});
</script>
@endpush