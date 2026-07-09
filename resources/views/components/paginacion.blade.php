{{--
    Componente de paginación uniforme.
    Uso: <x-paginacion :paginator="$registros" label="registros" />
--}}
@props(['paginator', 'label' => 'registros'])

<style>
.paginacion-wrap {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px; border-top: 1px solid #e5e7eb;
    flex-wrap: wrap; gap: 10px;
}
.paginacion-info { font-size: .75rem; color: #6b7280; }
.paginacion-links { display: flex; align-items: center; gap: 4px; }
.pag-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 6px;
    font-size: .78rem; font-weight: 500; text-decoration: none;
    border: 1px solid #e5e7eb; background: #fff;
    color: #374151; transition: all .12s;
}
.pag-btn:hover:not(.pag-disabled):not(.pag-activo) { border-color: #0D2B5E; color: #0D2B5E; }
.pag-btn.pag-activo { background: #0D2B5E; color: #fff; border-color: #0D2B5E; font-weight: 700; }
.pag-btn.pag-disabled { opacity: .35; cursor: not-allowed; pointer-events: none; }
.pag-dots { width: 32px; text-align: center; font-size: .78rem; color: #6b7280; }
</style>

@php
    $current = $paginator->currentPage();
    $last    = $paginator->lastPage();
    $total   = $paginator->total();
    $from    = $paginator->firstItem();
    $to      = $paginator->lastItem();

    $pages = [];
    for ($i = 1; $i <= $last; $i++) {
        if ($i === 1 || $i === $last || abs($i - $current) <= 2) {
            $pages[] = $i;
        }
    }
    $pages = array_unique($pages);
    sort($pages);
@endphp

<div class="paginacion-wrap">
    <div class="paginacion-info">
        @if($from && $to)
            Mostrando {{ $from }}–{{ $to }} de {{ number_format($total) }} {{ $label }}
        @else
            {{ number_format($total) }} {{ $label }}
        @endif
    </div>

    @if($last > 1)
    <div class="paginacion-links">
        {{-- Anterior --}}
        <a href="{{ $paginator->previousPageUrl() ?? '#' }}"
           class="pag-btn {{ $paginator->onFirstPage() ? 'pag-disabled' : '' }}"
           title="Página anterior">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </a>

        {{-- Números con puntos suspensivos --}}
        @php $prev = null; @endphp
        @foreach($pages as $page)
            @if($prev !== null && $page - $prev > 1)
                <span class="pag-dots">…</span>
            @endif
            <a href="{{ $paginator->url($page) }}"
               class="pag-btn {{ $page === $current ? 'pag-activo' : '' }}">
                {{ $page }}
            </a>
            @php $prev = $page; @endphp
        @endforeach

        {{-- Siguiente --}}
        <a href="{{ $paginator->nextPageUrl() ?? '#' }}"
           class="pag-btn {{ $paginator->hasMorePages() ? '' : 'pag-disabled' }}"
           title="Página siguiente">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M9 18l6-6-6-6"/>
            </svg>
        </a>
    </div>
    @endif
</div>
