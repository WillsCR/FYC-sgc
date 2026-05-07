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
                   style="border-top-color: {{ $bloque['color'] }}; text-decoration:none">
                    <div class="bloque-icon-wrap" style="background: {{ $bloque['color'] }}18">
                        <span style="font-size:1.5rem;line-height:1">{{ $bloque['emoji'] }}</span>
                    </div>
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
.modal-field { margin-bottom: 16px; }
.modal-label { display: block; font-size: .8rem; font-weight: 600; color: var(--navy); margin-bottom: 8px; }
.modal-input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 4px; font-size: .85rem; outline: none; box-sizing: border-box; }
.modal-input:focus { border-color: var(--blue-accent); }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; }
.btn-cancel { padding: 9px 18px; border-radius: 4px; border: 1px solid var(--border); background: transparent; font-size: .82rem; cursor: pointer; color: var(--text-secondary); font-weight: 500; }
.btn-cancel:hover { background: var(--surface-2); }
.btn-submit { padding: 9px 18px; border-radius: 4px; border: none; background: var(--navy); color: #fff; font-size: .82rem; cursor: pointer; font-weight: 600; }
.btn-submit:hover { background: #0a2147; }
.icon-btn {
    border: 2px solid var(--border);
    background: var(--surface-2);
    border-radius: 6px;
    padding: 10px;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.icon-btn:hover {
    border-color: var(--blue-accent);
    transform: scale(1.1);
}
.icon-btn.selected {
    border-color: var(--navy);
    background: #EFF6FF;
    transform: scale(1.15);
}
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
    <div class="modal" style="max-width:600px">
        <div class="modal-title">➕ Nuevo módulo</div>
        <form id="form-crear-modulo">
            <div class="modal-field">
                <label class="modal-label">Nombre del módulo</label>
                <input type="text" name="descripcion" id="modulo-nombre" class="modal-input" placeholder="Ej: Auditoría Interna" required>
            </div>
            <div class="modal-field">
                <label class="modal-label">Color (hexadecimal o nombre)</label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="color" id="modulo-color" class="modal-input" placeholder="#0D2B5E" style="flex:1">
                    <input type="color" id="modulo-color-picker" style="width:45px;height:38px;border:1px solid var(--border);border-radius:4px;cursor:pointer">
                </div>
            </div>
            <div class="modal-field">
                <label class="modal-label">Icono</label>
                <div style="display:grid;grid-template-columns:repeat(8,1fr);gap:8px;margin-bottom:12px;max-height:150px;overflow-y:auto;padding:8px">
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📋')">📋</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🌿')">🌿</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🛡️')">🛡️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🏗️')">🏗️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('👨‍💼')">👨‍💼</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🏢')">🏢</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📈')">📈</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('💰')">💰</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('⚠️')">⚠️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📏')">📏</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('✅')">✅</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🦺')">🦺</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📝')">📝</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📁')">📁</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🎓')">🎓</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📊')">📊</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🔍')">🔍</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('♻️')">♻️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🌱')">🌱</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🌍')">🌍</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('⚙️')">⚙️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🏥')">🏥</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('⚖️')">⚖️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('👥')">👥</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📚')">📚</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('⛏️')">⛏️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🔐')">🔐</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📱')">📱</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🎯')">🎯</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🚀')">🚀</button>
                </div>
                <input type="hidden" name="icono" id="modulo-icono" value="📋">
                <div style="font-size:.8rem;color:var(--text-muted);text-align:center">Icono seleccionado: <span id="icono-preview" style="font-size:1.2rem">📋</span></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalCrearModulo()">Cancelar</button>
                <button type="submit" class="btn-submit">Crear módulo</button>
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
    document.getElementById('form-crear-modulo').reset();
    document.getElementById('modulo-color-picker').value = '#0D2B5E';
    document.getElementById('modulo-color').value = '#0D2B5E';
    document.getElementById('modulo-icono').value = '📋';
    document.getElementById('icono-preview').textContent = '📋';
    document.querySelectorAll('#modal-crear-modulo .icon-btn').forEach((btn, i) => {
        btn.classList.toggle('selected', i === 0);
    });
    document.getElementById('modal-crear-modulo').classList.add('visible');
    setTimeout(() => document.getElementById('modulo-nombre').focus(), 100);
}

function cerrarModalCrearModulo() {
    document.getElementById('modal-crear-modulo').classList.remove('visible');
}

function seleccionarIcono(icono) {
    document.getElementById('modulo-icono').value = icono;
    document.getElementById('icono-preview').textContent = icono;
    document.querySelectorAll('#modal-crear-modulo .icon-btn').forEach(btn => {
        btn.classList.toggle('selected', btn.textContent.trim() === icono);
    });
}

// Sincronizar color pickers
document.getElementById('modulo-color').addEventListener('input', function () {
    if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
        document.getElementById('modulo-color-picker').value = this.value;
    }
});
document.getElementById('modulo-color-picker').addEventListener('input', function () {
    document.getElementById('modulo-color').value = this.value;
});

// ── Submit: crear módulo ───────────────────────────────────
document.getElementById('form-crear-modulo').addEventListener('submit', async function (e) {
    e.preventDefault();

    const nombre = document.getElementById('modulo-nombre').value.trim();
    const color  = document.getElementById('modulo-color').value.trim() || document.getElementById('modulo-color-picker').value;
    const icono  = document.getElementById('modulo-icono').value.trim();
    const btn    = this.querySelector('[type="submit"]');

    if (!nombre) { showToast('El nombre del módulo es obligatorio.', 'err'); return; }

    btn.disabled = true;
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
            btn.disabled = false;
            btn.textContent = 'Crear módulo';
            return;
        }

        if (res.ok && data.ok) {
            cerrarModalCrearModulo();
            showToast(`Módulo "${data.modulo.descripcion}" creado correctamente.`, 'ok');
            insertarTarjetaModulo(data.modulo); // separado del catch principal
        } else {
            showToast(data.error || data.message || 'No se pudo crear el módulo.', 'err');
        }
    } catch (err) {
        console.error('Error en fetch crearModulo:', err);
        showToast('Error inesperado: ' + (err.message || 'inténtalo de nuevo.'), 'err');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Crear módulo';
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

        const color = modulo.color || '#374151';
        const emoji = modulo.icono || '📁';
        const badge = modulo.descripcion.substring(0, 3).toUpperCase();
        const url   = `{{ url('/carpetas') }}/${modulo.id}`;

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
            <a href="${url}" class="bloque" style="border-top-color:${color};text-decoration:none">
                <div class="bloque-icon-wrap" style="background:${color}18">
                    <span style="font-size:1.5rem;line-height:1">${emoji}</span>
                </div>
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
</script>

@endpush
