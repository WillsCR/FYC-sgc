<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Métricas SGC — F&amp;C Chile SPA</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 9pt;
        color: #1e293b;
        background: #ffffff;
    }

    /* ── Encabezado ── */
    .header {
        background: #0D2B5E;
        color: #ffffff;
        padding: 18px 24px 14px;
        margin-bottom: 0;
    }
    .header-top {
        display: table;
        width: 100%;
        margin-bottom: 6px;
    }
    .header-logo {
        display: table-cell;
        vertical-align: middle;
        width: 60%;
    }
    .header-meta {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
        width: 40%;
        font-size: 7.5pt;
        color: #90B4E8;
    }
    .header-empresa {
        font-size: 15pt;
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    .header-subtitulo {
        font-size: 8pt;
        color: #90B4E8;
        margin-top: 2px;
    }
    .header-divider {
        border: none;
        border-top: 1px solid rgba(255,255,255,0.2);
        margin: 10px 0 8px;
    }
    .header-titulo-reporte {
        font-size: 11pt;
        font-weight: bold;
        color: #ffffff;
        letter-spacing: 0.3px;
    }

    /* ── Bandas de sección ── */
    .section-title {
        background: #1E4D9B;
        color: #ffffff;
        padding: 6px 12px;
        font-size: 8.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 14px;
        margin-bottom: 0;
    }

    /* ── KPI Cards ── */
    .kpi-grid {
        display: table;
        width: 100%;
        margin-top: 10px;
        margin-bottom: 4px;
        border-spacing: 0;
    }
    .kpi-row {
        display: table-row;
    }
    .kpi-card {
        display: table-cell;
        width: 25%;
        padding: 4px;
        vertical-align: top;
    }
    .kpi-inner {
        border: 1px solid #DCE5F0;
        border-top: 3px solid #1D6FD9;
        border-radius: 4px;
        padding: 10px 10px 8px;
        background: #FAFCFF;
        text-align: center;
    }
    .kpi-inner.verde  { border-top-color: #16A34A; }
    .kpi-inner.rojo   { border-top-color: #DC2626; }
    .kpi-inner.naranja{ border-top-color: #D97706; }
    .kpi-valor {
        font-size: 20pt;
        font-weight: bold;
        color: #0D2B5E;
        line-height: 1;
    }
    .kpi-valor.verde  { color: #16A34A; }
    .kpi-valor.rojo   { color: #DC2626; }
    .kpi-valor.naranja{ color: #D97706; }
    .kpi-label {
        font-size: 6.5pt;
        color: #6B7A99;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }
    .kpi-badge {
        display: inline-block;
        margin-top: 5px;
        padding: 1px 7px;
        border-radius: 8px;
        font-size: 6pt;
        font-weight: bold;
        background: #EFF6FF;
        color: #0D2B5E;
    }
    .kpi-badge.verde  { background: #DCFCE7; color: #15803D; }
    .kpi-badge.rojo   { background: #FCEBEB; color: #991B1B; }
    .kpi-badge.naranja{ background: #FEF3C7; color: #92400E; }

    /* ── Tablas de datos ── */
    table.data {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1px;
        font-size: 8pt;
    }
    table.data thead tr {
        background: #0D2B5E;
        color: #ffffff;
    }
    table.data thead th {
        padding: 6px 10px;
        text-align: left;
        font-size: 7pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    table.data thead th.center { text-align: center; }
    table.data tbody tr:nth-child(even) { background: #F8FAFC; }
    table.data tbody tr:nth-child(odd)  { background: #ffffff; }
    table.data tbody td {
        padding: 5px 10px;
        border-bottom: 1px solid #EFF6FF;
        color: #334155;
        vertical-align: middle;
    }
    table.data tbody td.center { text-align: center; }
    table.data tfoot tr {
        background: #1E3A5F;
        color: #ffffff;
    }
    table.data tfoot td {
        padding: 6px 10px;
        font-weight: bold;
        font-size: 8pt;
        border: none;
    }
    table.data tfoot td.center { text-align: center; }

    /* Badges de estado */
    .badge {
        display: inline-block;
        padding: 1px 8px;
        border-radius: 8px;
        font-size: 6.5pt;
        font-weight: bold;
    }
    .badge-ok      { background: #DCFCE7; color: #15803D; }
    .badge-warning { background: #FEF3C7; color: #92400E; }
    .badge-danger  { background: #FCEBEB; color: #991B1B; }

    /* Barra de progreso */
    .bar-wrap {
        background: #E2E8F0;
        border-radius: 3px;
        height: 6px;
        width: 80px;
        display: inline-block;
        vertical-align: middle;
        margin-right: 4px;
    }
    .bar-fill {
        height: 6px;
        border-radius: 3px;
        background: #1D6FD9;
    }
    .bar-fill.verde  { background: #16A34A; }
    .bar-fill.naranja{ background: #D97706; }
    .bar-fill.rojo   { background: #DC2626; }

    /* Minutas */
    .mes-dot {
        display: inline-block;
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #1D6FD9;
        margin-right: 5px;
        vertical-align: middle;
    }

    /* ── Pie de página ── */
    .footer {
        margin-top: 18px;
        border-top: 1px solid #DCE5F0;
        padding-top: 8px;
        display: table;
        width: 100%;
    }
    .footer-left  { display: table-cell; font-size: 6.5pt; color: #94A3B8; width: 50%; }
    .footer-right { display: table-cell; font-size: 6.5pt; color: #94A3B8; text-align: right; width: 50%; }

    /* ── Gráficos ── */
    .grafico-wrap {
        text-align: center;
        margin: 10px 0 4px;
        padding: 10px;
        background: #FAFCFF;
        border: 1px solid #DCE5F0;
        border-radius: 4px;
    }
    .grafico-wrap img {
        max-width: 100%;
        height: auto;
    }
    .graficos-row {
        display: table;
        width: 100%;
        margin: 10px 0 4px;
        border-spacing: 8px;
    }
    .grafico-col {
        display: table-cell;
        vertical-align: top;
        padding: 10px;
        background: #FAFCFF;
        border: 1px solid #DCE5F0;
        border-radius: 4px;
        text-align: center;
    }
    .grafico-col img { max-width: 100%; height: auto; }
    .grafico-titulo {
        font-size: 7pt;
        font-weight: bold;
        color: #6B7A99;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    /* ── Página break ── */
    .page-break { page-break-before: always; }

    /* Nota informativa */
    .nota {
        background: #EFF6FF;
        border-left: 3px solid #1D6FD9;
        padding: 6px 10px;
        font-size: 7.5pt;
        color: #334155;
        margin-top: 10px;
        border-radius: 2px;
    }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════
     ENCABEZADO
══════════════════════════════════════════════════════════ --}}
<div class="header">
    <div class="header-top">
        <div class="header-logo">
            <div class="header-empresa">F&amp;C Chile SPA</div>
            <div class="header-subtitulo">Sistema de Gestión y Control — SGC</div>
        </div>
        <div class="header-meta">
            Generado el {{ \Carbon\Carbon::now()->format('d/m/Y') }}<br>
            {{ \Carbon\Carbon::now()->format('H:i') }} hrs
        </div>
    </div>
    <hr class="header-divider">
    <div class="header-titulo-reporte">Reporte de Métricas — {{ \Carbon\Carbon::now()->format('F Y') }}</div>
</div>

{{-- ══════════════════════════════════════════════════════════
     KPIs GLOBALES
══════════════════════════════════════════════════════════ --}}
<div class="section-title">📊 Indicadores Clave de Desempeño</div>

@php
    $cumplColor = $stats['cumplimiento'] >= 80 ? 'verde' : ($stats['cumplimiento'] >= 60 ? 'naranja' : 'rojo');
    $cumplBadge = $stats['cumplimiento'] >= 80 ? 'Óptimo' : ($stats['cumplimiento'] >= 60 ? 'Regular' : 'Crítico');
    $pendColor  = $stats['pendientes'] > 5 ? 'rojo' : ($stats['pendientes'] > 2 ? 'naranja' : 'verde');
@endphp

<div class="kpi-grid">
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-inner {{ $cumplColor }}">
                <div class="kpi-valor {{ $cumplColor }}">{{ $stats['cumplimiento'] }}%</div>
                <div class="kpi-label">Cumplimiento Global</div>
                <div class="kpi-badge {{ $cumplColor }}">{{ $cumplBadge }}</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-inner verde">
                <div class="kpi-valor verde">{{ $stats['cerradas'] }}</div>
                <div class="kpi-label">Actividades Cerradas</div>
                <div class="kpi-badge verde">Completadas</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-inner {{ $pendColor }}">
                <div class="kpi-valor {{ $pendColor }}">{{ $stats['pendientes'] }}</div>
                <div class="kpi-label">Actividades Pendientes</div>
                <div class="kpi-badge {{ $pendColor }}">{{ $stats['pendientes'] > 5 ? 'Crítico' : 'Normal' }}</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-inner">
                <div class="kpi-valor">{{ $stats['totalPlan'] }}</div>
                <div class="kpi-label">Total Planificaciones</div>
                <div class="kpi-badge">Registradas</div>
            </div>
        </div>
    </div>
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-inner">
                <div class="kpi-valor">{{ $stats['minutasMes'] }}</div>
                <div class="kpi-label">Minutas este mes</div>
                <div class="kpi-badge">{{ \Carbon\Carbon::now()->format('M Y') }}</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-inner">
                <div class="kpi-valor">{{ $stats['minutasAnio'] }}</div>
                <div class="kpi-label">Minutas {{ now()->year }}</div>
                <div class="kpi-badge">Año en curso</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-inner">
                <div class="kpi-valor">{{ $stats['documentos'] }}</div>
                <div class="kpi-label">Documentos Activos</div>
                <div class="kpi-badge">En sistema</div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-inner">
                <div class="kpi-valor">{{ $stats['totalPlan'] > 0 ? number_format($stats['cerradas'] / $stats['totalPlan'] * 100, 1) : '0.0' }}%</div>
                <div class="kpi-label">Tasa de Cierre</div>
                <div class="kpi-badge">Eficiencia</div>
            </div>
        </div>
    </div>
</div>

{{-- Gráficos de KPIs: barras + donut lado a lado --}}
@if($imgArea || $imgDonut)
<div class="graficos-row">
    @if($imgArea)
    <div class="grafico-col" style="width:63%">
        <div class="grafico-titulo">Cumplimiento por Área</div>
        <img src="{{ $imgArea }}" alt="Gráfico de barras por área">
    </div>
    @endif
    @if($imgDonut)
    <div class="grafico-col" style="width:33%">
        <div class="grafico-titulo">Estado Global</div>
        <img src="{{ $imgDonut }}" alt="Gráfico donut">
    </div>
    @endif
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     CUMPLIMIENTO POR ÁREA
══════════════════════════════════════════════════════════ --}}
<div class="section-title">📋 Cumplimiento por Área — Detalle</div>

@if(count($graficoPorArea['labels']) > 0)
<table class="data">
    <thead>
        <tr>
            <th style="width:30%">Área</th>
            <th class="center" style="width:10%">Total</th>
            <th class="center" style="width:10%">Cerradas</th>
            <th class="center" style="width:10%">Pendientes</th>
            <th style="width:25%">Progreso</th>
            <th class="center" style="width:15%">Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($graficoPorArea['labels'] as $i => $area)
        @php
            $pct       = $graficoPorArea['cumplimiento'][$i];
            $cerr      = $graficoPorArea['cerradasPorArea'][$i];
            $pend      = $graficoPorArea['pendientesPorArea'][$i];
            $tot       = $graficoPorArea['totales'][$i];
            $color     = $pct >= 80 ? 'verde' : ($pct >= 60 ? 'naranja' : 'rojo');
            $estado    = $pct >= 80 ? 'Óptimo' : ($pct >= 60 ? 'Regular' : 'Crítico');
            $badgeClass= $pct >= 80 ? 'badge-ok' : ($pct >= 60 ? 'badge-warning' : 'badge-danger');
            $barWidth  = min(100, $pct);
        @endphp
        <tr>
            <td><strong>{{ $area }}</strong></td>
            <td class="center">{{ $tot }}</td>
            <td class="center" style="color:#16A34A;font-weight:bold">{{ $cerr }}</td>
            <td class="center" style="color:{{ $pend > 0 ? '#DC2626' : '#16A34A' }};font-weight:bold">{{ $pend }}</td>
            <td>
                <div class="bar-wrap">
                    <div class="bar-fill {{ $color }}" style="width:{{ $barWidth }}%"></div>
                </div>
                <span style="font-weight:bold;color:{{ $pct >= 80 ? '#16A34A' : ($pct >= 60 ? '#D97706' : '#DC2626') }}">{{ $pct }}%</span>
            </td>
            <td class="center"><span class="badge {{ $badgeClass }}">{{ $estado }}</span></td>
        </tr>
        @endforeach
    </tbody>
    @php
        $totGlobal  = array_sum($graficoPorArea['totales']);
        $cerrGlobal = array_sum($graficoPorArea['cerradasPorArea']);
        $pendGlobal = array_sum($graficoPorArea['pendientesPorArea']);
        $pctGlobal  = $totGlobal > 0 ? round($cerrGlobal / $totGlobal * 100, 1) : 0;
    @endphp
    <tfoot>
        <tr>
            <td><strong>TOTAL GLOBAL</strong></td>
            <td class="center">{{ $totGlobal }}</td>
            <td class="center">{{ $cerrGlobal }}</td>
            <td class="center">{{ $pendGlobal }}</td>
            <td><strong>{{ $pctGlobal }}%</strong></td>
            <td class="center"><span class="badge {{ $pctGlobal >= 80 ? 'badge-ok' : ($pctGlobal >= 60 ? 'badge-warning' : 'badge-danger') }}">{{ $pctGlobal >= 80 ? 'Óptimo' : ($pctGlobal >= 60 ? 'Regular' : 'Crítico') }}</span></td>
        </tr>
    </tfoot>
</table>
@else
<div class="nota">Sin datos de planificación registrados aún.</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     MINUTAS POR MES
══════════════════════════════════════════════════════════ --}}
<div class="section-title">📅 Minutas por Mes — {{ now()->year }}</div>

@if($imgMinutas)
<div class="grafico-wrap">
    <div class="grafico-titulo">Evolución mensual de minutas — {{ now()->year }}</div>
    <img src="{{ $imgMinutas }}" alt="Gráfico de minutas por mes">
</div>
@endif

@php
    $maxMinutas = max(array_merge($graficoMinutas['valores'], [1]));
    $totalMinutasAnio = array_sum($graficoMinutas['valores']);
@endphp

<table class="data">
    <thead>
        <tr>
            <th style="width:18%">Mes</th>
            <th style="width:42%">Volumen</th>
            <th class="center" style="width:15%">Cantidad</th>
            <th class="center" style="width:25%">Acumulado anual</th>
        </tr>
    </thead>
    <tbody>
        @php $acumulado = 0; @endphp
        @foreach($graficoMinutas['labels'] as $i => $mes)
        @php
            $cant = $graficoMinutas['valores'][$i];
            $acumulado += $cant;
            $barPct = $maxMinutas > 0 ? round($cant / $maxMinutas * 100) : 0;
            $esMesActual = ($i + 1) === (int) now()->format('n');
        @endphp
        <tr @if($esMesActual) style="background:#EFF6FF !important;" @endif>
            <td>
                @if($esMesActual)<strong style="color:#1D6FD9">{{ $mes }} ◀</strong>@else{{ $mes }}@endif
            </td>
            <td>
                <div class="bar-wrap" style="width:120px">
                    <div class="bar-fill" style="width:{{ $barPct }}%;background:{{ $cant > 0 ? '#1D6FD9' : '#E2E8F0' }}"></div>
                </div>
            </td>
            <td class="center">
                @if($cant > 0)<strong style="color:#0D2B5E">{{ $cant }}</strong>@else<span style="color:#94A3B8">—</span>@endif
            </td>
            <td class="center" style="color:#6B7A99">{{ $acumulado > 0 ? $acumulado : '—' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td><strong>TOTAL {{ now()->year }}</strong></td>
            <td></td>
            <td class="center">{{ $totalMinutasAnio }}</td>
            <td class="center">{{ $totalMinutasAnio }}</td>
        </tr>
    </tfoot>
</table>

{{-- ══════════════════════════════════════════════════════════
     PIE DE PÁGINA
══════════════════════════════════════════════════════════ --}}
<div class="footer">
    <div class="footer-left">
        F&amp;C Chile SPA — Sistema de Gestión y Control (SGC)<br>
        Documento generado automáticamente. No requiere firma.
    </div>
    <div class="footer-right">
        Reporte generado el {{ \Carbon\Carbon::now()->format('d/m/Y') }} a las {{ \Carbon\Carbon::now()->format('H:i') }} hrs<br>
        Período: {{ \Carbon\Carbon::now()->format('Y') }}
    </div>
</div>

</body>
</html>
