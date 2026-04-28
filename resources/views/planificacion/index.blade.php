@extends('layouts.app')
@section('title', 'Planificación')

@push('styles')
<style>
.plan-body { padding: 20px; max-width: 1400px; margin: 0 auto; }

.plan-header {
    display: flex; align-items: flex-start;
    justify-content: space-between; flex-wrap: wrap;
    gap: 12px; margin-bottom: 20px;
}
.plan-header h2 { font-size: 1.1rem; color: var(--navy); margin-bottom: 2px; }
.plan-header p  { font-size: .78rem; color: var(--text-muted); }

.plan-kpis {
    display: grid; grid-template-columns: repeat(4, minmax(0,1fr));
    gap: 10px; margin-bottom: 18px;
}
.plan-kpi {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); padding: 12px 16px;
    border-top: 3px solid var(--navy);
}
.plan-kpi.pendiente { border-top-color: #D97706; }
.plan-kpi.cerrada   { border-top-color: #16A34A; }
.plan-kpi.vencida   { border-top-color: #DC2626; }
.plan-kpi-val { font-size: 1.6rem; font-weight: 700; color: var(--navy); line-height: 1; margin-bottom: 4px; }
.plan-kpi-val.amber { color: #D97706; }
.plan-kpi-val.green { color: #16A34A; }
.plan-kpi-val.red   { color: #DC2626; }
.plan-kpi-lbl { font-size: .65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .06em; font-weight: 600; }

/* Filtros */
.plan-filtros {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); padding: 14px 16px;
    display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;
    margin-bottom: 14px;
}
.filtro-group { display: flex; flex-direction: column; gap: 4px; min-width: 130px; flex: 1; }
.filtro-group label { font-size: .72rem; font-weight: 600; color: var(--text-secondary); }
.filtro-group select,
.filtro-group input {
    padding: 7px 10px; border: 1px solid var(--border);
    border-radius: var(--radius-sm); font-size: .8rem;
    font-family: var(--font); outline: none; background: var(--body-bg);
    color: var(--text-primary);
}
.filtro-group select:focus,
.filtro-group input:focus { border-color: var(--blue-accent); }
.btn-filtrar {
    padding: 8px 16px; background: var(--navy); color: #fff;
    border: none; border-radius: var(--radius-sm); font-size: .8rem;
    font-weight: 600; cursor: pointer; align-self: flex-end; white-space: nowrap;
}
.btn-limpiar {
    padding: 8px 14px; background: transparent; color: var(--text-secondary);
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: .8rem; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; align-self: flex-end; white-space: nowrap;
}

/* Barra de controles: orden + por página */
.plan-controles {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 10px; margin-bottom: 10px;
}
.controles-izq {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.controles-der {
    font-size: .75rem; color: var(--text-muted);
}

/* Botones de orden */
.btn-orden {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 12px; border-radius: var(--radius-sm);
    font-size: .75rem; font-weight: 600; cursor: pointer;
    text-decoration: none; border: 1px solid var(--border);
    background: var(--surface); color: var(--text-secondary);
    transition: all .12s;
}
.btn-orden:hover { border-color: var(--navy); color: var(--navy); }
.btn-orden.activo {
    background: var(--navy); color: #fff; border-color: var(--navy);
}
.btn-orden svg { flex-shrink: 0; }

/* Selector por página */
.select-pagina {
    padding: 5px 8px; border: 1px solid var(--border);
    border-radius: var(--radius-sm); font-size: .75rem;
    font-family: var(--font); background: var(--surface);
    color: var(--text-secondary); cursor: pointer; outline: none;
}

/* Leyenda semáforo */
.semaforo-leyenda {
    display: flex; gap: 14px; align-items: center;
    font-size: .72rem; color: var(--text-muted); flex-wrap: wrap;
}
.leyenda-item { display: flex; align-items: center; gap: 5px; }
.dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.dot-verde    { background: #16A34A; }
.dot-amarillo { background: #D97706; }
.dot-rojo     { background: #DC2626; }
.dot-gris     { background: #94A3B8; }

/* Tabla */
.plan-table-wrap {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); overflow: hidden;
}
.plan-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.plan-table th {
    background: var(--navy); color: #fff;
    padding: 10px 12px; text-align: left;
    font-size: .68rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .05em;
}
.plan-table td {
    padding: 10px 12px; border-bottom: 1px solid var(--border);
    color: var(--text-secondary); vertical-align: middle;
}
.plan-table tr:last-child td { border-bottom: none; }
.plan-table tr:hover td { background: var(--surface-2); }

.semaforo { display: inline-flex; align-items: center; gap: 6px; font-size: .72rem; font-weight: 600; }
.semaforo-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.s-verde    .semaforo-dot { background: #16A34A; }
.s-amarillo .semaforo-dot { background: #D97706; }
.s-rojo     .semaforo-dot { background: #DC2626; }
.s-gris     .semaforo-dot { background: #94A3B8; }
.s-verde    { color: #16A34A; }
.s-amarillo { color: #D97706; }
.s-rojo     { color: #DC2626; }
.s-gris     { color: #94A3B8; }

.estado-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 12px;
    font-size: .68rem; font-weight: 700;
}
.estado-pendiente { background: #FEF3C7; color: #B45309; }
.estado-cerrado   { background: #DCFCE7; color: #15803D; }
.estado-sin       { background: var(--surface-2); color: var(--text-muted); }

.actividad-text {
    max-width: 320px; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
    color: var(--text-primary); font-weight: 500; cursor: pointer;
}
.actividad-text.expandido { white-space: normal; }

.btn-accion {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: var(--radius-sm);
    font-size: .7rem; font-weight: 600; cursor: pointer;
    border: 1px solid; text-decoration: none; transition: all .12s;
}
.btn-edit   { color: var(--navy);   border-color: var(--navy);   background: transparent; }
.btn-cerrar { color: #16A34A;       border-color: #16A34A;       background: transparent; }
.btn-edit:hover   { background: var(--navy);   color: #fff; }
.btn-cerrar:hover { background: #16A34A;       color: #fff; }

.btn-nuevo {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px; background: var(--navy); color: #fff;
    border-radius: var(--radius-sm); font-size: .82rem; font-weight: 600;
    text-decoration: none; transition: background .15s;
}
.btn-nuevo:hover { background: var(--navy-light); }

.fila-vencida td { background: #FFF5F5 !important; }
.fila-vencida:hover td { background: #FEE2E2 !important; }

.alert-ok  { background:#DCFCE7; border-left:3px solid #16A34A; color:#166534; padding:10px 14px; border-radius:var(--radius-sm); font-size:.82rem; margin-bottom:14px; }
.alert-err { background:#FCEBEB; border-left:3px solid #DC2626; color:#991B1B; padding:10px 14px; border-radius:var(--radius-sm); font-size:.82rem; margin-bottom:14px; }

/* ── Paginación ──────────────────────────────────────────────── */
.paginacion-wrap {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px; border-top: 1px solid var(--border);
    flex-wrap: wrap; gap: 10px;
}
.paginacion-info {
    font-size: .75rem; color: var(--text-muted);
}
.paginacion-links {
    display: flex; align-items: center; gap: 4px;
}
.pag-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: var(--radius-sm);
    font-size: .78rem; font-weight: 500; text-decoration: none;
    border: 1px solid var(--border); background: var(--surface);
    color: var(--text-secondary); transition: all .12s;
}
.pag-btn:hover:not(.disabled):not(.activo) {
    border-color: var(--navy); color: var(--navy);
}
.pag-btn.activo {
    background: var(--navy); color: #fff; border-color: var(--navy); font-weight: 700;
}
.pag-btn.disabled {
    opacity: .35; cursor: not-allowed; pointer-events: none;
}
.pag-dots {
    width: 32px; text-align: center; font-size: .78rem; color: var(--text-muted);
}

/* Responsive */
@media (max-width: 1024px) {
    .plan-kpis { grid-template-columns: repeat(2, 1fr); }
    .plan-table th:nth-child(5),
    .plan-table td:nth-child(5),
    .plan-table th:nth-child(6),
    .plan-table td:nth-child(6) { display: none; }
}
@media (max-width: 640px) {
    .plan-body { padding: 12px; }
    .plan-kpis { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .plan-table th:nth-child(3),
    .plan-table td:nth-child(3) { display: none; }
    .actividad-text { max-width: 160px; }
    .controles-izq { gap: 6px; }
}
</style>
@endpush

@section('content')
<div class="plan-body">

    {{-- Header --}}
    <div class="plan-header">
        <div>
            <h2>📋 Planificación</h2>
            <p>
                @if($esAdmin) Todas las planificaciones del sistema
                @else Planificaciones de tus áreas asignadas
                @endif
                · {{ number_format($stats['total']) }} {{ $stats['total'] === 1 ? 'registro' : 'registros' }}
            </p>
        </div>
        @if($esAdmin)
        <a href="{{ route('planificacion.create') }}" class="btn-nuevo">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nueva planificación
        </a>
        @endif
    </div>

    @if(session('ok'))
        <div class="alert-ok">✅ {{ session('ok') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-err">❌ {{ $errors->first() }}</div>
    @endif

    {{-- KPIs --}}
    <div class="plan-kpis">
        <div class="plan-kpi">
            <div class="plan-kpi-val">{{ $stats['total'] }}</div>
            <div class="plan-kpi-lbl">Total planificaciones</div>
        </div>
        <div class="plan-kpi pendiente">
            <div class="plan-kpi-val amber">{{ $stats['pendientes'] }}</div>
            <div class="plan-kpi-lbl">Pendientes</div>
        </div>
        <div class="plan-kpi cerrada">
            <div class="plan-kpi-val green">{{ $stats['cerradas'] }}</div>
            <div class="plan-kpi-lbl">Cerradas</div>
        </div>
        <div class="plan-kpi vencida">
            <div class="plan-kpi-val red">{{ $stats['vencidas'] }}</div>
            <div class="plan-kpi-lbl">Vencidas</div>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('planificacion.index') }}" id="form-filtros">
        {{-- Preservar orden y por_pagina al filtrar --}}
        <input type="hidden" name="orden"     value="{{ $orden }}">
        <input type="hidden" name="por_pagina" value="{{ $porPagina }}">

        <div class="plan-filtros">
            <div class="filtro-group" style="flex:2;min-width:200px">
                <label>Buscar actividad o responsable</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       placeholder="Escribe para buscar...">
            </div>
            @if(count($areasParaFiltro) > 1)
            <div class="filtro-group">
                <label>Área</label>
                <select name="area">
                    <option value="">Todas las áreas</option>
                    @foreach($areasParaFiltro as $id => $nombre)
                        <option value="{{ $id }}" {{ request('area') == $id ? 'selected' : '' }}>
                            {{ $nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="filtro-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Pendiente</option>
                    <option value="2" {{ request('estado') == '2' ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>
            <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
            <a href="{{ route('planificacion.index') }}" class="btn-limpiar">✕ Limpiar</a>
        </div>
    </form>

    {{-- Controles: orden + por página + leyenda --}}
    <div class="plan-controles">
        <div class="controles-izq">
            {{-- Orden --}}
            <span style="font-size:.72rem;font-weight:600;color:var(--text-secondary)">Orden:</span>
            <a href="{{ request()->fullUrlWithQuery(['orden' => 'desc', 'page' => 1]) }}"
               class="btn-orden {{ $orden === 'desc' ? 'activo' : '' }}">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <polyline points="19 12 12 19 5 12"/>
                </svg>
                Más recientes primero
            </a>
            <a href="{{ request()->fullUrlWithQuery(['orden' => 'asc', 'page' => 1]) }}"
               class="btn-orden {{ $orden === 'asc' ? 'activo' : '' }}">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="19" x2="12" y2="5"/>
                    <polyline points="5 12 12 5 19 12"/>
                </svg>
                Más antiguas primero
            </a>

            {{-- Por página --}}
            <span style="font-size:.72rem;font-weight:600;color:var(--text-secondary);margin-left:8px">Mostrar:</span>
            <select class="select-pagina" onchange="cambiarPorPagina(this.value)">
                <option value="10"  {{ $porPagina == 10  ? 'selected' : '' }}>10</option>
                <option value="20"  {{ $porPagina == 20  ? 'selected' : '' }}>20</option>
                <option value="50"  {{ $porPagina == 50  ? 'selected' : '' }}>50</option>
            </select>
        </div>

        <div class="semaforo-leyenda">
            <div class="leyenda-item"><div class="dot dot-verde"></div> A tiempo</div>
            <div class="leyenda-item"><div class="dot dot-amarillo"></div> Por vencer</div>
            <div class="leyenda-item"><div class="dot dot-rojo"></div> Vencida</div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="plan-table-wrap">
        <table class="plan-table">
            <thead>
                <tr>
                    <th style="width:34px"></th>
                    <th>Actividad</th>
                    <th>Área</th>
                    <th>Responsable</th>
                    <th>Inicio</th>
                    <th>Término</th>
                    <th>Estado</th>
                    <th style="width:130px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($planificaciones as $p)
                @php
                    $filaClass = $p->semaforo === 'rojo' && (int)$p->id_estado === 1 ? 'fila-vencida' : '';
                    $diasTexto = match(true) {
                        $p->dias_restantes === null  => '—',
                        (int)$p->id_estado === 2     => 'Cerrada',
                        $p->dias_restantes < 0        => abs($p->dias_restantes) . 'd vencida',
                        $p->dias_restantes === 0      => 'Vence hoy',
                        $p->dias_restantes === 1      => 'Mañana',
                        default                       => $p->dias_restantes . 'd restantes',
                    };
                @endphp
                <tr class="{{ $filaClass }}">
                    <td style="text-align:center">
                        <div class="semaforo s-{{ $p->semaforo }}">
                            <div class="semaforo-dot"></div>
                        </div>
                    </td>
                    <td>
                        <div class="actividad-text" onclick="expandirTexto(this)"
                             title="{{ $p->actividades }}">
                            {{ $p->actividades }}
                        </div>
                        @if($p->observaciones)
                        <div style="font-size:.68rem;color:var(--text-muted);margin-top:2px">
                            💬 {{ Str::limit($p->observaciones, 60) }}
                        </div>
                        @endif
                    </td>
                    <td style="font-size:.75rem">{{ $p->area_nombre }}</td>
                    <td style="font-size:.75rem;white-space:nowrap">{{ $p->responsable }}</td>
                    <td style="font-size:.75rem;white-space:nowrap">
                        {{ $p->inicio ? \Carbon\Carbon::parse($p->inicio)->format('d/m/Y') : '—' }}
                    </td>
                    <td style="white-space:nowrap">
                        <div style="font-size:.75rem">
                            {{ $p->termino ? \Carbon\Carbon::parse($p->termino)->format('d/m/Y') : '—' }}
                        </div>
                        <div style="font-size:.65rem;font-weight:600;color:{{ $p->semaforo === 'rojo' ? '#DC2626' : ($p->semaforo === 'amarillo' ? '#D97706' : 'var(--text-muted)') }}">
                            {{ $diasTexto }}
                        </div>
                    </td>
                    <td>
                        @php
                            $badgeClass = match((int)$p->id_estado) {
                                1 => 'estado-pendiente',
                                2 => 'estado-cerrado',
                                default => 'estado-sin',
                            };
                        @endphp
                        <span class="estado-badge {{ $badgeClass }}">
                            {{ $p->estado_nombre }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:5px;align-items:center">
                            @if(in_array((int)$p->area, $areasConEdicion, true))
                            <a href="{{ route('planificacion.edit', $p->id) }}"
                               class="btn-accion btn-edit">✏️</a>
                            @endif
                            @if($esAdmin && (int)$p->id_estado !== 2)
                            <form method="POST" action="{{ route('planificacion.cerrar', $p->id) }}"
                                  onsubmit="return confirm('¿Cerrar esta planificación?')">
                                @csrf
                                <button type="submit" class="btn-accion btn-cerrar"
                                        title="Cerrar planificación">✓</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">
                        <div style="font-size:2rem;margin-bottom:8px">📋</div>
                        No se encontraron planificaciones con los filtros seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Paginación --}}
        @if($planificaciones->lastPage() > 1)
        <div class="paginacion-wrap">
            <div class="paginacion-info">
                Mostrando {{ $planificaciones->firstItem() }}–{{ $planificaciones->lastItem() }}
                de {{ number_format($planificaciones->total()) }} planificaciones
            </div>

            <div class="paginacion-links">
                {{-- Anterior --}}
                <a href="{{ $planificaciones->previousPageUrl() ?? '#' }}"
                   class="pag-btn {{ ! $planificaciones->onFirstPage() ?: 'disabled' }}"
                   title="Página anterior">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </a>

                {{-- Números --}}
                @php
                    $current  = $planificaciones->currentPage();
                    $last     = $planificaciones->lastPage();
                    $pages    = [];
                    // Siempre mostrar: 1, last, y ventana alrededor de current
                    for ($i = 1; $i <= $last; $i++) {
                        if ($i === 1 || $i === $last || abs($i - $current) <= 2) {
                            $pages[] = $i;
                        }
                    }
                    $pages = array_unique($pages);
                    sort($pages);
                @endphp

                @php $prev = null; @endphp
                @foreach($pages as $page)
                    @if($prev !== null && $page - $prev > 1)
                        <span class="pag-dots">…</span>
                    @endif
                    <a href="{{ $planificaciones->url($page) }}"
                       class="pag-btn {{ $page === $current ? 'activo' : '' }}">
                        {{ $page }}
                    </a>
                    @php $prev = $page; @endphp
                @endforeach

                {{-- Siguiente --}}
                <a href="{{ $planificaciones->nextPageUrl() ?? '#' }}"
                   class="pag-btn {{ $planificaciones->hasMorePages() ? '' : 'disabled' }}"
                   title="Página siguiente">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </a>
            </div>
        </div>
        @else
        {{-- Info de registros aunque sea una sola página --}}
        <div class="paginacion-wrap">
            <div class="paginacion-info">
                {{ $planificaciones->total() }} {{ $planificaciones->total() === 1 ? 'planificación' : 'planificaciones' }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function expandirTexto(el) {
    el.classList.toggle('expandido');
}

// Cambiar cantidad por página manteniendo todos los parámetros actuales
function cambiarPorPagina(valor) {
    var url = new URL(window.location.href);
    url.searchParams.set('por_pagina', valor);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}
</script>
@endpush