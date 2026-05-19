@extends('layouts.app')

@section('title', 'Gestión de Áreas')

@push('styles')
<style>
/* ── Encabezado de página ───────────────────────────────────────────────────── */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}
.page-header h1 {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--navy);
    margin: 0;
}
.page-header p {
    margin: 4px 0 0;
    font-size: .875rem;
    color: var(--text-muted);
}

/* ── Tarjeta principal ──────────────────────────────────────────────────────── */
.card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,.08);
    padding: 28px 28px 32px;
    max-width: 780px;
    margin: 0 auto;
}

/* ── Tabla de áreas ─────────────────────────────────────────────────────────── */
.areas-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}
.areas-table th {
    font-size: .75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--text-muted);
    padding: 8px 12px;
    text-align: left;
    border-bottom: 2px solid #e5e7eb;
}
.areas-table td {
    padding: 12px 12px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    font-size: .9rem;
}
.areas-table tbody tr:last-child td { border-bottom: none; }
.areas-table tbody tr:hover td { background: #f9fafb; }

.area-nombre { font-weight: 500; color: #111; }
.badge-usuarios {
    display: inline-block;
    background: #eff6ff;
    color: #1d4ed8;
    border-radius: 20px;
    padding: 2px 10px;
    font-size: .75rem;
    font-weight: 600;
}
.badge-usuarios.cero { background: #f3f4f6; color: #9ca3af; }

.btn-icon {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px 7px;
    border-radius: 6px;
    color: var(--text-muted);
    font-size: .95rem;
    transition: background .15s, color .15s;
}
.btn-icon:hover { background: #f3f4f6; color: #111; }
.btn-icon.danger:hover { background: #fee2e2; color: #dc2626; }

/* ── Formulario de nueva área ───────────────────────────────────────────────── */
.add-area-form {
    display: flex;
    gap: 10px;
    margin-bottom: 24px;
    align-items: flex-start;
}
.add-area-form input[type=text] {
    flex: 1;
    padding: 9px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: .9rem;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.add-area-form input[type=text]:focus {
    border-color: var(--navy);
    box-shadow: 0 0 0 3px rgba(13,43,94,.1);
}
.btn-primary {
    background: var(--navy);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 9px 18px;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: opacity .15s;
}
.btn-primary:hover { opacity: .85; }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; }

/* ── Modal edición ──────────────────────────────────────────────────────────── */
.modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 999;
    align-items: center;
    justify-content: center;
}
.modal-overlay.visible { display: flex; }
.modal-box {
    background: #fff;
    border-radius: 12px;
    padding: 28px 28px 24px;
    width: 400px;
    max-width: 95vw;
    box-shadow: 0 8px 32px rgba(0,0,0,.18);
}
.modal-box h3 { margin: 0 0 16px; font-size: 1.05rem; color: var(--navy); }
.modal-box label { display: block; font-size: .8rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
.modal-box input[type=text] {
    width: 100%; box-sizing: border-box;
    padding: 9px 12px; border: 1px solid #d1d5db;
    border-radius: 8px; font-size: .9rem; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.modal-box input[type=text]:focus {
    border-color: var(--navy);
    box-shadow: 0 0 0 3px rgba(13,43,94,.1);
}
.modal-actions {
    display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;
}
.btn-secondary {
    background: #f3f4f6; color: #374151;
    border: none; border-radius: 8px;
    padding: 8px 16px; font-size: .875rem;
    font-weight: 500; cursor: pointer;
}
.btn-secondary:hover { background: #e5e7eb; }

/* ── Alertas ────────────────────────────────────────────────────────────────── */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: .875rem;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.alert-ok  { background: #dcfce7; color: #15803d; }
.alert-err { background: #fee2e2; color: #dc2626; }
.alert-hidden { display: none; }

/* ── Spinner ────────────────────────────────────────────────────────────────── */
.row-spinner { display: none; }
</style>
@endpush

@section('content')
<div class="container" style="padding: 32px 24px">

    {{-- ── Encabezado ──────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h1>🏢 Gestión de Áreas</h1>
            <p>Administra las áreas de la organización. Las áreas se usan para asignar permisos de planificación y minutas a los usuarios.</p>
        </div>
    </div>

    {{-- ── Alerta global ───────────────────────────────────────────────────── --}}
    <div id="alerta-ok"  class="alert alert-ok  alert-hidden">✅ <span id="alerta-ok-msg"></span></div>
    <div id="alerta-err" class="alert alert-err alert-hidden">❌ <span id="alerta-err-msg"></span></div>

    <div class="card">

        {{-- ── Formulario nueva área ───────────────────────────────────────── --}}
        <div class="add-area-form">
            <input type="text" id="nueva-area-input" placeholder="Nombre del área nueva…" maxlength="100" autocomplete="off">
            <button class="btn-primary" id="btn-agregar-area" onclick="agregarArea()">
                + Agregar área
            </button>
        </div>

        {{-- ── Tabla ────────────────────────────────────────────────────────── --}}
        <table class="areas-table">
            <thead>
                <tr>
                    <th>Nombre del área</th>
                    <th style="width:120px; text-align:center">Usuarios</th>
                    <th style="width:90px; text-align:right">Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-areas-body">
                @foreach($areas as $area)
                <tr id="fila-area-{{ $area->id }}">
                    <td class="area-nombre">{{ $area->descripcion }}</td>
                    <td style="text-align:center">
                        <span class="badge-usuarios {{ $area->total_usuarios === 0 ? 'cero' : '' }}">
                            {{ $area->total_usuarios }}
                            {{ $area->total_usuarios === 1 ? 'usuario' : 'usuarios' }}
                        </span>
                    </td>
                    <td style="text-align:right">
                        <button class="btn-icon" title="Editar" onclick="abrirEditar({{ $area->id }}, '{{ addslashes($area->descripcion) }}')">✏️</button>
                        <button class="btn-icon danger" title="Eliminar" onclick="eliminarArea({{ $area->id }}, '{{ addslashes($area->descripcion) }}')">🗑️</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($areas->isEmpty())
        <p style="text-align:center; color:var(--text-muted); padding: 32px 0; margin:0">
            No hay áreas registradas. Agrega la primera arriba.
        </p>
        @endif
    </div>
</div>

{{-- ── Modal edición ──────────────────────────────────────────────────────── --}}
<div class="modal-overlay" id="modal-editar">
    <div class="modal-box">
        <h3>✏️ Editar área</h3>
        <input type="hidden" id="editar-area-id">
        <label for="editar-area-nombre">Nombre del área</label>
        <input type="text" id="editar-area-nombre" maxlength="100" placeholder="Ej: Medio Ambiente" autocomplete="off">
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-primary" id="btn-guardar-edicion" onclick="guardarEdicion()">Guardar cambios</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const BASE_AREAS = "{{ url('/areas') }}";

/* ── Utilidades ─────────────────────────────────────────────────────────────── */
function mostrarOk(msg) {
    document.getElementById('alerta-err').classList.add('alert-hidden');
    const el = document.getElementById('alerta-ok');
    document.getElementById('alerta-ok-msg').textContent = msg;
    el.classList.remove('alert-hidden');
    setTimeout(() => el.classList.add('alert-hidden'), 4000);
}
function mostrarErr(msg) {
    document.getElementById('alerta-ok').classList.add('alert-hidden');
    const el = document.getElementById('alerta-err');
    document.getElementById('alerta-err-msg').textContent = msg;
    el.classList.remove('alert-hidden');
}

/* ── Crear área ─────────────────────────────────────────────────────────────── */
async function agregarArea() {
    const input = document.getElementById('nueva-area-input');
    const nombre = input.value.trim();
    if (! nombre) { input.focus(); return; }

    const btn = document.getElementById('btn-agregar-area');
    btn.disabled = true;
    btn.textContent = 'Guardando…';

    try {
        const res  = await window.sgcFetch(BASE_AREAS, {
            method: 'POST',
            body: JSON.stringify({ descripcion: nombre }),
        });
        const data = await res.json();

        if (! res.ok) { mostrarErr(data.error || data.message || 'Error al crear área.'); return; }

        input.value = '';
        agregarFilaTabla(data.area);
        mostrarOk(`Área "${data.area.descripcion}" creada correctamente.`);
    } catch (e) {
        mostrarErr('Error de conexión. Intenta de nuevo.');
    } finally {
        btn.disabled = false;
        btn.textContent = '+ Agregar área';
    }
}

function agregarFilaTabla(area) {
    const tbody = document.getElementById('tabla-areas-body');
    const tr    = document.createElement('tr');
    tr.id       = `fila-area-${area.id}`;
    tr.innerHTML = `
        <td class="area-nombre">${area.descripcion}</td>
        <td style="text-align:center">
            <span class="badge-usuarios cero">0 usuarios</span>
        </td>
        <td style="text-align:right">
            <button class="btn-icon" title="Editar" onclick="abrirEditar(${area.id}, '${area.descripcion.replace(/'/g,"\\'")}')">✏️</button>
            <button class="btn-icon danger" title="Eliminar" onclick="eliminarArea(${area.id}, '${area.descripcion.replace(/'/g,"\\'")}')">🗑️</button>
        </td>`;
    tbody.appendChild(tr);

    // Animar entrada
    tr.style.background = '#f0fdf4';
    setTimeout(() => tr.style.background = '', 1500);
}

/* ── Editar área ────────────────────────────────────────────────────────────── */
function abrirEditar(id, nombre) {
    document.getElementById('editar-area-id').value = id;
    document.getElementById('editar-area-nombre').value = nombre;
    document.getElementById('modal-editar').classList.add('visible');
    setTimeout(() => document.getElementById('editar-area-nombre').focus(), 60);
}
function cerrarModal() {
    document.getElementById('modal-editar').classList.remove('visible');
}

async function guardarEdicion() {
    const id     = document.getElementById('editar-area-id').value;
    const nombre = document.getElementById('editar-area-nombre').value.trim();
    if (! nombre) { document.getElementById('editar-area-nombre').focus(); return; }

    const btn = document.getElementById('btn-guardar-edicion');
    btn.disabled = true;
    btn.textContent = 'Guardando…';

    try {
        const res  = await window.sgcFetch(`${BASE_AREAS}/${id}`, {
            method: 'PUT',
            body: JSON.stringify({ descripcion: nombre }),
        });
        const data = await res.json();

        if (! res.ok) { mostrarErr(data.error || data.message || 'Error al actualizar.'); return; }

        // Actualizar nombre en la fila
        const fila = document.getElementById(`fila-area-${id}`);
        if (fila) {
            fila.querySelector('.area-nombre').textContent = data.area.descripcion;
            // Actualizar argumento del botón editar
            fila.querySelector('.btn-icon').setAttribute(
                'onclick', `abrirEditar(${id}, '${data.area.descripcion.replace(/'/g,"\\'")}')` );
        }

        cerrarModal();
        mostrarOk(`Área actualizada correctamente.`);
    } catch (e) {
        mostrarErr('Error de conexión. Intenta de nuevo.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar cambios';
    }
}

// Cerrar modal al hacer clic fuera
document.getElementById('modal-editar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

// Enter en el input de edición
document.getElementById('editar-area-nombre').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') guardarEdicion();
});

/* ── Eliminar área ──────────────────────────────────────────────────────────── */
async function eliminarArea(id, nombre) {
    if (! confirm(`¿Eliminar el área "${nombre}"?\n\nSolo se puede eliminar si no tiene usuarios asignados.`)) return;

    try {
        const res  = await window.sgcFetch(`${BASE_AREAS}/${id}`, { method: 'DELETE' });
        const data = await res.json();

        if (! res.ok) { mostrarErr(data.error || 'No se pudo eliminar.'); return; }

        const fila = document.getElementById(`fila-area-${id}`);
        if (fila) {
            fila.style.background = '#fee2e2';
            setTimeout(() => fila.remove(), 400);
        }
        mostrarOk(data.mensaje || 'Área eliminada.');
    } catch (e) {
        mostrarErr('Error de conexión. Intenta de nuevo.');
    }
}

// Enter en input nueva área
document.getElementById('nueva-area-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') agregarArea();
});
</script>
@endpush
