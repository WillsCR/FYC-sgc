@extends('layouts.app')

@section('title', 'Gestión Documental')


@push('styles')
<style>
/* ── Layout principal ──────────────────────────────────────── */
.doc-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    height: calc(100vh - 90px);
    overflow: hidden;
}

/* ── Panel árbol (izquierdo) ───────────────────────────────── */
.tree-panel {
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.tree-header {
    padding: 14px 16px 10px;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.tree-header-title {
    font-size: .72rem;
    font-weight: 700;
    color: var(--navy);
    text-transform: uppercase;
    letter-spacing: .06em;
}
.tree-search {
    margin-top: 8px;
    position: relative;
}
.tree-search input {
    width: 100%;
    padding: 7px 10px 7px 30px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: .78rem;
    font-family: var(--font);
    outline: none;
    color: var(--text-primary);
    background: var(--body-bg);
}
.tree-search input:focus { border-color: var(--blue-accent); }
.tree-search svg {
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}
.tree-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0;
}

/* ── Nodos del árbol ───────────────────────────────────────── */
.tree-node { user-select: none; }
.tree-node-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    cursor: pointer;
    border-radius: 0;
    font-size: .8rem;
    color: var(--text-secondary);
    transition: background .12s;
    text-decoration: none;
    position: relative;
}
.tree-node-item:hover   { background: var(--surface-2); color: var(--navy); }
.tree-node-item.activo  { background: var(--surface-2); color: var(--navy); font-weight: 600; }
.tree-node-item.activo::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: var(--navy);
    border-radius: 0 2px 2px 0;
}
.tree-toggle {
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: var(--text-muted);
    transition: transform .15s;
}
.tree-toggle.abierto { transform: rotate(90deg); }
.tree-folder-icon { flex-shrink: 0; }
.tree-label { flex: 1; line-height: 1.3; }
.tree-children { display: none; }
.tree-children.visible { display: block; }

/* ── Panel contenido (derecho) ─────────────────────────────── */
.content-panel {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: var(--body-bg);
}
.content-header {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 12px 20px;
    flex-shrink: 0;
}
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}
.breadcrumb-item {
    font-size: .75rem;
    color: var(--text-muted);
    text-decoration: none;
}
.breadcrumb-item:hover { color: var(--navy); }
.breadcrumb-item.activo { color: var(--navy); font-weight: 600; }
.breadcrumb-sep { color: var(--text-muted); font-size: .75rem; }
.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: .75rem;
    font-weight: 600;
    color: var(--navy);
    text-decoration: none;
    padding: 2px 8px 2px 4px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface);
    transition: all .12s;
}
.btn-back:hover { background: var(--surface-2); border-color: var(--navy); }
.content-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.content-title {
    font-size: .95rem;
    font-weight: 700;
    color: var(--navy);
    flex: 1;
}
.btn-upload {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: var(--navy);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
    text-decoration: none;
}
.btn-upload:hover { background: var(--navy-light); }
.content-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
}

/* ── Subcarpetas grid ──────────────────────────────────────── */
.subcarpetas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 8px;
    margin-bottom: 20px;
}
.subcarpeta-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-decoration: none;
    color: var(--text-primary);
    font-size: .78rem;
    font-weight: 500;
    transition: all .15s;
}
.subcarpeta-card:hover {
    border-color: var(--blue-accent);
    background: var(--surface-2);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

/* ── Tabla de archivos ─────────────────────────────────────── */
.archivos-table-wrap {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
}
.archivos-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .8rem;
}
.archivos-table th {
    background: var(--navy);
    color: #fff;
    padding: 9px 12px;
    text-align: left;
    font-size: .68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.archivos-table td {
    padding: 9px 12px;
    border-bottom: 1px solid var(--border);
    color: var(--text-secondary);
    vertical-align: middle;
}
.archivos-table tr:last-child td { border-bottom: none; }
.archivos-table tr:hover td { background: var(--surface-2); }
.archivo-icon { font-size: 1.1rem; flex-shrink: 0; }
.archivo-nombre { font-weight: 500; color: var(--text-primary); }
.archivo-ext {
    display: inline-block;
    font-size: .65rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    text-transform: uppercase;
}
.ext-pdf   { background: #FCEBEB; color: #991B1B; }
.ext-doc   { background: #EFF6FF; color: #1D4ED8; }
.ext-xls   { background: #F0FDF4; color: #15803D; }
.ext-img   { background: #FDF4FF; color: #7C3AED; }
.ext-other { background: var(--surface-2); color: var(--text-muted); }
.btn-accion {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: var(--radius-sm);
    font-size: .72rem;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid;
    text-decoration: none;
    transition: all .12s;
}
.btn-dl  { color: var(--navy);        border-color: var(--navy);        background: transparent; }
.btn-ver { color: var(--blue-accent); border-color: var(--blue-accent); background: transparent; }
.btn-del { color: var(--danger);      border-color: var(--danger);      background: transparent; }
.btn-dl:hover  { background: var(--navy);        color: #fff; }
.btn-ver:hover { background: var(--blue-accent); color: #fff; }
.btn-del:hover { background: var(--danger);      color: #fff; }

/* ── Upload modal ──────────────────────────────────────────── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 200;
    align-items: center;
    justify-content: center;
}
.modal-overlay.visible { display: flex; }
.modal {
    background: var(--surface);
    border-radius: var(--radius-lg);
    padding: 24px;
    width: 100%;
    max-width: 460px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
}
.modal-title {
    font-size: .95rem;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}
.drop-zone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-md);
    padding: 28px;
    text-align: center;
    cursor: pointer;
    transition: all .15s;
    margin-bottom: 14px;
}
.drop-zone:hover, .drop-zone.drag-over {
    border-color: var(--blue-accent);
    background: var(--surface-2);
}
.drop-zone input[type=file] { display: none; }
.drop-label { font-size: .82rem; color: var(--text-muted); margin-top: 8px; }
.drop-label strong { color: var(--navy); cursor: pointer; }
.modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px; }
.btn-cancel {
    padding: 8px 16px; border-radius: var(--radius-sm);
    border: 1px solid var(--border); background: transparent;
    font-size: .82rem; cursor: pointer; color: var(--text-secondary);
}
.btn-cancel:hover { background: var(--surface-2); }

/* ── Estados vacíos ────────────────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: var(--text-muted);
}
.empty-icon { font-size: 2.5rem; margin-bottom: 10px; }
.empty-text { font-size: .82rem; }

/* ── Alertas ───────────────────────────────────────────────── */
.alert-ok {
    background: #DCFCE7;
    border-left: 3px solid #16A34A;
    color: #166534;
    padding: 10px 14px;
    border-radius: var(--radius-sm);
    font-size: .82rem;
    margin-bottom: 14px;
}
.alert-err {
    background: #FCEBEB;
    border-left: 3px solid #DC2626;
    color: #991B1B;
    padding: 10px 14px;
    border-radius: var(--radius-sm);
    font-size: .82rem;
    margin-bottom: 14px;
}

/* ── Botón hamburguesa (móvil) ─────────────────────────────── */
.tree-toggle-btn {
    display: none;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    background: var(--navy);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    margin: 10px 16px;
}

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 768px) {
    .doc-layout {
        grid-template-columns: 1fr;
        height: auto;
        overflow: visible;
    }
    .tree-panel {
        border-right: none;
        border-bottom: 1px solid var(--border);
        max-height: 0;
        overflow: hidden;
        transition: max-height .3s ease;
    }
    .tree-panel.abierto {
        max-height: 400px;
        overflow-y: auto;
    }
    .tree-toggle-btn { display: flex; }
    .content-panel { overflow: visible; height: auto; }
    .content-scroll { overflow: visible; padding: 12px; }
    .archivos-table th:nth-child(3),
    .archivos-table td:nth-child(3) { display: none; }
    .subcarpetas-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@section('content')

{{-- Botón hamburguesa para móvil --}}
<button class="tree-toggle-btn" onclick="toggleTree()" id="tree-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 7h18M3 12h18M3 17h18"/>
    </svg>
    📁 Carpetas
</button>

<div class="doc-layout">

    {{-- ── Panel árbol ───────────────────────────────────────── --}}
    <div class="tree-panel" id="tree-panel">
        <div class="tree-header">
            <div class="tree-header-title">📁 Gestión Documental</div>
            <div class="tree-search">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" id="buscador-carpetas"
                       placeholder="Buscar carpeta..."
                       oninput="filtrarCarpetas(this.value)">
            </div>
        </div>

        <div class="tree-scroll" id="tree-scroll">
            @foreach($raices as $raiz)
            <div class="tree-node" id="nodo-{{ $raiz->id }}">
                <div class="tree-node-item {{ isset($carpetaActual) && $carpetaActual->id === $raiz->id ? 'activo' : '' }}"
                     onclick="toggleNodo({{ $raiz->id }}, this)">
                    <span class="tree-toggle" id="toggle-{{ $raiz->id }}">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </span>
                    <span class="tree-folder-icon">📁</span>
                    <a href="{{ route('carpetas.show', $raiz->id) }}"
                       class="tree-label"
                       onclick="event.stopPropagation()">
                        {{ $raiz->descripcion }}
                    </a>
                </div>
                <div class="tree-children" id="hijos-{{ $raiz->id }}">
                    {{-- Se carga dinámicamente via AJAX --}}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Panel contenido ────────────────────────────────────── --}}
    <div class="content-panel">
        <div class="content-header">

            {{-- Breadcrumb + botón retroceso --}}
            @if(isset($breadcrumb) && count($breadcrumb) > 0)
            <div class="breadcrumb">
                {{-- Botón atrás --}}
                @php
                    $padreId = isset($carpetaActual) && $carpetaActual->id_padre > 0
                        ? $carpetaActual->id_padre
                        : null;
                @endphp
                @if($padreId)
                    <a href="{{ route('carpetas.show', $padreId) }}" class="btn-back" title="Subir un nivel">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M19 12H5M12 5l-7 7 7 7"/>
                        </svg>
                        Atrás
                    </a>
                    <span class="breadcrumb-sep">·</span>
                @else
                    <a href="{{ route('carpetas.index') }}" class="btn-back" title="Ir al inicio">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M19 12H5M12 5l-7 7 7 7"/>
                        </svg>
                        Inicio
                    </a>
                    <span class="breadcrumb-sep">·</span>
                @endif

                <a href="{{ route('carpetas.index') }}" class="breadcrumb-item">📁 Inicio</a>
                @foreach($breadcrumb as $i => $migaja)
                    <span class="breadcrumb-sep">›</span>
                    @if($i === count($breadcrumb) - 1)
                        <span class="breadcrumb-item activo">{{ $migaja['descripcion'] }}</span>
                    @else
                        <a href="{{ route('carpetas.show', $migaja['id']) }}" class="breadcrumb-item">
                            {{ $migaja['descripcion'] }}
                        </a>
                    @endif
                @endforeach
            </div>
            @endif

            <div class="content-actions">
                <div class="content-title">
                    {{ isset($carpetaActual) ? $carpetaActual->descripcion : 'Selecciona una carpeta' }}
                </div>
                @if(isset($permisos) && $permisos['crear'])
                <button class="btn-upload" onclick="abrirModalCarpeta()"
                        style="background:var(--surface);color:var(--navy);border:1px solid var(--navy)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
                        <line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/>
                    </svg>
                    Nueva carpeta
                </button>
                @endif
                @if(isset($permisos) && $permisos['carga'])
                <button class="btn-upload" onclick="abrirModal()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                    </svg>
                    Subir archivo
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
                <div style="margin-bottom:8px">
                    <div class="section-label">Subcarpetas</div>
                    <div class="subcarpetas-grid">
                        @foreach($subcarpetas as $sub)
                        <a href="{{ route('carpetas.show', $sub->id) }}" class="subcarpeta-card">
                            📂 <span>{{ $sub->descripcion }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Archivos --}}
                <div class="section-label">
                    Archivos
                    @if(isset($contenido))
                        <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--text-muted)">
                            — {{ $contenido->count() }} {{ $contenido->count() === 1 ? 'archivo' : 'archivos' }}
                        </span>
                    @endif
                </div>

                @if(isset($contenido) && $contenido->count() > 0)
                <div class="archivos-table-wrap">
                    <table class="archivos-table">
                        <thead>
                            <tr>
                                <th style="width:36px"></th>
                                <th>Nombre</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th style="width:140px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contenido as $archivo)
                            @php
                                $ext = $archivo->extension;
                                $extClass = match(true) {
                                    $ext === 'pdf'                    => 'ext-pdf',
                                    in_array($ext,['doc','docx'])     => 'ext-doc',
                                    in_array($ext,['xls','xlsx'])     => 'ext-xls',
                                    in_array($ext,['jpg','jpeg','png','gif','webp']) => 'ext-img',
                                    default                           => 'ext-other',
                                };
                                $icono = match(true) {
                                    $ext === 'pdf'                    => '📄',
                                    in_array($ext,['doc','docx'])     => '📝',
                                    in_array($ext,['xls','xlsx'])     => '📊',
                                    in_array($ext,['jpg','jpeg','png','gif','webp']) => '🖼️',
                                    in_array($ext,['zip','rar'])      => '📦',
                                    default                           => '📎',
                                };
                            @endphp
                            <tr>
                                <td style="text-align:center">
                                    <span class="archivo-icon">{{ $icono }}</span>
                                </td>
                                <td>
                                    <div class="archivo-nombre">{{ $archivo->nombre }}</div>
                                    @if($archivo->es_legacy)
                                        <span style="font-size:.65rem;color:var(--text-muted)">Archivo legacy</span>
                                    @endif
                                </td>
                                <td style="font-size:.75rem;color:var(--text-muted);white-space:nowrap">
                                    {{ $archivo->creada_el ? \Carbon\Carbon::parse($archivo->creada_el)->format('d/m/Y') : '—' }}
                                </td>
                                <td>
                                    <span class="archivo-ext {{ $extClass }}">{{ strtoupper($ext) ?: 'FILE' }}</span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:5px;align-items:center">
                                        @if(isset($permisos) && $permisos['descarga'])
                                        <button class="btn-accion btn-ver"
                                                onclick="abrirVisor('{{ route('archivos.ver', $archivo->id) }}', '{{ addslashes($archivo->nombre) }}', '{{ $archivo->extension }}')">
                                            👁 Ver
                                        </button>
                                        <a href="{{ route('archivos.descargar', $archivo->id) }}"
                                           class="btn-accion btn-dl">
                                            ⬇ Descargar
                                        </a>
                                        @endif

                                        @if(isset($permisos) && $permisos['eliminar'])
                                        <form method="POST"
                                              action="{{ route('archivos.eliminar', $archivo->id) }}"
                                              onsubmit="return confirm('¿Eliminar {{ addslashes($archivo->nombre) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-accion btn-del">🗑</button>
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
                    <div class="empty-text">Esta carpeta no tiene archivos.</div>
                    @if(isset($permisos) && $permisos['carga'])
                        <div style="margin-top:10px">
                            <button class="btn-upload" onclick="abrirModal()">Subir el primer archivo</button>
                        </div>
                    @endif
                </div>
                @endif

            @else
            <div class="empty-state">
                <div class="empty-icon">📁</div>
                <div class="empty-text">Selecciona una carpeta del panel izquierdo para ver su contenido.</div>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- ── Modal de previsualización ──────────────────────────────── --}}
<div class="modal-overlay" id="modal-visor">
    <div class="modal" style="max-width:900px;width:95%;height:90vh;display:flex;flex-direction:column;padding:0;overflow:hidden">
        <div class="modal-title" style="padding:14px 20px;margin:0;border-radius:var(--radius-lg) var(--radius-lg) 0 0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
            <span id="visor-nombre" style="font-size:.9rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:75%"></span>
            <div style="display:flex;gap:8px;flex-shrink:0">
                <a id="visor-descargar" href="#" class="btn-accion btn-dl" style="font-size:.72rem">⬇ Descargar</a>
                <button onclick="cerrarVisor()" class="btn-cancel" style="padding:5px 12px;font-size:.78rem">✕ Cerrar</button>
            </div>
        </div>
        <div id="visor-body" style="flex:1;overflow:hidden;background:#525659;display:flex;align-items:center;justify-content:center">
            <iframe id="visor-iframe" src="" style="width:100%;height:100%;border:none;display:none"></iframe>
            <div id="visor-imagen" style="display:none;max-width:100%;max-height:100%;overflow:auto;padding:16px;text-align:center">
                <img id="visor-img-tag" src="" style="max-width:100%;max-height:calc(90vh - 60px);object-fit:contain;border-radius:4px">
            </div>
            <div id="visor-no-soportado" style="display:none;color:#fff;text-align:center;padding:40px">
                <div style="font-size:2.5rem;margin-bottom:12px">📄</div>
                <div style="font-size:.95rem;margin-bottom:16px">Este tipo de archivo no puede previsualizarse en el navegador.</div>
                <a id="visor-dl-fallback" href="#" class="btn-upload" style="display:inline-flex">⬇ Descargar archivo</a>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal de nueva carpeta ─────────────────────────────────── --}}
<div class="modal-overlay" id="modal-carpeta">
    <div class="modal">
        <div class="modal-title">📁 Nueva carpeta</div>

        <form method="POST"
              action="{{ isset($carpetaActual) ? route('carpetas.store', $carpetaActual->id) : '#' }}"
              id="form-carpeta">
            @csrf
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:.78rem;font-weight:600;color:var(--navy);margin-bottom:6px">
                    Nombre de la carpeta
                </label>
                <input type="text" name="descripcion" id="carpeta-nombre"
                       placeholder="Ej: Planos 2025"
                       style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:.85rem;font-family:var(--font);outline:none;color:var(--text-primary);background:var(--body-bg);box-sizing:border-box"
                       onfocus="this.style.borderColor='var(--blue-accent)'"
                       onblur="this.style.borderColor='var(--border)'">
            </div>

            <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:4px">
                Se creará como subcarpeta de <strong>{{ isset($carpetaActual) ? $carpetaActual->descripcion : '' }}</strong>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalCarpeta()">Cancelar</button>
                <button type="submit" class="btn-upload">Crear carpeta</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal de subida ────────────────────────────────────────── --}}
<div class="modal-overlay" id="modal-upload">
    <div class="modal">
        <div class="modal-title">📤 Subir archivo</div>

        <form method="POST" action="{{ route('archivos.subir') }}" enctype="multipart/form-data" id="form-upload">
            @csrf
            <input type="hidden" name="carpeta_id" value="{{ isset($carpetaActual) ? $carpetaActual->id : '' }}">

            <div class="drop-zone" id="drop-zone" onclick="document.getElementById('file-input').click()">
                <div style="font-size:2rem">📎</div>
                <div class="drop-label">
                    Arrastra un archivo aquí o <strong>haz clic para seleccionar</strong>
                </div>
                <div id="file-name" style="margin-top:8px;font-size:.78rem;color:var(--navy);font-weight:500"></div>
                <input type="file" name="archivo" id="file-input"
                       onchange="mostrarNombre(this)">
            </div>

            <div style="font-size:.72rem;color:var(--text-muted)">
                Tipos permitidos: PDF, Word, Excel, PowerPoint, imágenes, ZIP · Máximo 20 MB
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-upload">Subir archivo</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Modal de previsualización ─────────────────────────────────────────────
var EXTS_PDF   = ['pdf'];
var EXTS_IMAGE = ['jpg','jpeg','png','gif','webp','bmp','svg'];
var EXTS_TXT   = ['txt','csv','xml','json','html','htm'];

function abrirVisor(url, nombre, ext) {
    var overlay   = document.getElementById('modal-visor');
    var iframe    = document.getElementById('visor-iframe');
    var imgWrap   = document.getElementById('visor-imagen');
    var imgTag    = document.getElementById('visor-img-tag');
    var noSop     = document.getElementById('visor-no-soportado');
    var dlBtn     = document.getElementById('visor-descargar');
    var dlFallback= document.getElementById('visor-dl-fallback');
    var titulo    = document.getElementById('visor-nombre');
    var descUrl   = url.replace('/ver', '/descargar');

    titulo.textContent      = nombre;
    dlBtn.href              = descUrl;
    dlFallback.href         = descUrl;

    // Ocultar todos los paneles
    iframe.style.display  = 'none';
    imgWrap.style.display = 'none';
    noSop.style.display   = 'none';
    iframe.src            = '';
    imgTag.src            = '';

    if (EXTS_PDF.indexOf(ext) !== -1 || EXTS_TXT.indexOf(ext) !== -1) {
        iframe.src           = url;
        iframe.style.display = 'block';
    } else if (EXTS_IMAGE.indexOf(ext) !== -1) {
        imgTag.src            = url;
        imgWrap.style.display = 'block';
    } else {
        noSop.style.display = 'flex';
        noSop.style.flexDirection = 'column';
        noSop.style.alignItems = 'center';
    }

    overlay.classList.add('visible');
}

function cerrarVisor() {
    var overlay = document.getElementById('modal-visor');
    overlay.classList.remove('visible');
    document.getElementById('visor-iframe').src = '';
    document.getElementById('visor-img-tag').src = '';
}

document.getElementById('modal-visor').addEventListener('click', function(e) {
    if (e.target === this) cerrarVisor();
});

// ── Modal de nueva carpeta ────────────────────────────────────────────────
function abrirModalCarpeta() {
    document.getElementById('carpeta-nombre').value = '';
    document.getElementById('modal-carpeta').classList.add('visible');
    setTimeout(function() { document.getElementById('carpeta-nombre').focus(); }, 100);
}
function cerrarModalCarpeta() { document.getElementById('modal-carpeta').classList.remove('visible'); }
document.getElementById('modal-carpeta').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalCarpeta();
});

// ── Modal de subida ───────────────────────────────────────────────────────
function abrirModal()  { document.getElementById('modal-upload').classList.add('visible'); }
function cerrarModal() { document.getElementById('modal-upload').classList.remove('visible'); }

document.getElementById('modal-upload').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

function mostrarNombre(input) {
    var nombre = input.files[0] ? input.files[0].name : '';
    document.getElementById('file-name').textContent = nombre;
}

// Drag & drop
var dz = document.getElementById('drop-zone');
dz.addEventListener('dragover', function(e) { e.preventDefault(); dz.classList.add('drag-over'); });
dz.addEventListener('dragleave', function() { dz.classList.remove('drag-over'); });
dz.addEventListener('drop', function(e) {
    e.preventDefault();
    dz.classList.remove('drag-over');
    var files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('file-input').files = files;
        mostrarNombre(document.getElementById('file-input'));
    }
});

// ── Toggle árbol (móvil) ──────────────────────────────────────────────────
function toggleTree() {
    var panel = document.getElementById('tree-panel');
    panel.classList.toggle('abierto');
}

// ── Expansión dinámica del árbol ──────────────────────────────────────────
function toggleNodo(id, item) {
    var hijos    = document.getElementById('hijos-' + id);
    var toggle   = document.getElementById('toggle-' + id);
    var estaAbierto = hijos.classList.contains('visible');

    if (estaAbierto) {
        hijos.classList.remove('visible');
        toggle.classList.remove('abierto');
    } else {
        // Si no tiene hijos cargados, cargarlos vía AJAX
        if (hijos.children.length === 0) {
            cargarHijos(id, hijos);
        }
        hijos.classList.add('visible');
        toggle.classList.add('abierto');
    }
}

function cargarHijos(idPadre, contenedor) {
    fetch('/carpetas/' + idPadre + '/hijos', {
        headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(hijos) {
        if (hijos.length === 0) {
            contenedor.innerHTML = '<div style="padding:4px 12px 4px 40px;font-size:.72rem;color:var(--text-muted)">Sin subcarpetas</div>';
            return;
        }
        hijos.forEach(function(hijo) {
            var div = document.createElement('div');
            div.className = 'tree-node';
            div.id = 'nodo-' + hijo.id;
            div.style.paddingLeft = '16px';
            div.innerHTML =
                '<div class="tree-node-item" onclick="toggleNodo(' + hijo.id + ', this)">' +
                    '<span class="tree-toggle" id="toggle-' + hijo.id + '">' +
                        (hijo.tiene_hijos ? '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>' : '') +
                    '</span>' +
                    '<span class="tree-folder-icon">📁</span>' +
                    '<a href="/carpetas/' + hijo.id + '" class="tree-label" onclick="event.stopPropagation()">' +
                        hijo.descripcion +
                    '</a>' +
                '</div>' +
                '<div class="tree-children" id="hijos-' + hijo.id + '"></div>';
            contenedor.appendChild(div);
        });
    })
    .catch(function(e) { console.error('Error cargando hijos:', e); });
}

// ── Buscador de carpetas ──────────────────────────────────────────────────
function filtrarCarpetas(texto) {
    var items = document.querySelectorAll('.tree-node-item');
    var q = texto.toLowerCase().trim();
    items.forEach(function(item) {
        var label = item.querySelector('.tree-label');
        if (!label) return;
        var visible = q === '' || label.textContent.toLowerCase().includes(q);
        item.closest('.tree-node').style.display = visible ? '' : 'none';
    });
}

// ── Fix botón atrás ───────────────────────────────────────────────────────
window.history.pushState(null, '', window.location.href);
window.addEventListener('popstate', function() {
    window.history.pushState(null, '', window.location.href);
});
</script>
@endpush
