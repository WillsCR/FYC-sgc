@extends('layouts.app')
@section('title', 'Detalle Minuta #' . $minuta->id)

@push('styles')
<style>
.show-body { padding: 20px; max-width: 1100px; margin: 0 auto; }

/* Header */
.show-header {
    display: flex; align-items: flex-start;
    justify-content: space-between; flex-wrap: wrap;
    gap: 12px; margin-bottom: 20px;
}
.show-header h2 { font-size: 1.1rem; color: var(--navy); margin-bottom: 2px; }
.show-header p  { font-size: .78rem; color: var(--text-muted); }
.show-actions   { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }

/* Card base */
.show-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); margin-bottom: 16px; overflow: hidden;
}
.show-card-header {
    background: var(--navy); color: #fff;
    padding: 10px 16px; font-size: .78rem;
    font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    display: flex; align-items: center; gap: 8px;
}
.show-card-body { padding: 16px; }

/* Grid de datos */
.datos-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}
.dato-item {}
.dato-label {
    font-size: .68rem; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px;
}
.dato-valor {
    font-size: .85rem; color: var(--text-primary); font-weight: 500;
}

/* Tabla convocados */
.conv-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.conv-table th {
    background: var(--surface-2); color: var(--text-secondary);
    padding: 8px 12px; text-align: left;
    font-size: .68rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    border-bottom: 1px solid var(--border);
}
.conv-table td {
    padding: 9px 12px; border-bottom: 1px solid var(--border);
    color: var(--text-secondary); vertical-align: middle;
}
.conv-table tr:last-child td { border-bottom: none; }
.conv-table tr:hover td { background: var(--surface-2); }

/* Tabla compromisos */
.comp-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.comp-table th {
    background: var(--navy); color: #fff;
    padding: 9px 12px; text-align: left;
    font-size: .67rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .05em;
}
.comp-table td {
    padding: 10px 12px; border-bottom: 1px solid var(--border);
    color: var(--text-secondary); vertical-align: top;
}
.comp-table tr:last-child td { border-bottom: none; }
.comp-table tr:hover td { background: var(--surface-2); }

.item-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 50%;
    background: var(--navy); color: #fff;
    font-size: .7rem; font-weight: 700; flex-shrink: 0;
}

/* Status badges */
.status-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 12px;
    font-size: .68rem; font-weight: 700; white-space: nowrap;
}
.status-1 { background: #FEF3C7; color: #B45309; } /* En Proceso  */
.status-2 { background: #DCFCE7; color: #15803D; } /* Cerrado     */
.status-3 { background: #F1F5F9; color: #64748B; } /* Descartado  */

/* Botones */
.btn-accion {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: var(--radius-sm);
    font-size: .8rem; font-weight: 600; cursor: pointer;
    border: 1px solid; text-decoration: none; transition: all .12s;
}
.btn-edit   { color: var(--navy); border-color: var(--navy); background: transparent; }
.btn-back   { color: var(--text-secondary); border-color: var(--border); background: transparent; }
.btn-del    { color: #DC2626; border-color: #DC2626; background: transparent; }
.btn-edit:hover { background: var(--navy);   color: #fff; }
.btn-back:hover { background: var(--surface-2); }
.btn-del:hover  { background: #DC2626; color: #fff; }

/* Alerts */
.alert-ok { background:#DCFCE7; border-left:3px solid #16A34A; color:#166534;
            padding:10px 14px; border-radius:var(--radius-sm); font-size:.82rem; margin-bottom:14px; }

/* Área badge */
.area-badge {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 10px;
    font-size: .75rem; font-weight: 700;
    background: #EFF6FF; color: #1D4ED8;
}

/* Responsive */
@media (max-width: 900px) {
    .datos-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 560px) {
    .datos-grid { grid-template-columns: 1fr; }
    .show-body  { padding: 12px; }
    .comp-table th:nth-child(4),
    .comp-table td:nth-child(4) { display: none; }
}
</style>
@endpush

@section('content')
<div class="show-body">

    {{-- Header --}}
    <div class="show-header">
        <div>
            <h2>🗒️ Minuta #{{ $minuta->id }}</h2>
            <p>
                <span class="area-badge">{{ $minuta->area_nombre }}</span>
                &nbsp;·&nbsp;
                {{ $minuta->fecha ? \Carbon\Carbon::parse($minuta->fecha)->format('d/m/Y') : '—' }}
            </p>
        </div>
        <div class="show-actions">
            <a href="{{ route('minutas.index') }}" class="btn-accion btn-back">
                ← Volver
            </a>
            @if($puedeEditar)
            <a href="{{ route('minutas.edit', $minuta->id) }}" class="btn-accion btn-edit">
                ✏️ Editar
            </a>
            @endif
            @if($esAdmin)
            <form method="POST" action="{{ route('minutas.destroy', $minuta->id) }}"
                  onsubmit="return confirm('¿Eliminar esta minuta y todos sus compromisos?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-accion btn-del">🗑️ Eliminar</button>
            </form>
            @endif
        </div>
    </div>

    @if(session('ok'))
        <div class="alert-ok">✅ {{ session('ok') }}</div>
    @endif

    {{-- Datos generales --}}
    <div class="show-card">
        <div class="show-card-header">📋 Datos de la reunión</div>
        <div class="show-card-body">
            <div class="datos-grid">
                <div class="dato-item">
                    <div class="dato-label">Fecha</div>
                    <div class="dato-valor">
                        {{ $minuta->fecha ? \Carbon\Carbon::parse($minuta->fecha)->format('d/m/Y') : '—' }}
                    </div>
                </div>
                <div class="dato-item">
                    <div class="dato-label">Área / Proceso</div>
                    <div class="dato-valor">{{ $minuta->area_nombre }}</div>
                </div>
                <div class="dato-item">
                    <div class="dato-label">Lugar</div>
                    <div class="dato-valor">{{ $minuta->lugar ?: '—' }}</div>
                </div>
                <div class="dato-item">
                    <div class="dato-label">Tipo de Reunión</div>
                    <div class="dato-valor">{{ $minuta->tipo_reunion ?: '—' }}</div>
                </div>
                <div class="dato-item">
                    <div class="dato-label">Hora Inicio</div>
                    <div class="dato-valor">
                        {{ $minuta->hora_inicio ? \Carbon\Carbon::parse($minuta->hora_inicio)->format('H:i') : '—' }}
                    </div>
                </div>
                <div class="dato-item">
                    <div class="dato-label">Hora Término</div>
                    <div class="dato-valor">
                        {{ $minuta->hora_fin ? \Carbon\Carbon::parse($minuta->hora_fin)->format('H:i') : '—' }}
                    </div>
                </div>
                <div class="dato-item">
                    <div class="dato-label">Empresa</div>
                    <div class="dato-valor">{{ $minuta->empresa ?: '—' }}</div>
                </div>
                <div class="dato-item">
                    <div class="dato-label">Próxima Reunión</div>
                    <div class="dato-valor">
                        @if($minuta->proxima_reunion)
                            {{ \Carbon\Carbon::parse($minuta->proxima_reunion)->format('d/m/Y') }}
                        @else
                            <span style="color:var(--text-muted)">Sin fecha</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Participantes --}}
    <div class="show-card">
        <div class="show-card-header">
            👥 Participantes
            <span style="font-size:.7rem;font-weight:400;opacity:.8">
                ({{ $convocados->count() }} {{ $convocados->count() === 1 ? 'persona' : 'personas' }})
            </span>
        </div>
        <div class="show-card-body" style="padding:0">
            @if($convocados->isEmpty())
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:.82rem">
                    Sin participantes registrados.
                </div>
            @else
            <table class="conv-table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Nombre y Apellidos</th>
                        <th>Cargo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($convocados as $c)
                    <tr>
                        <td style="font-size:.78rem">{{ $c->empresa ?: '—' }}</td>
                        <td style="font-weight:500;color:var(--text-primary)">{{ $c->nombre_display ?: '—' }}</td>
                        <td style="font-size:.78rem">{{ $c->cargo ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Compromisos --}}
    <div class="show-card">
        <div class="show-card-header">
            ✅ Compromisos
            <span style="font-size:.7rem;font-weight:400;opacity:.8">
                ({{ $compromisos->count() }} {{ $compromisos->count() === 1 ? 'ítem' : 'ítems' }})
            </span>
        </div>
        <div class="show-card-body" style="padding:0">
            @if($compromisos->isEmpty())
                <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:.82rem">
                    Sin compromisos registrados.
                </div>
            @else
            <table class="comp-table">
                <thead>
                    <tr>
                        <th style="width:42px">N°</th>
                        <th>Descripción</th>
                        <th style="width:140px">Responsable</th>
                        <th style="width:120px">Fecha Comp.</th>
                        <th style="width:110px">Status</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compromisos as $c)
                    <tr>
                        <td style="text-align:center">
                            <span class="item-num">{{ $c->item ?: $loop->iteration }}</span>
                        </td>
                        <td style="color:var(--text-primary);font-weight:500">
                            {{ $c->descripcion }}
                        </td>
                        <td style="font-size:.78rem;white-space:nowrap">{{ $c->responsable ?: '—' }}</td>
                        <td style="font-size:.75rem;white-space:nowrap">
                            {{ $c->inicio_compromiso ? \Carbon\Carbon::parse($c->inicio_compromiso)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <span class="status-badge status-{{ $c->status }}">
                                {{ $c->status_nombre }}
                            </span>
                        </td>
                        <td style="font-size:.75rem;color:var(--text-muted)">
                            {{ $c->observaciones ?: '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

</div>
@endsection
