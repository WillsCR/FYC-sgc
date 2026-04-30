@extends('layouts.app')
@section('title', 'Minutas')

@push('styles')
<style>
.min-body { padding: 20px; max-width: 1400px; margin: 0 auto; }

/* Header */
.min-header {
    display: flex; align-items: flex-start;
    justify-content: space-between; flex-wrap: wrap;
    gap: 12px; margin-bottom: 20px;
}
.min-header h2 { font-size: 1.1rem; color: var(--navy); margin-bottom: 2px; }
.min-header p  { font-size: .78rem; color: var(--text-muted); }

/* Filtros */
.min-filtros {
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

/* Controles */
.min-controles {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 10px; margin-bottom: 10px;
}
.controles-izq { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.btn-orden {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 12px; border-radius: var(--radius-sm);
    font-size: .75rem; font-weight: 600; cursor: pointer;
    text-decoration: none; border: 1px solid var(--border);
    background: var(--surface); color: var(--text-secondary);
    transition: all .12s;
}
.btn-orden:hover { border-color: var(--navy); color: var(--navy); }
.btn-orden.activo { background: var(--navy); color: #fff; border-color: var(--navy); }
.select-pagina {
    padding: 5px 8px; border: 1px solid var(--border);
    border-radius: var(--radius-sm); font-size: .75rem;
    font-family: var(--font); background: var(--surface);
    color: var(--text-secondary); cursor: pointer; outline: none;
}

/* Tabla */
.min-table-wrap {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); overflow: hidden;
}
.min-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.min-table th {
    background: var(--navy); color: #fff;
    padding: 10px 12px; text-align: left;
    font-size: .68rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .05em;
}
.min-table td {
    padding: 10px 12px; border-bottom: 1px solid var(--border);
    color: var(--text-secondary); vertical-align: middle;
}
.min-table tr:last-child td { border-bottom: none; }
.min-table tr:hover td { background: var(--surface-2); }

/* Badge área */
.area-badge {
    display: inline-flex; align-items: center;
    padding: 3px 8px; border-radius: 10px;
    font-size: .67rem; font-weight: 700;
    background: #EFF6FF; color: #1D4ED8;
    white-space: nowrap;
}

/* Badge compromisos */
.comp-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: .72rem; font-weight: 600;
}
.comp-badge .comp-abiertos { color: #D97706; }
.comp-badge .comp-total    { color: var(--text-muted); }

/* Tipo reunión pill */
.tipo-pill {
    display: inline-block; padding: 2px 8px;
    border-radius: 10px; font-size: .67rem; font-weight: 600;
    background: var(--surface-2); color: var(--text-secondary);
    text-transform: capitalize;
}

/* Botones acción */
.btn-accion {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: var(--radius-sm);
    font-size: .7rem; font-weight: 600; cursor: pointer;
    border: 1px solid; text-decoration: none; transition: all .12s;
}
.btn-ver    { color: #0369A1; border-color: #0369A1; background: transparent; }
.btn-edit   { color: var(--navy); border-color: var(--navy); background: transparent; }
.btn-del    { color: #DC2626; border-color: #DC2626; background: transparent; }
.btn-ver:hover  { background: #0369A1; color: #fff; }
.btn-edit:hover { background: var(--navy); color: #fff; }
.btn-del:hover  { background: #DC2626; color: #fff; }

.btn-nuevo {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px; background: var(--navy); color: #fff;
    border-radius: var(--radius-sm); font-size: .82rem; font-weight: 600;
    text-decoration: none; transition: background .15s;
}
.btn-nuevo:hover { background: var(--navy-light); }

/* Alerts */
.alert-ok  { background:#DCFCE7; border-left:3px solid #16A34A; color:#166534; padding:10px 14px; border-radius:var(--radius-sm); font-size:.82rem; margin-bottom:14px; }
.alert-err { background:#FCEBEB; border-left:3px solid #DC2626; color:#991B1B; padding:10px 14px; border-radius:var(--radius-sm); font-size:.82rem; margin-bottom:14px; }

/* Paginación */
.paginacion-wrap {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px; border-top: 1px solid var(--border);
    flex-wrap: wrap; gap: 10px;
}
.paginacion-info { font-size: .75rem; color: var(--text-muted); }
.paginacion-links { display: flex; align-items: center; gap: 4px; }
.pag-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: var(--radius-sm);
    font-size: .78rem; font-weight: 500; text-decoration: none;
    border: 1px solid var(--border); background: var(--surface);
    color: var(--text-secondary); transition: all .12s;
}
.pag-btn:hover:not(.disabled):not(.activo) { border-color: var(--navy); color: var(--navy); }
.pag-btn.activo { background: var(--navy); color: #fff; border-color: var(--navy); font-weight: 700; }
.pag-btn.disabled { opacity: .35; cursor: not-allowed; pointer-events: none; }
.pag-dots { width: 32px; text-align: center; font-size: .78rem; color: var(--text-muted); }

/* Responsive */
@media (max-width: 1024px) {
    .min-table th:nth-child(4),
    .min-table td:nth-child(4),
    .min-table th:nth-child(5),
    .min-table td:nth-child(5) { display: none; }
}
@media (max-width: 640px) {
    .min-body { padding: 12px; }
    .min-table th:nth-child(3),
    .min-table td:nth-child(3) { display: none; }
}
</style>
@endpush

@section('content')
<div class="min-body">

    {{-- Header --}}
    <div class="min-header">
        <div>
            <h2>🗒️ Minutas</h2>
            <p>
                @if($esAdmin) Todas las minutas del sistema
                @else Minutas de tus áreas asignadas
                @endif
                · {{ number_format($total) }} {{ $total === 1 ? 'registro' : 'registros' }}
            </p>
        </div>
        @if($esAdmin || count($areasConEdicion) > 0)
        <a href="{{ route('minutas.create') }}" class="btn-nuevo">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nueva minuta
        </a>
        @endif
    </div>

    @if(session('ok'))
        <div class="alert-ok">✅ {{ session('ok') }}</div>
    @endif

    {{-- Filtros --}}
    <form method="GET" action="{{ route('minutas.index') }}" id="form-filtros">
        <input type="hidden" name="orden"      value="{{ $orden }}">
        <input type="hidden" name="por_pagina" value="{{ $porPagina }}">

        <div class="min-filtros">
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
                <label>Tipo de reunión</label>
                <input type="text" name="tipo_reunion"
                       value="{{ request('tipo_reunion') }}"
                       placeholder="Online, Presencial...">
            </div>

            <div class="filtro-group" style="min-width:130px;max-width:160px">
                <label>Fecha desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}">
            </div>

            <div class="filtro-group" style="min-width:130px;max-width:160px">
                <label>Fecha hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}">
            </div>

            <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
            <a href="{{ route('minutas.index') }}" class="btn-limpiar">✕ Limpiar</a>
        </div>
    </form>

    {{-- Controles orden + por página --}}
    <div class="min-controles">
        <div class="controles-izq">
            <span style="font-size:.72rem;font-weight:600;color:var(--text-secondary)">Orden:</span>
            <a href="{{ request()->fullUrlWithQuery(['orden' => 'desc', 'page' => 1]) }}"
               class="btn-orden {{ $orden === 'desc' ? 'activo' : '' }}">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/>
                </svg>
                Más recientes primero
            </a>
            <a href="{{ request()->fullUrlWithQuery(['orden' => 'asc', 'page' => 1]) }}"
               class="btn-orden {{ $orden === 'asc' ? 'activo' : '' }}">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>
                </svg>
                Más antiguas primero
            </a>
            <span style="font-size:.72rem;font-weight:600;color:var(--text-secondary);margin-left:8px">Mostrar:</span>
            <select class="select-pagina" onchange="cambiarPorPagina(this.value)">
                <option value="10"  {{ $porPagina == 10  ? 'selected' : '' }}>10</option>
                <option value="20"  {{ $porPagina == 20  ? 'selected' : '' }}>20</option>
                <option value="50"  {{ $porPagina == 50  ? 'selected' : '' }}>50</option>
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="min-table-wrap">
        <table class="min-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Área</th>
                    <th>Tipo Reunión</th>
                    <th>Lugar</th>
                    <th>Empresa</th>
                    <th>Compromisos</th>
                    <th>Próxima Reunión</th>
                    <th style="width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($minutas as $m)
                <tr>
                    <td style="white-space:nowrap;font-size:.75rem">
                        {{ $m->fecha ? \Carbon\Carbon::parse($m->fecha)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        <span class="area-badge">{{ $m->area_nombre }}</span>
                    </td>
                    <td>
                        <span class="tipo-pill">{{ $m->tipo_reunion ?: '—' }}</span>
                    </td>
                    <td style="font-size:.75rem">{{ $m->lugar ?: '—' }}</td>
                    <td style="font-size:.75rem">{{ $m->empresa ?: '—' }}</td>
                    <td>
                        @if($m->total_compromisos > 0)
                        <span class="comp-badge">
                            @if($m->compromisos_abiertos > 0)
                                <span class="comp-abiertos">{{ $m->compromisos_abiertos }} abiertos</span>
                                <span class="comp-total">/ {{ $m->total_compromisos }}</span>
                            @else
                                <span style="color:#16A34A;font-weight:700">✓ {{ $m->total_compromisos }} cerrados</span>
                            @endif
                        </span>
                        @else
                            <span style="font-size:.72rem;color:var(--text-muted)">Sin compromisos</span>
                        @endif
                    </td>
                    <td style="font-size:.75rem;white-space:nowrap">
                        {{ $m->proxima_reunion ? \Carbon\Carbon::parse($m->proxima_reunion)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        <div style="display:flex;gap:5px;align-items:center;flex-wrap:wrap">
                            <a href="{{ route('minutas.show', $m->id) }}"
                               class="btn-accion btn-ver" title="Ver detalle">👁️</a>

                            @if(in_array((int)$m->id_area, $areasConEdicion, true))
                            <a href="{{ route('minutas.edit', $m->id) }}"
                               class="btn-accion btn-edit" title="Editar">✏️</a>
                            @endif

                            @if($esAdmin)
                            <form method="POST" action="{{ route('minutas.destroy', $m->id) }}"
                                  onsubmit="return confirm('¿Eliminar esta minuta y todos sus compromisos?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion btn-del" title="Eliminar">🗑️</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">
                        <div style="font-size:2rem;margin-bottom:8px">🗒️</div>
                        No se encontraron minutas con los filtros seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Paginación --}}
        @if($minutas->lastPage() > 1)
        <div class="paginacion-wrap">
            <div class="paginacion-info">
                Mostrando {{ $minutas->firstItem() }}–{{ $minutas->lastItem() }}
                de {{ number_format($minutas->total()) }} minutas
            </div>
            <div class="paginacion-links">
                <a href="{{ $minutas->previousPageUrl() ?? '#' }}"
                   class="pag-btn {{ ! $minutas->onFirstPage() ?: 'disabled' }}">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </a>
                @php
                    $current = $minutas->currentPage();
                    $last    = $minutas->lastPage();
                    $pages   = [];
                    for ($i = 1; $i <= $last; $i++) {
                        if ($i === 1 || $i === $last || abs($i - $current) <= 2) $pages[] = $i;
                    }
                    $pages = array_unique($pages); sort($pages);
                @endphp
                @php $prev = null; @endphp
                @foreach($pages as $page)
                    @if($prev !== null && $page - $prev > 1)
                        <span class="pag-dots">…</span>
                    @endif
                    <a href="{{ $minutas->url($page) }}"
                       class="pag-btn {{ $page === $current ? 'activo' : '' }}">{{ $page }}</a>
                    @php $prev = $page; @endphp
                @endforeach
                <a href="{{ $minutas->nextPageUrl() ?? '#' }}"
                   class="pag-btn {{ $minutas->hasMorePages() ? '' : 'disabled' }}">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </a>
            </div>
        </div>
        @else
        <div class="paginacion-wrap">
            <div class="paginacion-info">
                {{ $minutas->total() }} {{ $minutas->total() === 1 ? 'minuta' : 'minutas' }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function cambiarPorPagina(valor) {
    var url = new URL(window.location.href);
    url.searchParams.set('por_pagina', valor);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}
</script>
@endpush
