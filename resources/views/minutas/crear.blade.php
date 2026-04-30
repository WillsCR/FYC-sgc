@extends('layouts.app')
@section('title', 'Nueva Minuta')

@push('styles')
<style>
.form-body { padding: 20px; max-width: 1100px; margin: 0 auto; }

/* Header */
.form-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
}
.form-header h2 { font-size: 1.1rem; color: var(--navy); margin-bottom: 2px; }
.form-header p  { font-size: .78rem; color: var(--text-muted); }

/* Card */
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

/* Grid formulario */
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

/* Tabla dinámica convocados */
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
.dyn-table select {
    width: 100%; padding: 6px 8px;
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: .78rem; font-family: var(--font);
    background: var(--body-bg); color: var(--text-primary); outline: none;
}
.dyn-table input:focus,
.dyn-table select:focus { border-color: var(--blue-accent); }

/* Botones */
.btn-add-row {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: var(--radius-sm);
    font-size: .75rem; font-weight: 600; cursor: pointer;
    border: 1px dashed var(--navy); color: var(--navy);
    background: transparent; transition: all .12s;
}
.btn-add-row:hover { background: var(--navy); color: #fff; border-style: solid; }

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

/* Status badge en select */
.status-select { min-width: 120px; }

/* Alerts */
.alert-err {
    background:#FCEBEB; border-left:3px solid #DC2626; color:#991B1B;
    padding:10px 14px; border-radius:var(--radius-sm);
    font-size:.82rem; margin-bottom:14px;
}

/* Ítem número */
.num-cell {
    text-align: center; font-weight: 700; color: var(--navy);
    font-size: .78rem; width: 32px;
}

/* Responsive */
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

    {{-- Header --}}
    <div class="form-header">
        <div>
            <h2>🗒️ Nueva Minuta</h2>
            <p>Completa los datos de la reunión, participantes y compromisos.</p>
        </div>
        <a href="{{ route('minutas.index') }}" class="btn-cancelar">← Volver</a>
    </div>

    @if($errors->any())
        <div class="alert-err">❌ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('minutas.store') }}">
        @csrf

        {{-- Datos generales --}}
        <div class="form-card">
            <div class="form-card-header">📋 Datos de la reunión</div>
            <div class="form-card-body">
                <div class="form-grid">

                    <div class="form-group">
                        <label>Fecha <span class="form-required">*</span></label>
                        <input type="date" name="fecha" value="{{ old('fecha') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Área / Proceso <span class="form-required">*</span></label>
                        <select name="id_area" required>
                            <option value="">Seleccionar...</option>
                            @foreach($areas as $id => $nombre)
                                <option value="{{ $id }}" {{ old('id_area') == $id ? 'selected' : '' }}>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Hora Inicio <span class="form-required">*</span></label>
                        <input type="time" name="hora_inicio" value="{{ old('hora_inicio') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Hora Término <span class="form-required">*</span></label>
                        <input type="time" name="hora_fin" value="{{ old('hora_fin') }}" required>
                    </div>

                    <div class="form-group col-2">
                        <label>Lugar <span class="form-required">*</span></label>
                        <input type="text" name="lugar" value="{{ old('lugar') }}"
                               placeholder="Ej: Sala de reuniones, Meet, Teams..." required>
                    </div>

                    <div class="form-group">
                        <label>Tipo de Reunión <span class="form-required">*</span></label>
                        <input type="text" name="tipo_reunion" value="{{ old('tipo_reunion') }}"
                               placeholder="Presencial, Online..." required>
                    </div>

                    <div class="form-group">
                        <label>Empresa <span class="form-required">*</span></label>
                        <input type="text" name="empresa" value="{{ old('empresa', 'FyC Chile SpA') }}" required>
                    </div>

                    <div class="form-group col-2">
                        <label>Próxima Reunión</label>
                        <input type="date" name="proxima_reunion" value="{{ old('proxima_reunion') }}">
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
                <table class="dyn-table" id="tabla-convocados">
                    <thead>
                        <tr>
                            <th style="width:140px">Empresa</th>
                            <th>Nombre y Apellidos</th>
                            <th style="width:180px">Cargo</th>
                            <th style="width:36px"></th>
                        </tr>
                    </thead>
                    <tbody id="body-convocados">
                        {{-- Fila inicial --}}
                        <tr>
                            <td><input type="text" name="conv_empresa[]" placeholder="Empresa"></td>
                            <td>
                                <select name="conv_id_usuario[]" onchange="autocompletarCargo(this)">
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
                <table class="dyn-table" id="tabla-compromisos">
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
                        {{-- Fila inicial --}}
                        <tr>
                            <td class="num-cell">1</td>
                            <td><textarea name="comp_descripcion[]" rows="2" placeholder="Descripción del compromiso..."></textarea></td>
                            <td><input type="text" name="comp_responsable[]" placeholder="Responsable"></td>
                            <td><input type="date" name="comp_inicio_compromiso[]"></td>
                            <td>
                                <select name="comp_status[]" class="status-select">
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
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Acciones --}}
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
            <a href="{{ route('minutas.index') }}" class="btn-cancelar">Cancelar</a>
            <button type="submit" class="btn-guardar">💾 Guardar Minuta</button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
// ── Usuarios disponibles para autocompletar ──────────────────────────────────
const usuariosData = @json($usuariosSelect);

// ── Plantilla fila convocado ─────────────────────────────────────────────────
function plantillaConvocado() {
    const opciones = usuariosData.map(u =>
        `<option value="${u.id}" data-nombre="${u.nombre}">${u.nombre}</option>`
    ).join('');

    return `<tr>
        <td><input type="text" name="conv_empresa[]" placeholder="Empresa"></td>
        <td>
            <select name="conv_id_usuario[]" onchange="autocompletarCargo(this)">
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

// ── Plantilla fila compromiso ────────────────────────────────────────────────
function plantillaCompromiso(num) {
    return `<tr>
        <td class="num-cell">${num}</td>
        <td><textarea name="comp_descripcion[]" rows="2" placeholder="Descripción..."></textarea></td>
        <td><input type="text" name="comp_responsable[]" placeholder="Responsable"></td>
        <td><input type="date" name="comp_inicio_compromiso[]"></td>
        <td>
            <select name="comp_status[]" class="status-select">
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

// ── Agregar fila ─────────────────────────────────────────────────────────────
function agregarConvocado() {
    document.getElementById('body-convocados').insertAdjacentHTML('beforeend', plantillaConvocado());
}

function agregarCompromiso() {
    const tbody = document.getElementById('body-compromisos');
    const num   = tbody.querySelectorAll('tr').length + 1;
    tbody.insertAdjacentHTML('beforeend', plantillaCompromiso(num));
}

// ── Eliminar fila ────────────────────────────────────────────────────────────
function eliminarFila(btn, tbodyId) {
    const tbody = document.getElementById(tbodyId);
    const fila  = btn.closest('tr');
    if (tbody.querySelectorAll('tr').length <= 1) {
        alert('Debe haber al menos una fila.');
        return;
    }
    fila.remove();
    // Re-numerar compromisos
    if (tbodyId === 'body-compromisos') {
        tbody.querySelectorAll('tr').forEach((tr, i) => {
            const cell = tr.querySelector('.num-cell');
            if (cell) cell.textContent = i + 1;
        });
    }
}

// ── Autocompletar nombre oculto cuando se selecciona usuario ─────────────────
function autocompletarCargo(select) {
    const fila    = select.closest('tr');
    const hidden  = fila.querySelector('input[name="conv_nom_ape[]"]');
    const opt     = select.options[select.selectedIndex];
    if (hidden) hidden.value = opt ? (opt.dataset.nombre || '') : '';
}
</script>
@endpush
