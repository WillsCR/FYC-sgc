@extends('layouts.app')
@section('title', 'Información SIG')

@push('styles')
<style>
/* ── Contenedor principal ─────────────────────────────────────────── */
.pub-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 20px 60px;
}

/* ── Barra de sección ─────────────────────────────────────────────── */
.pub-section-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f3f4f6;
    border-bottom: 2px solid #e5e7eb;
    padding: 10px 16px;
    margin-bottom: 24px;
    border-radius: 6px 6px 0 0;
}
.pub-section-title {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}
.pub-section-bar .btn-nueva {
    background: #2563eb;
    color: #fff;
    border: none;
    padding: 6px 16px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s;
}
.pub-section-bar .btn-nueva:hover { background: #1d4ed8; }

/* ── Tarjeta de publicación ───────────────────────────────────────── */
.pub-card {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 28px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
    background: #fff;
}
.pub-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #e5e7eb;
    padding: 8px 14px;
    gap: 10px;
}
.pub-card-titulo {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pub-card-acciones {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}
.pub-card-acciones button,
.pub-card-acciones a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: opacity .2s, transform .1s;
}
.pub-card-acciones button:hover,
.pub-card-acciones a:hover { opacity: .85; transform: scale(1.07); }

.btn-editar-pub   { background: #d97706; color: #fff; }
.btn-eliminar-pub { background: #dc2626; color: #fff; }
.btn-descargar-pub{ background: #0891b2; color: #fff; }

/* ── Cuerpo de la tarjeta ─────────────────────────────────────────── */
.pub-card-body {
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    min-height: 300px;
}
.pub-viewer-wrap {
    background: #fff;
    width: 100%;
    max-width: 860px;
    min-height: 460px;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pub-viewer-wrap iframe,
.pub-viewer-wrap img {
    width: 100%;
    display: block;
    border: none;
}
.pub-viewer-wrap iframe { height: 520px; }
.pub-viewer-wrap img    { max-height: 520px; object-fit: contain; }

/* ── Placeholder archivos no visualizables ───────────────────────── */
.pub-no-preview {
    text-align: center;
    color: #6b7280;
    padding: 40px 20px;
    font-size: 14px;
}
.pub-no-preview svg { margin-bottom: 12px; color: #9ca3af; }
.pub-no-preview a   { color: #2563eb; text-decoration: underline; }

/* ── Estado vacío ─────────────────────────────────────────────────── */
.pub-empty {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}
.pub-empty svg { margin-bottom: 16px; }
.pub-empty p   { font-size: 15px; }

/* ── Modal ───────────────────────────────────────────────────────── */
.pub-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 9000;
    align-items: center;
    justify-content: center;
}
.pub-modal-overlay.active { display: flex; }
.pub-modal {
    background: #fff;
    border-radius: 10px;
    width: 520px;
    max-width: 95vw;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    overflow: hidden;
}
.pub-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}
.pub-modal-header h3 { font-size: 16px; font-weight: 600; color: #1e293b; margin: 0; }
.pub-modal-close {
    background: none; border: none; cursor: pointer;
    font-size: 20px; color: #6b7280; line-height: 1;
}
.pub-modal-close:hover { color: #111; }
.pub-modal-body { padding: 20px; }
.pub-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.pub-form-group { display: flex; flex-direction: column; gap: 6px; }
.pub-form-group label { font-size: 13px; font-weight: 500; color: #374151; }
.pub-form-group input[type="text"] {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
}
.pub-form-group input[type="text"]:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37,99,235,.15);
}
.pub-file-btn {
    display: inline-block;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 7px 12px;
    font-size: 13px;
    cursor: pointer;
    background: #f9fafb;
    color: #374151;
    transition: background .15s;
}
.pub-file-btn:hover { background: #f3f4f6; }
.pub-file-name { font-size: 12px; color: #6b7280; margin-top: 4px; }
.pub-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
}
.btn-publicar {
    background: #2563eb; color: #fff; border: none;
    padding: 8px 20px; border-radius: 6px; font-size: 14px;
    font-weight: 500; cursor: pointer; transition: background .2s;
}
.btn-publicar:hover { background: #1d4ed8; }
.btn-publicar:disabled { background: #93c5fd; cursor: not-allowed; }
.btn-cerrar-modal {
    background: #6b7280; color: #fff; border: none;
    padding: 8px 16px; border-radius: 6px; font-size: 14px;
    cursor: pointer; transition: background .2s;
}
.btn-cerrar-modal:hover { background: #4b5563; }

/* ── Barra de progreso ────────────────────────────────────────────── */
.pub-progress-wrap {
    display: none;
    margin-top: 12px;
    background: #e5e7eb;
    border-radius: 4px;
    height: 6px;
    overflow: hidden;
}
.pub-progress-bar {
    height: 100%;
    background: #2563eb;
    width: 0%;
    border-radius: 4px;
    transition: width .2s;
}
.pub-progress-label {
    font-size: 11px;
    color: #6b7280;
    margin-top: 4px;
    display: none;
}

/* ── Modal edición ────────────────────────────────────────────────── */
.pub-modal-edit-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.4);
    z-index: 9100;
    align-items: center;
    justify-content: center;
}
.pub-modal-edit-overlay.active { display: flex; }

/* ── Toast ────────────────────────────────────────────────────────── */
#pub-toast-container {
    position: fixed; top: 20px; right: 20px;
    z-index: 99999; display: flex; flex-direction: column; gap: 10px;
}
.pub-toast {
    min-width: 280px; max-width: 380px;
    padding: 13px 18px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
    animation: pub-slideIn .3s ease;
    cursor: pointer;
}
.pub-toast.ok  { background: #16a34a; }
.pub-toast.err { background: #dc2626; }
.pub-toast.info{ background: #0891b2; }
@keyframes pub-slideIn {
    from { opacity:0; transform: translateX(40px); }
    to   { opacity:1; transform: translateX(0); }
}
@keyframes pub-fadeOut {
    to { opacity:0; transform: translateX(40px); }
}

/* ── Confirmación de eliminación ─────────────────────────────────── */
.pub-confirm-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.4);
    z-index: 9200;
    align-items: center;
    justify-content: center;
}
.pub-confirm-overlay.active { display: flex; }
.pub-confirm-box {
    background: #fff;
    border-radius: 10px;
    padding: 28px 28px 20px;
    width: 360px;
    max-width: 92vw;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
    text-align: center;
}
.pub-confirm-box p { font-size: 15px; color: #1e293b; margin-bottom: 20px; }
.pub-confirm-box strong { color: #dc2626; }
.pub-confirm-btns { display: flex; gap: 12px; justify-content: center; }
.btn-confirm-si {
    background: #dc2626; color: #fff; border: none;
    padding: 8px 22px; border-radius: 6px; font-size: 14px;
    font-weight: 500; cursor: pointer;
}
.btn-confirm-no {
    background: #e5e7eb; color: #374151; border: none;
    padding: 8px 22px; border-radius: 6px; font-size: 14px;
    cursor: pointer;
}
</style>
@endpush

@section('content')
<div class="pub-page">

    {{-- ── Barra superior ─────────────────────────────────────────── --}}
    <div class="pub-section-bar">
        <span class="pub-section-title">Noticias</span>
        @if($esAdmin)
        <button class="btn-nueva" id="btn-nueva-noticia">+ Nueva noticia</button>
        @endif
    </div>

    {{-- ── Listado de publicaciones ────────────────────────────────── --}}
    <div id="pub-lista">
        @forelse($publicaciones as $pub)
        <div class="pub-card" data-id="{{ $pub->id }}">
            <div class="pub-card-header">
                <span class="pub-card-titulo">{{ $pub->titulo }}</span>
                <div class="pub-card-acciones">
                    @if($esAdmin)
                    <button class="btn-editar-pub" title="Editar título"
                            data-id="{{ $pub->id }}" data-titulo="{{ e($pub->titulo) }}"
                            onclick="abrirEditModal(this)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-eliminar-pub" title="Eliminar"
                            data-id="{{ $pub->id }}" data-titulo="{{ e($pub->titulo) }}"
                            onclick="confirmarEliminar(this)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                    @endif
                    <a class="btn-descargar-pub" href="{{ route('publicaciones.descargar', $pub->id) }}" title="Descargar">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </a>
                </div>
            </div>
            <div class="pub-card-body">
                <div class="pub-viewer-wrap">
                    @if($pub->esVisualizableEnLinea())
                        @if($pub->tipo_mime === 'application/pdf')
                            <iframe src="{{ route('publicaciones.ver', $pub->id) }}"
                                    loading="lazy" title="{{ $pub->titulo }}"></iframe>
                        @else
                            <img src="{{ route('publicaciones.ver', $pub->id) }}"
                                 alt="{{ $pub->titulo }}" loading="lazy">
                        @endif
                    @else
                        <div class="pub-no-preview">
                            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                            <p>{{ $pub->nombre_original }}<br>
                               <small>{{ $pub->tamanioFormateado() }}</small></p>
                            <a href="{{ route('publicaciones.descargar', $pub->id) }}">Descargar archivo</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="pub-empty" id="pub-empty-state">
            <svg width="56" height="56" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <p>No hay noticias publicadas aún.</p>
        </div>
        @endforelse
    </div>

</div>

{{-- ── Modal: Nueva noticia ──────────────────────────────────────────── --}}
@if($esAdmin)
<div class="pub-modal-overlay" id="modal-nueva">
    <div class="pub-modal">
        <div class="pub-modal-header">
            <h3>Nueva Noticia</h3>
            <button class="pub-modal-close" onclick="cerrarModal()">×</button>
        </div>
        <div class="pub-modal-body">
            <div class="pub-form-row">
                <div class="pub-form-group" style="grid-column:1/-1">
                    <label for="nf-titulo">Título:</label>
                    <input type="text" id="nf-titulo" placeholder="Título de la noticia">
                </div>
            </div>
            <div class="pub-form-group">
                <label>Archivo:</label>
                <label class="pub-file-btn" for="nf-archivo">Elegir archivo</label>
                <input type="file" id="nf-archivo" style="display:none"
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp">
                <span class="pub-file-name" id="nf-archivo-nombre">No se ha seleccionado ningún archivo</span>
            </div>
            <div class="pub-progress-wrap" id="nf-progress-wrap">
                <div class="pub-progress-bar" id="nf-progress-bar"></div>
            </div>
            <div class="pub-progress-label" id="nf-progress-label"></div>
        </div>
        <div class="pub-modal-footer">
            <button class="btn-publicar" id="btn-publicar" onclick="publicar()">Publicar</button>
            <button class="btn-cerrar-modal" onclick="cerrarModal()">Cerrar</button>
        </div>
    </div>
</div>

{{-- ── Modal: Editar título ─────────────────────────────────────────── --}}
<div class="pub-modal-edit-overlay" id="modal-editar">
    <div class="pub-modal" style="width:420px">
        <div class="pub-modal-header">
            <h3>Editar título</h3>
            <button class="pub-modal-close" onclick="cerrarEditModal()">×</button>
        </div>
        <div class="pub-modal-body">
            <div class="pub-form-group">
                <label for="edit-titulo">Nuevo título:</label>
                <input type="text" id="edit-titulo" placeholder="Título de la noticia">
            </div>
        </div>
        <div class="pub-modal-footer">
            <button class="btn-publicar" onclick="guardarTitulo()">Guardar</button>
            <button class="btn-cerrar-modal" onclick="cerrarEditModal()">Cancelar</button>
        </div>
    </div>
</div>

{{-- ── Confirmación eliminación ─────────────────────────────────────── --}}
<div class="pub-confirm-overlay" id="confirm-overlay">
    <div class="pub-confirm-box">
        <p>¿Eliminar la publicación <strong id="confirm-titulo"></strong>?<br>
           <small style="color:#6b7280">Esta acción no se puede deshacer.</small></p>
        <div class="pub-confirm-btns">
            <button class="btn-confirm-si" onclick="ejecutarEliminar()">Eliminar</button>
            <button class="btn-confirm-no" onclick="cerrarConfirm()">Cancelar</button>
        </div>
    </div>
</div>
@endif

{{-- ── Toast container ──────────────────────────────────────────────── --}}
<div id="pub-toast-container"></div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ── Toast ──────────────────────────────────────────────────────────────────
function toast(msg, tipo = 'ok') {
    const tc = document.getElementById('pub-toast-container');
    const t  = document.createElement('div');
    t.className = 'pub-toast ' + tipo;
    t.textContent = msg;
    t.onclick = () => t.remove();
    tc.appendChild(t);
    setTimeout(() => {
        t.style.animation = 'pub-fadeOut .4s forwards';
        setTimeout(() => t.remove(), 400);
    }, 4500);
}

// ── Modal nueva noticia ───────────────────────────────────────────────────
document.getElementById('btn-nueva-noticia')?.addEventListener('click', () => {
    document.getElementById('modal-nueva').classList.add('active');
});

function cerrarModal() {
    document.getElementById('modal-nueva').classList.remove('active');
    document.getElementById('nf-titulo').value = '';
    document.getElementById('nf-archivo').value = '';
    document.getElementById('nf-archivo-nombre').textContent = 'No se ha seleccionado ningún archivo';
    document.getElementById('nf-progress-wrap').style.display = 'none';
    document.getElementById('nf-progress-bar').style.width = '0%';
    document.getElementById('nf-progress-label').style.display = 'none';
    document.getElementById('btn-publicar').disabled = false;
}

document.getElementById('nf-archivo')?.addEventListener('change', function() {
    document.getElementById('nf-archivo-nombre').textContent =
        this.files[0]?.name || 'No se ha seleccionado ningún archivo';
});

// ── Publicar ──────────────────────────────────────────────────────────────
function publicar() {
    const titulo  = document.getElementById('nf-titulo').value.trim();
    const fileEl  = document.getElementById('nf-archivo');
    const archivo = fileEl.files[0];

    if (!titulo)   { toast('El título es obligatorio.', 'err'); return; }
    if (!archivo)  { toast('Debes seleccionar un archivo.', 'err'); return; }

    const formData = new FormData();
    formData.append('titulo',   titulo);
    formData.append('seccion',  'sig');
    formData.append('archivo',  archivo);
    formData.append('_token',   CSRF);

    const btnPub  = document.getElementById('btn-publicar');
    const progW   = document.getElementById('nf-progress-wrap');
    const progB   = document.getElementById('nf-progress-bar');
    const progL   = document.getElementById('nf-progress-label');

    btnPub.disabled     = true;
    progW.style.display = 'block';
    progL.style.display = 'block';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route('publicaciones.store') }}');

    xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) {
            const pct = Math.round(e.loaded / e.total * 100);
            progB.style.width  = pct + '%';
            progL.textContent  = 'Subiendo... ' + pct + '%';
        }
    });

    xhr.addEventListener('load', () => {
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.ok) {
                toast('Noticia publicada correctamente.', 'ok');
                cerrarModal();
                insertarCard(res.pub);
            } else {
                toast(res.error || 'Error al publicar.', 'err');
                btnPub.disabled = false;
            }
        } else {
            let msg = 'Error al publicar.';
            try { msg = JSON.parse(xhr.responseText).error || msg; } catch(e) {}
            toast(msg, 'err');
            btnPub.disabled = false;
        }
    });

    xhr.addEventListener('error', () => {
        toast('Error de red. Inténtalo nuevamente.', 'err');
        btnPub.disabled = false;
    });

    xhr.send(formData);
}

// ── Insertar card en el DOM ───────────────────────────────────────────────
function insertarCard(pub) {
    const empty = document.getElementById('pub-empty-state');
    if (empty) empty.remove();

    const lista = document.getElementById('pub-lista');

    let bodyHtml = '';
    if (pub.es_inline) {
        if (pub.tipo_mime === 'application/pdf') {
            bodyHtml = `<iframe src="${pub.url_ver}" loading="lazy" title="${escHtml(pub.titulo)}"></iframe>`;
        } else {
            bodyHtml = `<img src="${pub.url_ver}" alt="${escHtml(pub.titulo)}" loading="lazy">`;
        }
    } else {
        bodyHtml = `<div class="pub-no-preview">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
            </svg>
            <p>${escHtml(pub.nombre_original)}<br><small>${escHtml(pub.tamanio)}</small></p>
            <a href="${pub.url_descargar}">Descargar archivo</a>
        </div>`;
    }

    const adminBtns = `
        <button class="btn-editar-pub" title="Editar título"
                data-id="${pub.id}" data-titulo="${escHtml(pub.titulo)}"
                onclick="abrirEditModal(this)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
        </button>
        <button class="btn-eliminar-pub" title="Eliminar"
                data-id="${pub.id}" data-titulo="${escHtml(pub.titulo)}"
                onclick="confirmarEliminar(this)">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                <path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
            </svg>
        </button>`;

    const card = document.createElement('div');
    card.className = 'pub-card';
    card.dataset.id = pub.id;
    card.innerHTML = `
        <div class="pub-card-header">
            <span class="pub-card-titulo">${escHtml(pub.titulo)}</span>
            <div class="pub-card-acciones">
                ${adminBtns}
                <a class="btn-descargar-pub" href="${pub.url_descargar}" title="Descargar">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                </a>
            </div>
        </div>
        <div class="pub-card-body">
            <div class="pub-viewer-wrap">${bodyHtml}</div>
        </div>`;

    lista.insertBefore(card, lista.firstChild);
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Modal editar título ───────────────────────────────────────────────────
let editId = null;
function abrirEditModal(btn) {
    editId = btn.dataset.id;
    document.getElementById('edit-titulo').value = btn.dataset.titulo;
    document.getElementById('modal-editar').classList.add('active');
}
function cerrarEditModal() {
    document.getElementById('modal-editar').classList.remove('active');
    editId = null;
}

function guardarTitulo() {
    const titulo = document.getElementById('edit-titulo').value.trim();
    if (!titulo) { toast('El título no puede estar vacío.', 'err'); return; }

    fetch(`/publicaciones/${editId}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ titulo })
    })
    .then(r => r.json())
    .then(res => {
        if (res.ok) {
            const card = document.querySelector(`.pub-card[data-id="${editId}"]`);
            if (card) {
                card.querySelector('.pub-card-titulo').textContent = res.titulo;
                const btnE = card.querySelector('.btn-editar-pub');
                const btnD = card.querySelector('.btn-eliminar-pub');
                if (btnE) btnE.dataset.titulo = res.titulo;
                if (btnD) btnD.dataset.titulo = res.titulo;
            }
            toast('Título actualizado.', 'ok');
            cerrarEditModal();
        } else {
            toast(res.error || 'Error al actualizar.', 'err');
        }
    })
    .catch(() => toast('Error de red.', 'err'));
}

// ── Confirmación y eliminación ────────────────────────────────────────────
let deleteId = null;
function confirmarEliminar(btn) {
    deleteId = btn.dataset.id;
    document.getElementById('confirm-titulo').textContent = '"' + btn.dataset.titulo + '"';
    document.getElementById('confirm-overlay').classList.add('active');
}
function cerrarConfirm() {
    document.getElementById('confirm-overlay').classList.remove('active');
    deleteId = null;
}

function ejecutarEliminar() {
    if (!deleteId) return;
    const id = deleteId;
    cerrarConfirm();

    fetch(`/publicaciones/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(res => {
        if (res.ok) {
            const card = document.querySelector(`.pub-card[data-id="${id}"]`);
            if (card) {
                card.style.transition = 'opacity .3s, transform .3s';
                card.style.opacity = '0';
                card.style.transform = 'translateY(-8px)';
                setTimeout(() => {
                    card.remove();
                    if (!document.querySelector('.pub-card')) {
                        document.getElementById('pub-lista').innerHTML =
                            `<div class="pub-empty" id="pub-empty-state">
                                <svg width="56" height="56" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <p>No hay noticias publicadas aún.</p>
                            </div>`;
                    }
                }, 300);
            }
            toast('Publicación eliminada.', 'ok');
        } else {
            toast(res.error || 'Error al eliminar.', 'err');
        }
    })
    .catch(() => toast('Error de red.', 'err'));
}

// ── Cerrar modales con Escape ──────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    cerrarModal();
    cerrarEditModal();
    cerrarConfirm();
});
</script>
@endpush
