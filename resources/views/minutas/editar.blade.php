@extends('layouts.app')
@section('title', 'Editar Minuta #' . $minuta->id)

@push('styles')
<style>
.form-body { padding: 20px; max-width: 1100px; margin: 0 auto; }

.form-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
}
.form-header h2 { font-size: 1.1rem; color: var(--navy); margin-bottom: 2px; }
.form-header p  { font-size: .78rem; color: var(--text-muted); }

.form-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); margin-bottom: 16px; overflow: hidden;
}
.form-card-header {
    background: var(--navy); color: #fff;
    padding: 10px 16px; font-size: .78rem;
    font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    display: flex; align-items: center; justify-content: space-between;
}
.form-card-body { padding: 16px; }

.form-grid {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group.col-2 { grid-column: span 2; }
.form-group.col-4 { grid-column: span 4; }
.form-group label {
    font-size: .72rem; font-weight: 700; color: var(--text-secondary);
    text-transform: uppercase; letter-spacing: .04em;
}
.form-group input,
.form-group select,
.form-group textarea {
    padding: 8px 10px; border: 1px solid var(--border);
    border-radius: var(--radius-sm); font-size: .82rem;
    font-family: var(--font); outline: none;
    background: var(--body-bg); color: var(--text-primary);
    transition: border-color .15s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color: var(--blue-accent); }
.form-group textarea { resize: vertical; min-height: 60px; }
.form-required { color: #DC2626; margin-left: 2px; }

.dyn-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.dyn-table th {
    background: var(--surface-2); color: var(--text-secondary);
    padding: 8px 10px; text-align: left;
    font-size: .67rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    border-bottom: 1px solid var(--border);
}
.dyn-table td {
    padding: 6px 6px; border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.dyn-table tr:last-child td { border-bottom: none; }
.dyn-table input,
.dyn-table select,
.dyn-table textarea {
    width: 100%; padding: 6px 8px;
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: .78rem; font-family: var(--font);
    background: var(--body-bg); color: var(--text-primary); outline: none;
}
.dyn-table textarea { resize: vertical; min-height: 50px; }
.dyn-table input:focus,
.dyn-table select:focus,
.dyn-table textarea:focus { border-color: var(--blue-accent); }

.btn-add-row {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: var(--radius-sm);
    font-size: .75rem; font-weight: 600; cursor: pointer;
    border: 1px dashed var(--navy); color: var(--navy);
    background: transparent; transition: all .12s;
}
.btn-add-row:hover { background: var(--navy); color: #fff; border-style: solid; }

/* Botones de agregar fila dentro del header oscuro */
.form-card-header .btn-add-row {
    border-color: rgba(255,255,255,.6);
    color: #fff;
}
.form-card-header .btn-add-row:hover {
    background: rgba(255,255,255,.15);
    border-color: #fff;
    border-style: dashed;
    color: #fff;
}

.btn-del-row {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: var(--radius-sm);
    border: 1px solid #DC2626; color: #DC2626;
    background: transparent; cursor: pointer; transition: all .12s;
    font-size: .85rem; flex-shrink: 0;
}
.btn-del-row:hover { background: #DC2626; color: #fff; }

.btn-guardar {
    padding: 9px 20px; background: var(--navy); color: #fff;
    border: none; border-radius: var(--radius-sm);
    font-size: .85rem; font-weight: 700; cursor: pointer;
    transition: background .15s;
}
.btn-guardar:hover { background: var(--navy-light); }
.btn-cancelar {
    padding: 9px 16px; background: transparent; color: var(--text-secondary);
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: .85rem; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center;
}

.alert-err {
    background:#FCEBEB; border-left:3px solid #DC2626; color:#991B1B;
    padding:10px 14px; border-radius:var(--radius-sm);
    font-size:.82rem; margin-bottom:14px;
}

.num-cell {
    text-align: center; font-weight: 700; color: var(--navy);
    font-size: .78rem; width: 32px;
}

/* Tipo participante */
.tipo-conv {
    font-weight: 600; cursor: pointer;
    border-color: var(--blue-accent) !important;
    color: var(--navy) !important;
}
.tipo-conv.externo {
    border-color: #7c3aed !important;
    color: #7c3aed !important;
}

@media (max-width: 900px) {
    .form-grid { grid-template-columns: repeat(2, 1fr); }
    .form-group.col-2 { grid-column: span 1; }
    .form-group.col-4 { grid-column: span 2; }
}
@media (max-width: 560px) {
    .form-body  { padding: 12px; }
    .form-grid  { grid-template-columns: 1fr; }
    .form-group.col-2,
    .form-group.col-4 { grid-column: span 1; }
}
</style>
@endpush

@section('content')
<div class="form-body">

    <div class="form-header">
        <div>
            <h2>✏️ Editar Minuta #{{ $minuta->id }}</h2>
            <p>Modifica los datos, participantes y compromisos de esta reunión.</p>
        </div>
        <a href="{{ route('minutas.show', $minuta->id) }}" class="btn-cancelar">← Volver</a>
    </div>

    @if($errors->any())
        <div class="alert-err">❌ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('minutas.update', $minuta->id) }}">
        @csrf
        @method('PUT')

        {{-- Datos generales --}}
        <div class="form-card">
            <div class="form-card-header">📋 Datos de la reunión</div>
            <div class="form-card-body">
                <div class="form-grid">

                    <div class="form-group">
                        <label>Fecha <span class="form-required">*</span></label>
                        <input type="date" name="fecha"
                               value="{{ old('fecha', $minuta->fecha) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Área / Proceso <span class="form-required">*</span></label>
                        <select name="id_area" required>
                            <option value="">Seleccionar...</option>
                            @foreach($areas as $id => $nombre)
                                <option value="{{ $id }}"
                                    {{ old('id_area', $minuta->id_area) == $id ? 'selected' : '' }}>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Hora Inicio <span class="form-required">*</span></label>
                        <input type="time" name="hora_inicio"
                               value="{{ old('hora_inicio', substr($minuta->hora_inicio, 0, 5)) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Hora Término <span class="form-required">*</span></label>
                        <input type="time" name="hora_fin"
                               value="{{ old('hora_fin', substr($minuta->hora_fin, 0, 5)) }}" required>
                    </div>

                    <div class="form-group col-2">
                        <label>Lugar <span class="form-required">*</span></label>
                        <input type="text" name="lugar"
                               value="{{ old('lugar', $minuta->lugar) }}"
                               placeholder="Ej: Sala de reuniones, Meet..." required>
                    </div>

                    <div class="form-group">
                        <label>Tipo de Reunión <span class="form-required">*</span></label>
                        <input type="text" name="tipo_reunion"
                               value="{{ old('tipo_reunion', $minuta->tipo_reunion) }}"
                               placeholder="Presencial, Online..." required>
                    </div>

                    <div class="form-group">
                        <label>Empresa <span class="form-required">*</span></label>
                        <input type="text" name="empresa"
                               value="{{ old('empresa', $minuta->empresa) }}" required>
                    </div>

                    <div class="form-group col-2">
                        <label>Próxima Reunión</label>
                        <input type="date" name="proxima_reunion"
                               value="{{ old('proxima_reunion', $minuta->proxima_reunion) }}">
                    </div>

                </div>
            </div>
        </div>

        {{-- Participantes --}}
        <div class="form-card">
            <div class="form-card-header">
                <span>👥 Participantes</span>
                <button type="button" class="btn-add-row" onclick="agregarConvocado()">
                    + Agregar participante
                </button>
            </div>
            <div class="form-card-body" style="padding:0">
                <table class="dyn-table">
                    <thead>
                        <tr>
                            <th style="width:120px">Empresa</th>
                            <th style="width:95px">Tipo</th>
                            <th>Nombre y Apellidos</th>
                            <th style="width:170px">Cargo</th>
                            <th style="width:36px"></th>
                        </tr>
                    </thead>
                    <tbody id="body-convocados">
                        @forelse($convocados as $c)
                        @php $esExterno = is_null($c->id_usuario); @endphp
                        <tr>
                            <td>
                                <input type="text" name="conv_empresa[]"
                                       value="{{ $c->empresa }}" placeholder="Empresa">
                            </td>
                            <td>
                                <select onchange="cambiarTipo(this)"
                                        class="tipo-conv {{ $esExterno ? 'externo' : '' }}">
                                    <option value="interno" {{ !$esExterno ? 'selected' : '' }}>Interno</option>
                                    <option value="externo" {{ $esExterno  ? 'selected' : '' }}>Externo</option>
                                </select>
                            </td>
                            <td class="nombre-cell">
                                @if($esExterno)
                                    <input type="hidden" name="conv_id_usuario[]" value="">
                                    <input type="text"   name="conv_nom_ape[]"
                                           value="{{ $c->nom_ape }}"
                                           placeholder="Nombre y apellidos completos...">
                                @else
                                    <select name="conv_id_usuario[]" onchange="autocompletarNombre(this)">
                                        <option value="">— Seleccionar usuario —</option>
                                        @foreach($usuariosSelect as $u)
                                            <option value="{{ $u->id }}"
                                                data-nombre="{{ $u->nombre }}"
                                                {{ $c->id_usuario == $u->id ? 'selected' : '' }}>
                                                {{ $u->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="conv_nom_ape[]" value="{{ $c->nom_ape }}">
                                @endif
                            </td>
                            <td>
                                <input type="text" name="conv_cargo[]"
                                       value="{{ $c->cargo }}" placeholder="Cargo">
                            </td>
                            <td>
                                <button type="button" class="btn-del-row"
                                        onclick="eliminarFila(this, 'body-convocados')">✕</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td><input type="text" name="conv_empresa[]" placeholder="Empresa"></td>
                            <td>
                                <select onchange="cambiarTipo(this)" class="tipo-conv">
                                    <option value="interno">Interno</option>
                                    <option value="externo">Externo</option>
                                </select>
                            </td>
                            <td class="nombre-cell">
                                <select name="conv_id_usuario[]" onchange="autocompletarNombre(this)">
                                    <option value="">— Seleccionar usuario —</option>
                                    @foreach($usuariosSelect as $u)
                                        <option value="{{ $u->id }}" data-nombre="{{ $u->nombre }}">
                                            {{ $u->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="conv_nom_ape[]" value="">
                            </td>
                            <td><input type="text" name="conv_cargo[]" placeholder="Cargo"></td>
                            <td>
                                <button type="button" class="btn-del-row"
                                        onclick="eliminarFila(this, 'body-convocados')">✕</button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Compromisos --}}
        <div class="form-card">
            <div class="form-card-header">
                <span>✅ Compromisos</span>
                <button type="button" class="btn-add-row" onclick="agregarCompromiso()">
                    + Agregar compromiso
                </button>
            </div>
            <div class="form-card-body" style="padding:0">
                <table class="dyn-table">
                    <thead>
                        <tr>
                            <th style="width:32px">N°</th>
                            <th>Descripción</th>
                            <th style="width:140px">Responsable</th>
                            <th style="width:130px">Fecha Comp.</th>
                            <th style="width:130px">Status</th>
                            <th>Observaciones</th>
                            <th style="width:36px"></th>
                        </tr>
                    </thead>
                    <tbody id="body-compromisos">
                        @forelse($compromisos as $i => $c)
                        <tr>
                            <td class="num-cell">{{ $i + 1 }}</td>
                            <td>
                                <textarea name="comp_descripcion[]" rows="2"
                                          placeholder="Descripción...">{{ $c->descripcion }}</textarea>
                            </td>
                            <td>
                                <input type="text" name="comp_responsable[]"
                                       value="{{ $c->responsable }}" placeholder="Responsable">
                            </td>
                            <td>
                                <input type="date" name="comp_inicio_compromiso[]"
                                       value="{{ $c->inicio_compromiso }}">
                            </td>
                            <td>
                                <select name="comp_status[]">
                                    <option value="1" {{ $c->status == 1 ? 'selected' : '' }}>En Proceso</option>
                                    <option value="2" {{ $c->status == 2 ? 'selected' : '' }}>Cerrado</option>
                                    <option value="3" {{ $c->status == 3 ? 'selected' : '' }}>Descartado</option>
                                </select>
                            </td>
                            <td>
                                <textarea name="comp_observaciones[]" rows="2"
                                          placeholder="Observaciones...">{{ $c->observaciones }}</textarea>
                            </td>
                            <td>
                                <button type="button" class="btn-del-row"
                                        onclick="eliminarFila(this, 'body-compromisos')">✕</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="num-cell">1</td>
                            <td><textarea name="comp_descripcion[]" rows="2" placeholder="Descripción..."></textarea></td>
                            <td><input type="text" name="comp_responsable[]" placeholder="Responsable"></td>
                            <td><input type="date" name="comp_inicio_compromiso[]"></td>
                            <td>
                                <select name="comp_status[]">
                                    <option value="1">En Proceso</option>
                                    <option value="2">Cerrado</option>
                                    <option value="3">Descartado</option>
                                </select>
                            </td>
                            <td><textarea name="comp_observaciones[]" rows="2" placeholder="Observaciones..."></textarea></td>
                            <td>
                                <button type="button" class="btn-del-row"
                                        onclick="eliminarFila(this, 'body-compromisos')">✕</button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Acciones --}}
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
            <a href="{{ route('minutas.show', $minuta->id) }}" class="btn-cancelar">Cancelar</a>
            <button type="button" class="btn-guardar" id="btn-previa-notif">💾 Guardar cambios</button>
        </div>

    </form>
</div>

{{-- ── Modal selección de destinatarios ─────────────────────────────── --}}
<div id="modal-notif" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:500;align-items:center;justify-content:center">
<div style="background:#fff;border-radius:8px;max-width:560px;width:95%;max-height:88vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,.22)">

    <div style="background:var(--navy);color:#fff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
        <div>
            <div style="font-size:.95rem;font-weight:700">📧 Notificar a participantes</div>
            <div style="font-size:.72rem;opacity:.75;margin-top:2px">Selecciona a quiénes enviar la notificación de esta minuta</div>
        </div>
        <button type="button" onclick="cerrarModalNotif()" style="background:rgba(255,255,255,.15);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center">&times;</button>
    </div>

    <div id="notif-lista" style="overflow-y:auto;flex:1;padding:16px 20px;"></div>

    <div style="padding:14px 20px;border-top:1px solid #e8edf2;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;gap:10px;flex-wrap:wrap">
        <button type="button" onclick="guardarSinNotificar()"
            style="background:transparent;border:none;color:var(--text-secondary);font-size:.8rem;cursor:pointer;text-decoration:underline;padding:4px">
            Guardar sin notificar
        </button>
        <button type="button" id="btn-enviar-notif" onclick="confirmarNotif()"
            style="padding:9px 20px;background:var(--navy);color:#fff;border:none;border-radius:6px;font-size:.84rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px">
            📧 <span id="btn-enviar-label">Enviar y guardar</span>
        </button>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script>
const usuariosData = @json($usuariosSelect);

function plantillaConvocado() {
    const opciones = usuariosData.map(u =>
        `<option value="${u.id}" data-nombre="${u.nombre}">${u.nombre}</option>`
    ).join('');
    return `<tr>
        <td><input type="text" name="conv_empresa[]" placeholder="Empresa"></td>
        <td>
            <select onchange="cambiarTipo(this)" class="tipo-conv">
                <option value="interno">Interno</option>
                <option value="externo">Externo</option>
            </select>
        </td>
        <td class="nombre-cell">
            <select name="conv_id_usuario[]" onchange="autocompletarNombre(this)">
                <option value="">— Seleccionar usuario —</option>
                ${opciones}
            </select>
            <input type="hidden" name="conv_nom_ape[]" value="">
        </td>
        <td><input type="text" name="conv_cargo[]" placeholder="Cargo"></td>
        <td>
            <button type="button" class="btn-del-row"
                    onclick="eliminarFila(this, 'body-convocados')">✕</button>
        </td>
    </tr>`;
}

function plantillaCompromiso(num) {
    return `<tr>
        <td class="num-cell">${num}</td>
        <td><textarea name="comp_descripcion[]" rows="2" placeholder="Descripción..."></textarea></td>
        <td><input type="text" name="comp_responsable[]" placeholder="Responsable"></td>
        <td><input type="date" name="comp_inicio_compromiso[]"></td>
        <td>
            <select name="comp_status[]">
                <option value="1">En Proceso</option>
                <option value="2">Cerrado</option>
                <option value="3">Descartado</option>
            </select>
        </td>
        <td><textarea name="comp_observaciones[]" rows="2" placeholder="Observaciones..."></textarea></td>
        <td>
            <button type="button" class="btn-del-row"
                    onclick="eliminarFila(this, 'body-compromisos')">✕</button>
        </td>
    </tr>`;
}

function agregarConvocado() {
    document.getElementById('body-convocados').insertAdjacentHTML('beforeend', plantillaConvocado());
}

function agregarCompromiso() {
    const tbody = document.getElementById('body-compromisos');
    const num   = tbody.querySelectorAll('tr').length + 1;
    tbody.insertAdjacentHTML('beforeend', plantillaCompromiso(num));
}

function eliminarFila(btn, tbodyId) {
    const tbody = document.getElementById(tbodyId);
    if (tbody.querySelectorAll('tr').length <= 1) {
        showToast('⚠ Debe haber al menos una fila en la tabla', 'warning');
        return;
    }
    btn.closest('tr').remove();
    if (tbodyId === 'body-compromisos') {
        tbody.querySelectorAll('tr').forEach((tr, i) => {
            const cell = tr.querySelector('.num-cell');
            if (cell) cell.textContent = i + 1;
        });
    }
}

function autocompletarNombre(select) {
    const fila   = select.closest('tr');
    const hidden = fila.querySelector('input[name="conv_nom_ape[]"]');
    const opt    = select.options[select.selectedIndex];
    if (hidden) hidden.value = opt ? (opt.dataset.nombre || '') : '';
}

// ── Cambiar entre participante interno / externo ──────────────────────────────
function cambiarTipo(select) {
    const fila = select.closest('tr');
    const cell = fila.querySelector('.nombre-cell');
    const esExterno = select.value === 'externo';

    select.classList.toggle('externo', esExterno);

    if (esExterno) {
        cell.innerHTML = `
            <input type="hidden" name="conv_id_usuario[]" value="">
            <input type="text"   name="conv_nom_ape[]"    placeholder="Nombre y apellidos completos...">
        `;
    } else {
        const opciones = usuariosData.map(u =>
            `<option value="${u.id}" data-nombre="${u.nombre}">${u.nombre}</option>`
        ).join('');
        cell.innerHTML = `
            <select name="conv_id_usuario[]" onchange="autocompletarNombre(this)">
                <option value="">— Seleccionar usuario —</option>
                ${opciones}
            </select>
            <input type="hidden" name="conv_nom_ape[]" value="">
        `;
    }
}

// ── Modal de notificación ────────────────────────────────────────────────────
const _formAction = "{{ route('minutas.update', $minuta->id) }}";

document.getElementById('btn-previa-notif').addEventListener('click', function () {
    const form = document.querySelector(`form[action="${_formAction}"]`);
    if (!form.checkValidity()) { form.reportValidity(); return; }
    abrirModalNotif();
});

function abrirModalNotif() {
    const filas = document.querySelectorAll('#body-convocados tr');
    const destinatarios = [];

    filas.forEach(tr => {
        const tipoSel = tr.querySelector('select.tipo-conv');
        const tipo    = tipoSel ? tipoSel.value : 'interno';

        if (tipo === 'interno') {
            const sel = tr.querySelector('select[name="conv_id_usuario[]"]');
            const id  = sel ? parseInt(sel.value) : 0;
            const u   = id ? usuariosData.find(x => x.id == id) : null;
            const nombreFallback = tr.querySelector('input[name="conv_nom_ape[]"]')?.value?.trim()
                                || (sel?.options[sel?.selectedIndex]?.text || '').trim();
            const nombre = u ? u.nombre : nombreFallback;
            const email  = u ? (u.email || '') : '';
            if (nombre && nombre !== '— Seleccionar usuario —') {
                destinatarios.push({ nombre, email, tipo: 'interno' });
            }
        } else {
            const nomInput = tr.querySelector('td.nombre-cell input[type="text"]');
            const nombre   = nomInput ? nomInput.value.trim() : '';
            if (nombre) destinatarios.push({ nombre, email: '', tipo: 'externo' });
        }
    });

    const lista = document.getElementById('notif-lista');

    if (destinatarios.length === 0) {
        lista.innerHTML = `<p style="color:#888;font-size:.85rem;text-align:center;padding:20px 0">No hay participantes registrados.</p>`;
    } else {
        let html = `<div style="font-size:.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px">Participantes (${destinatarios.length})</div>`;
        destinatarios.forEach((d, i) => {
            const tieneEmail = d.email && d.email.trim() !== '';
            const badge = d.tipo === 'externo'
                ? `<span style="font-size:.65rem;background:#F3E8FF;color:#7C3AED;padding:1px 7px;border-radius:10px;font-weight:700">Externo</span>`
                : `<span style="font-size:.65rem;background:#EFF6FF;color:#1D4ED8;padding:1px 7px;border-radius:10px;font-weight:700">Interno</span>`;

            html += `
            <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f0f4f8">
                <input type="checkbox" id="notif-chk-${i}" data-idx="${i}"
                       ${tieneEmail ? 'checked' : ''}
                       onchange="actualizarContador()"
                       style="width:16px;height:16px;cursor:pointer;flex-shrink:0;accent-color:var(--navy)">
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
                        <span style="font-size:.82rem;font-weight:600;color:var(--navy)">${d.nombre}</span>
                        ${badge}
                    </div>
                    <input type="text" id="notif-email-${i}" value="${d.email}"
                           placeholder="${d.tipo === 'externo' ? 'Ingresar correo del participante externo...' : 'Sin email registrado — puedes ingresarlo aquí'}"
                           oninput="actualizarContador()"
                           style="width:100%;padding:5px 8px;border:1px solid ${tieneEmail ? '#cbd5e1' : '#f59e0b'};border-radius:4px;font-size:.78rem;font-family:inherit;outline:none;background:${tieneEmail ? '#fff' : '#fffbeb'}"
                           onfocus="this.style.borderColor='var(--blue-accent)'"
                           onblur="this.style.borderColor='${tieneEmail ? '#cbd5e1' : '#f59e0b'}'">
                </div>
            </div>`;
        });
        lista.innerHTML = html;
    }

    window._notifDestinatarios = destinatarios;
    actualizarContador();
    document.getElementById('modal-notif').style.display = 'flex';
}

function actualizarContador() {
    const dest = window._notifDestinatarios || [];
    let validos = 0;
    dest.forEach((_, i) => {
        const chk   = document.getElementById(`notif-chk-${i}`);
        const email = (document.getElementById(`notif-email-${i}`)?.value || '').trim();
        const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        if (chk && valid && !chk.checked) chk.checked = true;
        if (chk?.checked && valid) validos++;
    });
    const lbl = document.getElementById('btn-enviar-label');
    if (lbl) lbl.textContent = validos > 0 ? `Enviar y guardar (${validos})` : 'Enviar y guardar';
    const btn = document.getElementById('btn-enviar-notif');
    if (btn) btn.style.opacity = validos > 0 ? '1' : '.55';
}

function cerrarModalNotif() {
    document.getElementById('modal-notif').style.display = 'none';
}

function guardarSinNotificar() {
    cerrarModalNotif();
    mostrarCargando('Guardando cambios...');
    document.querySelector(`form[action="${_formAction}"]`).submit();
}

function confirmarNotif() {
    const dest = window._notifDestinatarios || [];
    const form = document.querySelector(`form[action="${_formAction}"]`);

    form.querySelectorAll('input[name="notif_emails[]"], input[name="notif_nombres[]"]').forEach(e => e.remove());

    let totalValidos = 0;
    dest.forEach((d, i) => {
        const chk   = document.getElementById(`notif-chk-${i}`);
        const email = (document.getElementById(`notif-email-${i}`)?.value || '').trim();
        if (chk?.checked && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            const inpE = document.createElement('input'); inpE.type='hidden'; inpE.name='notif_emails[]'; inpE.value=email; form.appendChild(inpE);
            const inpN = document.createElement('input'); inpN.type='hidden'; inpN.name='notif_nombres[]'; inpN.value=d.nombre; form.appendChild(inpN);
            totalValidos++;
        }
    });

    cerrarModalNotif();
    mostrarCargando(totalValidos > 0
        ? `Guardando y enviando ${totalValidos} notificación${totalValidos > 1 ? 'es' : ''}...`
        : 'Guardando cambios...'
    );
    form.submit();
}

document.getElementById('modal-notif').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalNotif();
});

// ── Overlay de carga ─────────────────────────────────────────
function mostrarCargando(msg) {
    document.getElementById('cargando-msg').textContent = msg;
    document.getElementById('overlay-cargando').style.display = 'flex';
}
</script>

{{-- Overlay de carga --}}
<div id="overlay-cargando" style="display:none;position:fixed;inset:0;background:rgba(10,21,47,.72);z-index:9999;flex-direction:column;align-items:center;justify-content:center;gap:20px">
    <div style="width:52px;height:52px;border:4px solid rgba(255,255,255,.2);border-top-color:#fff;border-radius:50%;animation:spin .75s linear infinite"></div>
    <div id="cargando-msg" style="color:#fff;font-size:.95rem;font-weight:600;letter-spacing:.02em;text-align:center;max-width:260px;line-height:1.4"></div>
    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
</div>

@endpush
