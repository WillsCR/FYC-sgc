@extends('layouts.app')

@section('title', 'Videos')

@push('styles')
<style>
/* ── Layout ──────────────────────────────────────────────── */
.vid-page        { max-width: 1200px; margin: 0 auto; padding: 24px 20px 48px; }
.vid-header      { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
.vid-title       { font-size:1.5rem; font-weight:700; color:var(--blue-dark); display:flex; align-items:center; gap:10px; }
.vid-title-icon  { font-size:1.6rem; }

/* ── Grid ───────────────────────────────────────────────── */
.vid-grid        { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:22px; }
.vid-empty       { text-align:center; color:#888; padding:60px 20px; font-size:1rem; }

/* ── Card ───────────────────────────────────────────────── */
.vid-card        { background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.07); overflow:hidden; display:flex; flex-direction:column; }
.vid-player-wrap { background:#000; position:relative; }
.vid-player-wrap video { width:100%; display:block; max-height:220px; object-fit:contain; }
.vid-info        { padding:14px 16px; flex:1; display:flex; flex-direction:column; gap:6px; }
.vid-name        { font-weight:600; font-size:.95rem; color:#1a1a2e; line-height:1.3; }
.vid-meta        { font-size:.78rem; color:#888; }
.vid-actions     { padding:10px 16px 14px; display:flex; flex-direction:column; gap:7px; }
.vid-actions-row { display:flex; gap:7px; }
.btn-sm          { padding:5px 12px; border-radius:6px; font-size:.8rem; font-weight:500; cursor:pointer; border:none; display:inline-flex; align-items:center; gap:5px; text-decoration:none; }
.btn-dl          { background:#e8f0fe; color:#1a73e8; }
.btn-dl:hover    { background:#d2e3fc; }
.btn-del         { background:#fce8e6; color:#c62828; margin-left:auto; }
.btn-del:hover   { background:#f5c6c2; }
.btn-bienvenida         { background:#f0fdf4; color:#15803D; border:1.5px solid #bbf7d0; }
.btn-bienvenida:hover   { background:#dcfce7; }
.btn-bienvenida.activo  { background:#15803D; color:#fff; border-color:#15803D; }
.btn-bienvenida.activo:hover { background:#166534; }

/* ── Upload button ──────────────────────────────────────── */
.btn-upload      { background:var(--blue-dark,#0D2B5E); color:#fff; padding:8px 18px; border-radius:8px; font-size:.88rem; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:7px; }
.btn-upload:hover { background:#1a3d7a; }

/* ── Modal overlay ──────────────────────────────────────── */
.modal-overlay   { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box       { background:#fff; border-radius:14px; padding:28px 30px; width:100%; max-width:500px; box-shadow:0 8px 32px rgba(0,0,0,.18); position:relative; }
.modal-close     { position:absolute; top:14px; right:16px; background:none; border:none; font-size:1.4rem; cursor:pointer; color:#777; }
.modal-title     { font-size:1.1rem; font-weight:700; margin-bottom:18px; color:#1a1a2e; }
.form-label      { font-size:.85rem; font-weight:600; color:#555; margin-bottom:4px; display:block; }
.form-input      { width:100%; padding:9px 12px; border:1.5px solid #d0d5dd; border-radius:8px; font-size:.9rem; box-sizing:border-box; }
.form-input:focus { outline:none; border-color:var(--blue-dark,#0D2B5E); }
.form-group      { margin-bottom:14px; }

/* ── Progress bar ───────────────────────────────────────── */
.progress-wrap   { display:none; margin-top:10px; }
.progress-bar    { height:8px; background:#e0e0e0; border-radius:4px; overflow:hidden; }
.progress-fill   { height:100%; background:#1a73e8; width:0; transition:width .15s; border-radius:4px; }
.progress-label  { font-size:.78rem; color:#555; margin-top:4px; text-align:right; }

/* ── Submit button ──────────────────────────────────────── */
.btn-submit      { background:var(--blue-dark,#0D2B5E); color:#fff; padding:9px 20px; border-radius:8px; font-size:.9rem; font-weight:600; border:none; cursor:pointer; width:100%; margin-top:4px; }
.btn-submit:disabled { opacity:.55; cursor:not-allowed; }

/* ── Confirm modal ──────────────────────────────────────── */
.btn-danger-confirm { background:#c62828; color:#fff; padding:8px 18px; border-radius:8px; border:none; cursor:pointer; font-size:.88rem; font-weight:600; }
.btn-cancel-confirm { background:#e0e0e0; color:#333; padding:8px 18px; border-radius:8px; border:none; cursor:pointer; font-size:.88rem; font-weight:600; }

/* ── Toast ──────────────────────────────────────────────── */
.toast           { position:fixed; bottom:24px; right:24px; background:#323232; color:#fff; padding:12px 20px; border-radius:10px; font-size:.88rem; z-index:2000; opacity:0; transform:translateY(10px); transition:opacity .3s,transform .3s; pointer-events:none; max-width:340px; }
.toast.show      { opacity:1; transform:translateY(0); }
.toast.ok        { background:#2e7d32; }
.toast.err       { background:#c62828; }
</style>
@endpush

@section('content')
<div class="vid-page">

    {{-- Header --}}
    <div class="vid-header">
        <div class="vid-title">
            <span class="vid-title-icon">🎬</span>
            Videos
        </div>
        @if($esAdmin)
        <button class="btn-upload" onclick="abrirModalSubir()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
            Subir video
        </button>
        @endif
    </div>

    {{-- Grid de videos --}}
    <div class="vid-grid" id="vidGrid">
        @forelse($videos as $video)
        <div class="vid-card" id="vid-{{ $video->id }}">
            <div class="vid-player-wrap">
                <video controls preload="metadata"
                       src="{{ route('videos.stream', $video->id) }}"
                       type="{{ $video->tipo_mime }}">
                    Tu navegador no soporta la reproducción de video.
                </video>
            </div>
            <div class="vid-info">
                <div class="vid-name">{{ $video->titulo }}</div>
            </div>
            <div class="vid-actions">
                {{-- Fila 1: Descargar --}}
                <div class="vid-actions-row">
                    <a href="{{ route('videos.descargar', $video->id) }}" class="btn-sm btn-dl">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        Descargar
                    </a>
                </div>
                {{-- Fila 2: Bienvenida + Eliminar (solo admin) --}}
                @if($esAdmin)
                <div class="vid-actions-row">
                    <button class="btn-sm btn-bienvenida {{ $video->es_bienvenida ? 'activo' : '' }}"
                            id="btn-bienvenida-{{ $video->id }}"
                            onclick="toggleBienvenida({{ $video->id }})">
                        {{ $video->es_bienvenida ? '★ Bienvenida activa' : '☆ Usar en bienvenida' }}
                    </button>
                    <button class="btn-sm btn-del" onclick="pedirEliminar({{ $video->id }}, '{{ addslashes($video->titulo) }}')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                        Eliminar
                    </button>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="vid-empty" id="vidEmpty" style="grid-column:1/-1">
            <div style="font-size:2.5rem;margin-bottom:10px;">🎬</div>
            <div>No hay videos publicados aún.</div>
            @if($esAdmin)<div style="margin-top:8px;font-size:.85rem;">Usa el botón "Subir video" para agregar el primero.</div>@endif
        </div>
        @endforelse
    </div>
</div>

{{-- ── Modal: subir video ──────────────────────────────────── --}}
@if($esAdmin)
<div class="modal-overlay" id="modalSubir">
    <div class="modal-box">
        <button class="modal-close" onclick="cerrarModalSubir()">×</button>
        <div class="modal-title">Subir video</div>

        <div class="form-group">
            <label class="form-label" for="inpTitulo">Título</label>
            <input type="text" id="inpTitulo" class="form-input" placeholder="Título del video" maxlength="300">
        </div>
        <div class="form-group">
            <label class="form-label" for="inpFile">Archivo de video</label>
            <input type="file" id="inpFile" class="form-input" accept="video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo,video/x-matroska,.mp4,.webm,.ogg,.mov,.avi,.mkv">
            <div style="font-size:.75rem;color:#999;margin-top:4px;">Formatos: MP4, WebM, OGG, MOV, AVI, MKV. Máximo {{ 500 }} MB.</div>
        </div>

        <div class="progress-wrap" id="progressWrap">
            <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
            <div class="progress-label" id="progressLabel">0%</div>
        </div>

        <button class="btn-submit" id="btnSubir" onclick="subirVideo()">Subir video</button>
    </div>
</div>

{{-- Modal: confirmar eliminar --}}
<div class="modal-overlay" id="modalEliminar">
    <div class="modal-box" style="max-width:400px">
        <div class="modal-title">¿Eliminar video?</div>
        <p id="textoEliminar" style="font-size:.9rem;color:#555;margin-bottom:20px;"></p>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button class="btn-cancel-confirm" onclick="cerrarModalEliminar()">Cancelar</button>
            <button class="btn-danger-confirm" id="btnConfirmarEliminar">Eliminar</button>
        </div>
    </div>
</div>
@endif

{{-- Toast --}}
<div class="toast" id="toast"></div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Toast ──────────────────────────────────────────────────
function showToast(msg, tipo = 'ok') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `toast ${tipo} show`;
    clearTimeout(t._timer);
    t._timer = setTimeout(() => { t.classList.remove('show'); }, 4000);
}

// ── Modal subir ────────────────────────────────────────────
function abrirModalSubir() {
    document.getElementById('inpTitulo').value = '';
    document.getElementById('inpFile').value   = '';
    document.getElementById('progressWrap').style.display = 'none';
    document.getElementById('progressFill').style.width   = '0%';
    document.getElementById('progressLabel').textContent  = '0%';
    document.getElementById('btnSubir').disabled = false;
    document.getElementById('modalSubir').classList.add('open');
    document.getElementById('inpTitulo').focus();
}

function cerrarModalSubir() {
    document.getElementById('modalSubir').classList.remove('open');
}

// ── Subir video ────────────────────────────────────────────
function subirVideo() {
    const titulo = document.getElementById('inpTitulo').value.trim();
    const file   = document.getElementById('inpFile').files[0];

    if (! titulo) { showToast('El título es obligatorio.', 'err'); return; }
    if (! file)   { showToast('Selecciona un archivo de video.', 'err'); return; }

    const maxBytes = 500 * 1024 * 1024;
    if (file.size > maxBytes) { showToast('El archivo supera el límite de 500 MB.', 'err'); return; }

    const fd = new FormData();
    fd.append('titulo', titulo);
    fd.append('video', file);
    fd.append('_token', CSRF);

    document.getElementById('btnSubir').disabled = true;
    document.getElementById('progressWrap').style.display = 'block';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route("videos.store") }}');

    xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 100);
            document.getElementById('progressFill').style.width  = pct + '%';
            document.getElementById('progressLabel').textContent = pct + '%';
        }
    });

    xhr.addEventListener('load', () => {
        document.getElementById('btnSubir').disabled = false;
        document.getElementById('progressWrap').style.display = 'none';

        // 413: PHP rechazó el archivo por límites de php.ini
        if (xhr.status === 413) {
            showToast(
                'El servidor rechazó el archivo. El límite de subida de PHP es demasiado pequeño. ' +
                'Ejecuta: php artisan sgc:setup',
                'err'
            );
            return;
        }

        // Respuesta vacía: PHP puede haber cortado la conexión silenciosamente
        if (! xhr.responseText || xhr.responseText.trim() === '') {
            showToast(
                'El servidor no respondió. Probablemente el archivo supera el límite de PHP (upload_max_filesize). ' +
                'Ejecuta: php artisan sgc:setup',
                'err'
            );
            return;
        }

        try {
            const res = JSON.parse(xhr.responseText);
            if (xhr.status === 200 && res.ok) {
                cerrarModalSubir();
                showToast('Video subido correctamente.', 'ok');
                insertarCard(res.video);
            } else if (xhr.status === 422 && res.errors) {
                // Error de validación de Laravel — mostrar el primero
                const primerError = Object.values(res.errors)[0][0];
                showToast(primerError, 'err');
            } else {
                showToast(res.error || res.message || 'Error al subir el video.', 'err');
            }
        } catch {
            showToast('Respuesta inesperada del servidor. Intenta nuevamente.', 'err');
        }
    });

    xhr.addEventListener('error', () => {
        document.getElementById('btnSubir').disabled = false;
        document.getElementById('progressWrap').style.display = 'none';
        showToast('Error de red al subir el video. Verifica tu conexión.', 'err');
    });

    xhr.addEventListener('abort', () => {
        document.getElementById('btnSubir').disabled = false;
        document.getElementById('progressWrap').style.display = 'none';
        showToast('La subida fue cancelada.', 'err');
    });

    xhr.send(fd);
}

// ── Insertar card dinámicamente ────────────────────────────
function insertarCard(v) {
    const empty = document.getElementById('vidEmpty');
    if (empty) empty.remove();

    const streamUrl    = `{{ url('/videos') }}/${v.id}/stream`;
    const descargarUrl = `{{ url('/videos') }}/${v.id}/descargar`;

    const card = document.createElement('div');
    card.className = 'vid-card';
    card.id        = `vid-${v.id}`;
    card.innerHTML = `
        <div class="vid-player-wrap">
            <video controls preload="metadata" src="${streamUrl}">
                Tu navegador no soporta la reproducción de video.
            </video>
        </div>
        <div class="vid-info">
            <div class="vid-name">${escHtml(v.titulo)}</div>
        </div>
        <div class="vid-actions">
            <div class="vid-actions-row">
                <a href="${descargarUrl}" class="btn-sm btn-dl">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    Descargar
                </a>
            </div>
            <div class="vid-actions-row">
                <button class="btn-sm btn-del" onclick="pedirEliminar(${v.id}, '${escJs(v.titulo)}')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                    Eliminar
                </button>
            </div>
        </div>`;

    document.getElementById('vidGrid').prepend(card);
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escJs(s) { return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }

// ── Eliminar video ─────────────────────────────────────────
let _eliminarId = null;

function pedirEliminar(id, titulo) {
    _eliminarId = id;
    document.getElementById('textoEliminar').textContent = `¿Seguro que quieres eliminar "${titulo}"? Esta acción no se puede deshacer.`;
    document.getElementById('modalEliminar').classList.add('open');
}

function cerrarModalEliminar() {
    _eliminarId = null;
    document.getElementById('modalEliminar').classList.remove('open');
}

document.getElementById('btnConfirmarEliminar')?.addEventListener('click', async () => {
    if (! _eliminarId) return;

    const id  = _eliminarId;
    const btn = document.getElementById('btnConfirmarEliminar');
    btn.disabled = true;

    try {
        const res = await fetch(`{{ url('/videos') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (res.ok && data.ok) {
            cerrarModalEliminar();
            document.getElementById(`vid-${id}`)?.remove();
            showToast('Video eliminado correctamente.', 'ok');

            // Mostrar empty si no quedan cards
            if (! document.querySelector('.vid-card')) {
                const grid = document.getElementById('vidGrid');
                grid.innerHTML = `<div class="vid-empty" id="vidEmpty" style="grid-column:1/-1">
                    <div style="font-size:2.5rem;margin-bottom:10px;">🎬</div>
                    <div>No hay videos publicados aún.</div>
                    <div style="margin-top:8px;font-size:.85rem;">Usa el botón "Subir video" para agregar el primero.</div>
                </div>`;
            }
        } else {
            showToast(data.error || 'No se pudo eliminar el video.', 'err');
        }
    } catch {
        showToast('Error de red al eliminar.', 'err');
    } finally {
        btn.disabled = false;
    }
});

// ── Toggle video de bienvenida ─────────────────────────────
async function toggleBienvenida(id) {
    const btn = document.getElementById(`btn-bienvenida-${id}`);
    if (!btn) return;
    btn.disabled = true;

    try {
        const base = "{{ url('/videos') }}";
        const res  = await fetch(`${base}/${id}/bienvenida`, {
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (res.ok && data.ok) {
            // Si se activó este, desactivar todos los demás primero
            if (data.activo) {
                document.querySelectorAll('.btn-bienvenida').forEach(b => {
                    b.classList.remove('activo');
                    b.textContent = '☆ Usar en bienvenida';
                });
                btn.classList.add('activo');
                btn.textContent = '★ Bienvenida activa';
            } else {
                btn.classList.remove('activo');
                btn.textContent = '☆ Usar en bienvenida';
            }
            showToast(data.mensaje, 'ok');
        } else {
            showToast(data.error || 'Error al actualizar.', 'err');
        }
    } catch {
        showToast('Error de red.', 'err');
    } finally {
        btn.disabled = false;
    }
}

// ── Cerrar modales con Escape ──────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        cerrarModalSubir();
        cerrarModalEliminar();
    }
});

// ── Cerrar al click fuera del box ──────────────────────────
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) {
            cerrarModalSubir();
            cerrarModalEliminar();
        }
    });
});
</script>
@endpush
