@extends('layouts.app')
@section('title', 'Panel principal')

@section('content')
<div class="panel-body">

    @if(session('sin_permiso_carpeta'))
    <div style="background:#FEF2F2;border-left:4px solid #DC2626;color:#991B1B;
                padding:11px 16px;border-radius:4px;margin-bottom:16px;font-size:.84rem;display:flex;align-items:center;gap:8px">
        🔒 {{ session('sin_permiso_carpeta') }}
    </div>
    @endif

    <div class="panel-welcome">
        <h2>Bienvenido, {{ session('usuario_nombre') }}</h2>
        <p>{{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}
            @if(session('es_superadmin'))
                · <span style="color:var(--blue-accent);font-weight:500">Super Administrador</span>
            @elseif(session('es_admin'))
                · <span style="color:var(--blue-accent);font-weight:500">Administrador</span>
            @endif
        </p>
    </div>

    {{-- Bloques --}}
    @if(count($bloques) > 0)
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:10px">
            <div class="section-label" style="margin-bottom:0">Módulos del sistema</div>
            @if(session('es_superadmin'))
            <button onclick="abrirModalCrearModulo()"
                    style="padding:7px 14px;background:var(--navy);color:#fff;border:none;border-radius:6px;
                           font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;
                           white-space:nowrap;flex-shrink:0">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                Nuevo módulo
            </button>
            @endif
        </div>
        <div class="bloques-grid">
            @foreach($bloques as $bloque)
            <div class="bloque-wrap" data-modulo-id="{{ $bloque['carpeta_id'] }}">
                <a href="{{ route('carpetas.show', $bloque['carpeta_id']) }}"
                   class="bloque"
                   style="background: {{ $bloque['color'] }}; border-color: {{ $bloque['color'] }}; text-decoration:none">
                    @php $icono = $bloque['emoji'] ?? ''; @endphp
                    @if($icono && str_starts_with($icono, 'fa-'))
                        <i class="fa-solid {{ $icono }} bloque-icon"></i>
                    @elseif($icono)
                        <span class="bloque-icon" style="font-size:1.8rem">{{ $icono }}</span>
                    @endif
                    <div class="bloque-title">{{ $bloque['titulo'] }}</div>
                    <div class="bloque-badge">{{ $bloque['badge'] }}</div>
                </a>
                @if(session('es_superadmin'))
                <button class="btn-del-modulo"
                        title="Eliminar módulo"
                        onclick="pedirEliminarModulo({{ $bloque['carpeta_id'] }}, '{{ addslashes($bloque['titulo']) }}')">
                    ✕
                </button>
                @endif
            </div>
            @endforeach
        </div>
    @else
        <div style="padding:40px 0;text-align:center">
            <div style="font-size:2.5rem;margin-bottom:12px">🔒</div>
            <p style="color:var(--text-muted);font-size:.9rem">No tienes módulos asignados. Contacta al administrador.</p>
        </div>
    @endif

    {{-- Resumen --}}
    <div class="section-label">Resumen del sistema</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value {{ $stats['cumplimiento'] >= 80 ? 'success' : ($stats['cumplimiento'] >= 50 ? 'warning' : 'danger') }}">
                {{ $stats['cumplimiento'] }}%
            </div>
            <div class="stat-label">Cumplimiento global</div>
        </div>
        <div class="stat-card">
            <div class="stat-value {{ $stats['pendientes'] > 5 ? 'danger' : ($stats['pendientes'] > 0 ? 'warning' : 'success') }}">
                {{ $stats['pendientes'] }}
            </div>
            <div class="stat-label">Actividades pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value success">{{ $stats['cerradas'] }}</div>
            <div class="stat-label">Actividades cerradas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['minutas_mes'] }}</div>
            <div class="stat-label">Minutas este mes</div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<style>
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200; align-items: center; justify-content: center; }
.modal-overlay.visible { display: flex; }
.modal { background: var(--surface); border-radius: 8px; padding: 28px; width: 100%; max-width: 500px; box-shadow: 0 24px 64px rgba(0,0,0,.2); }
.modal-title { font-size: 1rem; font-weight: 700; color: var(--navy); margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; }
.btn-cancel { padding: 9px 18px; border-radius: 4px; border: 1px solid var(--border); background: transparent; font-size: .82rem; cursor: pointer; color: var(--text-secondary); font-weight: 500; }
.btn-cancel:hover { background: var(--surface-2); }
.btn-submit { padding: 9px 18px; border-radius: 4px; border: none; background: var(--navy); color: #fff; font-size: .82rem; cursor: pointer; font-weight: 600; }
.btn-submit:hover { background: #0a2147; }
.btn-submit:disabled { opacity: .6; cursor: not-allowed; }

/* Botón eliminar módulo */
.btn-del-modulo {
    position: absolute;
    top: 6px; right: 6px;
    width: 20px; height: 20px;
    border-radius: 50%;
    border: none;
    background: rgba(220,38,38,.9);
    color: #fff;
    font-size: .6rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .15s;
    z-index: 2;
    line-height: 1;
    padding: 0;
}
.bloque-wrap:hover .btn-del-modulo { opacity: 1; }

/* Modal confirmar eliminar módulo */
.btn-danger-mod { background:#c62828;color:#fff;padding:9px 20px;border-radius:6px;border:none;cursor:pointer;font-size:.88rem;font-weight:600; }
.btn-cancel-mod { background:#e0e0e0;color:#333;padding:9px 20px;border-radius:6px;border:none;cursor:pointer;font-size:.88rem;font-weight:600; }

/* ── Estilos compartidos con modal nuevo submódulo ───────── */
.nsub-field { margin-bottom: 14px; }
.nsub-label { display: block; font-size: .8rem; font-weight: 600; color: var(--navy); margin-bottom: 6px; }
.nsub-input {
    width: 100%; padding: 9px 12px;
    border: 1px solid var(--border); border-radius: 4px;
    font-size: .85rem; outline: none; box-sizing: border-box;
    color: var(--text-primary); background: var(--surface);
    transition: border-color .15s;
}
.nsub-input:focus { border-color: var(--blue-accent); }
.nsub-color-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.nsub-color-presets { display: flex; gap: 6px; flex-wrap: wrap; }
.nsub-color-preset {
    width: 22px; height: 22px; border-radius: 4px;
    border: 2px solid transparent; cursor: pointer; display: inline-block;
    transition: transform .12s, border-color .12s;
}
.nsub-color-preset:hover { transform: scale(1.2); border-color: rgba(0,0,0,.25); }
.nsub-emoji-grid {
    display: flex; flex-wrap: wrap; gap: 6px;
    max-height: 150px; overflow-y: auto;
    padding: 8px; background: var(--surface-2);
    border: 1px solid var(--border); border-radius: 6px;
}
.nsub-emoji-opt {
    width: 34px; height: 34px; font-size: 1.1rem;
    display: flex; align-items: center; justify-content: center;
    border-radius: 6px; cursor: pointer;
    border: 2px solid transparent; transition: all .12s;
    user-select: none;
}
.nsub-emoji-opt:hover  { background: #fff; border-color: var(--border); }
.nsub-emoji-opt.selected { background: #fff; border-color: var(--blue-accent); box-shadow: 0 0 0 2px rgba(29,111,217,.18); }
</style>

{{-- Modal: Confirmar eliminar módulo --}}
@if(session('es_superadmin'))
<div class="modal-overlay" id="modal-eliminar-modulo">
    <div class="modal" style="max-width:420px">
        <div class="modal-title" style="color:#c62828">🗑️ Eliminar módulo</div>
        <p id="texto-eliminar-modulo" style="font-size:.88rem;color:#555;margin-bottom:8px;line-height:1.5"></p>
        <p style="font-size:.8rem;color:#888;margin-bottom:20px">
            Solo se permite eliminar módulos que no tengan submódulos ni documentos asociados.
        </p>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button class="btn-cancel-mod" onclick="cerrarModalEliminarModulo()">Cancelar</button>
            <button class="btn-danger-mod" id="btn-confirmar-eliminar-modulo">Eliminar</button>
        </div>
    </div>
</div>
@endif

{{-- Modal: Crear módulo --}}
<div class="modal-overlay" id="modal-crear-modulo">
    <div class="modal" style="max-width:480px">
        <div class="modal-title">➕ Nuevo módulo</div>
        <form id="form-crear-modulo">

            <div class="nsub-field">
                <label class="nsub-label" for="modulo-nombre">
                    Nombre <span style="color:#DC2626">*</span>
                </label>
                <input type="text" name="descripcion" id="modulo-nombre" class="nsub-input"
                       placeholder="Ej: Auditoría Interna" maxlength="200" required
                       onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('form-crear-modulo').requestSubmit();}">
            </div>

            <div class="nsub-field">
                <label class="nsub-label">Color</label>
                <div class="nsub-color-row">
                    <input type="color" id="modulo-color-picker" value="#0D2B5E"
                           oninput="document.getElementById('modulo-color').value=this.value"
                           style="width:36px;height:36px;border:2px solid var(--border);border-radius:4px;padding:0;background:none;cursor:pointer;flex-shrink:0">
                    <input type="text" name="color" id="modulo-color" class="nsub-input" value="#0D2B5E"
                           maxlength="7" placeholder="#000000"
                           oninput="sincronizarColorMod(this.value)"
                           style="width:100px;font-family:monospace">
                    <div class="nsub-color-presets">
                        @foreach(['#0D2B5E','#15803D','#991B1B','#B45309','#7C3AED','#0C4A6E','#1D4ED8','#065F46','#374151','#0891B2','#BE185D','#9A3412'] as $c)
                        <span class="nsub-color-preset" title="{{ $c }}"
                              style="background:{{ $c }}"
                              onclick="elegirColorPresetMod('{{ $c }}')"></span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="nsub-field">
                <label class="nsub-label">Ícono</label>
                <div class="nsub-emoji-grid" id="mod-emoji-grid">
                    @foreach([
                        'fa-clipboard-check','fa-seedling','fa-shield-halved','fa-truck','fa-user-tie',
                        'fa-building','fa-chart-line','fa-coins','fa-folder-open','fa-file-lines',
                        'fa-certificate','fa-graduation-cap','fa-book-open','fa-chart-bar','fa-magnifying-glass',
                        'fa-triangle-exclamation','fa-gear','fa-gears','fa-wrench','fa-hammer',
                        'fa-users','fa-hospital','fa-scale-balanced','fa-leaf','fa-earth-americas',
                        'fa-recycle','fa-ruler','fa-vest','fa-hard-hat','fa-boxes-stacked',
                        'fa-calculator','fa-briefcase','fa-key','fa-bell','fa-calendar-days',
                        'fa-flag','fa-star','fa-lock','fa-chart-pie','fa-chalkboard-user',
                    ] as $fa)
                    <div class="nsub-emoji-opt{{ $fa === 'fa-clipboard-check' ? ' selected' : '' }}"
                         data-emoji="{{ $fa }}"
                         title="{{ $fa }}"
                         onclick="elegirEmojiMod(this)">
                        <i class="fa-solid {{ $fa }}"></i>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="icono" id="modulo-icono" value="fa-clipboard-check">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalCrearModulo()">Cancelar</button>
                <button type="submit" class="btn-submit" id="mod-btn-crear">➕ Crear módulo</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Crear submódulo --}}
<div class="modal-overlay" id="modal-crear-submodulo">
    <div class="modal">
        <div class="modal-title">➕ Nuevo submódulo</div>
        <form id="form-crear-submodulo">
            <input type="hidden" name="modulo_id" id="submodulo-modulo-id">
            <div class="modal-field">
                <label class="modal-label">Nombre del submódulo</label>
                <input type="text" name="descripcion" id="submodulo-nombre" class="modal-input" placeholder="Ej: Auditorías 2025" required>
            </div>
            <div class="modal-field">
                <label class="modal-label">Color (hexadecimal o nombre)</label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="color" id="submodulo-color" class="modal-input" placeholder="#15803D" style="flex:1">
                    <input type="color" id="submodulo-color-picker" style="width:45px;height:38px;border:1px solid var(--border);border-radius:4px;cursor:pointer">
                </div>
            </div>
            <div class="modal-field">
                <label class="modal-label">Icono (emoji o clase)</label>
                <input type="text" name="icono" id="submodulo-icono" class="modal-input" placeholder="🔍 o fa-search">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalCrearSubmodulo()">Cancelar</button>
                <button type="submit" class="btn-submit">Crear submódulo</button>
            </div>
        </form>
    </div>
</div>

<style>
.toast-panel { position:fixed; bottom:24px; right:24px; padding:13px 20px; border-radius:10px; font-size:.88rem; font-weight:500; color:#fff; z-index:9999; opacity:0; transform:translateY(10px); transition:opacity .3s,transform .3s; pointer-events:none; max-width:340px; }
.toast-panel.show { opacity:1; transform:translateY(0); }
.toast-panel.ok  { background:#2e7d32; }
.toast-panel.err { background:#c62828; }
</style>
<div class="toast-panel" id="toastPanel"></div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// ── Toast ──────────────────────────────────────────────────
function showToast(msg, tipo = 'ok') {
    const t = document.getElementById('toastPanel');
    t.textContent = msg;
    t.className = `toast-panel ${tipo} show`;
    clearTimeout(t._t);
    t._t = setTimeout(() => t.classList.remove('show'), 4000);
}

// ── Modal crear módulo ─────────────────────────────────────
function abrirModalCrearModulo() {
    document.getElementById('modulo-nombre').value       = '';
    document.getElementById('modulo-color-picker').value = '#0D2B5E';
    document.getElementById('modulo-color').value        = '#0D2B5E';
    document.getElementById('modulo-icono').value        = 'fa-clipboard-check';
    document.querySelectorAll('#mod-emoji-grid .nsub-emoji-opt').forEach(function(el) {
        el.classList.toggle('selected', el.dataset.emoji === 'fa-clipboard-check');
    });
    var btn = document.getElementById('mod-btn-crear');
    btn.disabled    = false;
    btn.textContent = '➕ Crear módulo';
    document.getElementById('modal-crear-modulo').classList.add('visible');
    setTimeout(function() { document.getElementById('modulo-nombre').focus(); }, 100);
}

function cerrarModalCrearModulo() {
    document.getElementById('modal-crear-modulo').classList.remove('visible');
}

function elegirColorPresetMod(hex) {
    document.getElementById('modulo-color-picker').value = hex;
    document.getElementById('modulo-color').value        = hex;
}

function sincronizarColorMod(val) {
    if (/^#[0-9a-fA-F]{6}$/.test(val)) {
        document.getElementById('modulo-color-picker').value = val;
    }
}

function elegirEmojiMod(el) {
    document.querySelectorAll('#mod-emoji-grid .nsub-emoji-opt').forEach(function(o) {
        o.classList.remove('selected');
    });
    el.classList.add('selected');
    document.getElementById('modulo-icono').value = el.dataset.emoji;
}

// ── Submit: crear módulo ───────────────────────────────────
document.getElementById('form-crear-modulo').addEventListener('submit', async function (e) {
    e.preventDefault();

    const nombre = document.getElementById('modulo-nombre').value.trim();
    const color  = document.getElementById('modulo-color').value.trim() || document.getElementById('modulo-color-picker').value;
    const icono  = document.getElementById('modulo-icono').value.trim();
    const btn    = this.querySelector('[type="submit"]');

    if (!nombre) { showToast('El nombre del módulo es obligatorio.', 'err'); return; }

    btn.disabled    = true;
    btn.textContent = 'Creando...';

    try {
        const res  = await fetch('{{ route("panel.crear.modulo") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ descripcion: nombre, color, icono }),
        });

        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch {
            console.error('Respuesta no-JSON del servidor:', text);
            showToast('El servidor devolvió una respuesta inesperada. Revisa la consola.', 'err');
            return;
        }

        if (res.ok && data.ok) {
            cerrarModalCrearModulo();
            showToast(`Módulo "${data.modulo.descripcion}" creado correctamente.`, 'ok');
            insertarTarjetaModulo(data.modulo);
        } else {
            showToast(data.error || data.message || 'No se pudo crear el módulo.', 'err');
        }
    } catch (err) {
        console.error('Error en fetch crearModulo:', err);
        showToast('Error inesperado: ' + (err.message || 'inténtalo de nuevo.'), 'err');
    } finally {
        btn.disabled    = false;
        btn.textContent = '➕ Crear módulo';
    }
});

// ── Insertar tarjeta dinámica en el grid ───────────────────
function insertarTarjetaModulo(modulo) {
    try {
        let grid = document.querySelector('.bloques-grid');

        // Si no existe el grid aún (panel vacío), crearlo
        if (!grid) {
            const container = document.querySelector('.panel-body') || document.querySelector('main');
            const wrapper   = document.createElement('div');
            wrapper.className = 'bloques-grid';
            // Insertar antes del resumen
            const resumen = document.querySelector('.section-label');
            if (resumen) container.insertBefore(wrapper, resumen);
            else container.appendChild(wrapper);
            grid = wrapper;
        }

        const color    = modulo.color || '#374151';
        const icono    = modulo.icono || 'fa-folder-open';
        const badge    = modulo.descripcion.substring(0, 3).toUpperCase();
        const url      = `{{ url('/carpetas') }}/${modulo.id}`;
        const iconoHtml = icono && icono.startsWith('fa-')
            ? `<i class="fa-solid ${escHtml(icono)} bloque-icon"></i>`
            : `<span class="bloque-icon" style="font-size:1.8rem">${escHtml(icono)}</span>`;

        @if(session('es_superadmin'))
        const btnElim = `<button class="btn-del-modulo" title="Eliminar módulo"
            onclick="pedirEliminarModulo(${modulo.id}, '${escJs(modulo.descripcion)}')">✕</button>`;
        @else
        const btnElim = '';
        @endif

        const wrap = document.createElement('div');
        wrap.className     = 'bloque-wrap';
        wrap.dataset.moduloId = modulo.id;
        wrap.innerHTML = `
            <a href="${url}" class="bloque" style="background:${color};border-color:${color};text-decoration:none">
                ${iconoHtml}
                <div class="bloque-title">${escHtml(modulo.descripcion)}</div>
                <div class="bloque-badge">${badge}</div>
            </a>
            ${btnElim}`;

        grid.appendChild(wrap);
    } catch (e) {
        console.warn('insertarTarjetaModulo error:', e);
        // No propagar — el módulo ya fue creado en el servidor
    }
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escJs(s) {
    return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
}

// ── Eliminar módulo ────────────────────────────────────────
let _eliminarModuloId = null;

function pedirEliminarModulo(id, titulo) {
    _eliminarModuloId = id;
    document.getElementById('texto-eliminar-modulo').textContent =
        `¿Estás seguro de que deseas eliminar el módulo "${titulo}"? Esta acción no se puede deshacer.`;
    document.getElementById('modal-eliminar-modulo').classList.add('visible');
}

function cerrarModalEliminarModulo() {
    _eliminarModuloId = null;
    document.getElementById('modal-eliminar-modulo').classList.remove('visible');
}

document.getElementById('btn-confirmar-eliminar-modulo')?.addEventListener('click', async () => {
    if (! _eliminarModuloId) return;

    const id  = _eliminarModuloId;
    const btn = document.getElementById('btn-confirmar-eliminar-modulo');
    btn.disabled = true;
    btn.textContent = 'Eliminando...';

    try {
        const res  = await fetch(`{{ url('/panel/modulo') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch {
            console.error('Respuesta no-JSON:', text);
            cerrarModalEliminarModulo();
            showToast('Respuesta inesperada del servidor.', 'err');
            return;
        }

        if (res.ok && data.ok) {
            cerrarModalEliminarModulo();
            showToast(data.mensaje, 'ok');
            document.querySelector(`.bloque-wrap[data-modulo-id="${id}"]`)?.remove();
        } else {
            cerrarModalEliminarModulo();
            showToast(data.error || 'No se pudo eliminar el módulo.', 'err');
        }
    } catch (err) {
        console.error('Error en fetch eliminarModulo:', err);
        cerrarModalEliminarModulo();
        showToast('Error de red. Inténtalo de nuevo.', 'err');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Eliminar';
    }
});

// ── Cerrar modales con Escape ──────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        cerrarModalCrearModulo();
        cerrarModalEliminarModulo();
        cerrarBienvenida();
    }
});
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => {
        if (e.target === o) {
            cerrarModalCrearModulo();
            cerrarModalEliminarModulo();
        }
    });
});

// ── Bienvenida ─────────────────────────────────────────────
function activarSonido() {
    const vid = document.getElementById('video-bienvenida');
    const btn = document.getElementById('btn-activar-sonido');
    if (!vid) return;
    vid.muted = false;
    vid.volume = 1;
    if (btn) btn.classList.add('oculto');
}

function cerrarBienvenida() {
    const m = document.getElementById('modal-bienvenida');
    if (!m) return;
    // Pausar el video si está reproduciendo
    const v = m.querySelector('video');
    if (v) { v.pause(); }
    m.style.opacity = '0';
    setTimeout(() => m.style.display = 'none', 280);
}
@if(session('bienvenida'))
window.addEventListener('DOMContentLoaded', () => {
    const m = document.getElementById('modal-bienvenida');
    if (!m) return;
    m.style.display = 'flex';
    requestAnimationFrame(() => m.style.opacity = '1');

    @if($videoBienvenida)
    // Intentar reproducir automáticamente
    const vid = document.getElementById('video-bienvenida');
    if (vid) {
        vid.play().then(() => {
            // Autoplay ok (muted) — mostrar botón para activar sonido
            document.getElementById('btn-activar-sonido')?.classList.remove('oculto');
        }).catch(() => {
            // Si falla autoplay, el usuario reproducirá manualmente
        });
    }
    @else
    // Sin video: cierre automático a los 6 segundos
    setTimeout(cerrarBienvenida, 6000);
    @endif
});
@endif
</script>

{{-- ── Modal bienvenida ──────────────────────────────── --}}
@if(session('bienvenida'))
<style>
#modal-bienvenida {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 600;
    align-items: center;
    justify-content: center;
    padding: 16px;
    opacity: 0;
    transition: opacity .28s ease;
}
.bienvenida-card {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: {{ $videoBienvenida ? '600px' : '420px' }};
    max-height: 92vh;
    overflow-y: auto;
    box-shadow: 0 28px 70px rgba(0,0,0,.3);
    animation: bienvenida-in .35s cubic-bezier(.22,1,.36,1);
    position: relative;
}
@keyframes bienvenida-in {
    from { transform: translateY(24px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.bienvenida-close {
    position: absolute; top: 10px; right: 12px;
    background: rgba(255,255,255,.2); border: none; color: #fff;
    width: 30px; height: 30px; border-radius: 50%;
    font-size: 1.1rem; cursor: pointer; z-index: 2;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s;
}
.bienvenida-close:hover { background: rgba(255,255,255,.35); }
.bienvenida-header {
    background: var(--navy);
    padding: 24px 28px 20px;
    text-align: center;
    position: relative;
}
.bienvenida-video-wrap {
    background: #000;
    line-height: 0;
    position: relative;
}
.bienvenida-video-wrap video {
    width: 100%; max-height: 300px; object-fit: contain; display: block;
}
.btn-sonido {
    position: absolute; bottom: 44px; right: 10px;
    background: rgba(0,0,0,.65); color: #fff;
    border: 1px solid rgba(255,255,255,.4);
    border-radius: 20px; padding: 5px 12px;
    font-size: .75rem; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; gap: 5px;
    transition: background .15s;
}
.btn-sonido:hover { background: rgba(0,0,0,.85); }
.btn-sonido.oculto { display: none; }
.bienvenida-body {
    padding: 20px 28px 24px;
    text-align: center;
}
.bienvenida-avatar {
    width: 54px; height: 54px; border-radius: 50%;
    background: rgba(255,255,255,.18);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.6rem; margin-bottom: 10px;
    border: 2px solid rgba(255,255,255,.3);
}
.bienvenida-saludo { font-size: .72rem; color: rgba(255,255,255,.7); font-weight: 600; letter-spacing: .06em; text-transform: uppercase; margin-bottom: 3px; }
.bienvenida-nombre { font-size: 1.25rem; font-weight: 800; color: #fff; }
.bienvenida-msg    { font-size: .86rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 16px; }
.bienvenida-fecha  { display: inline-block; background: var(--surface-2); border-radius: 6px; padding: 5px 12px; font-size: .73rem; color: var(--text-muted); font-weight: 500; margin-bottom: 18px; }
.bienvenida-btn    { width: 100%; padding: 11px; background: var(--navy); color: #fff; border: none; border-radius: 8px; font-size: .88rem; font-weight: 700; cursor: pointer; transition: background .15s; }
.bienvenida-btn:hover { background: #0a2147; }
</style>
<div id="modal-bienvenida" onclick="if(event.target===this)cerrarBienvenida()">
    <div class="bienvenida-card">
        {{-- Header --}}
        <div class="bienvenida-header">
            <button class="bienvenida-close" onclick="cerrarBienvenida()" title="Cerrar">✕</button>
            <div class="bienvenida-avatar">👋</div>
            <div class="bienvenida-saludo">Bienvenido/a de vuelta</div>
            <div class="bienvenida-nombre">{{ session('bienvenida') }}</div>
        </div>

        {{-- Video de bienvenida (si hay uno activo) --}}
        @if($videoBienvenida)
        <div class="bienvenida-video-wrap">
            <video id="video-bienvenida" controls autoplay muted preload="auto"
                   src="{{ route('videos.stream', $videoBienvenida->id) }}"
                   type="{{ $videoBienvenida->tipo_mime }}">
                Tu navegador no soporta la reproducción de video.
            </video>
            <button id="btn-activar-sonido" class="btn-sonido oculto"
                    onclick="activarSonido()">
                🔇 Toca para activar sonido
            </button>
        </div>
        @endif

        {{-- Cuerpo --}}
        <div class="bienvenida-body">
            <p class="bienvenida-msg">
                Nos alegra tenerte aquí. Tienes acceso al sistema
                <strong>SIG F&amp;C Chile SpA</strong>.
                @if(!$videoBienvenida) <br>¿Listo para comenzar? @endif
            </p>
            <div class="bienvenida-fecha">
                📅 {{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}
            </div>
            <button class="bienvenida-btn" onclick="cerrarBienvenida()">
                Comenzar →
            </button>
        </div>
    </div>
</div>
@endif

@endpush
