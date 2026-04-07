@extends('layouts.app')

@section('title', 'Métricas')


@push('styles')
<style>
/* ── Layout responsive ───────────────────────────────────── */
.met-body {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

/* Cabecera */
.met-header {
    margin-bottom: 20px;
}
.met-header h2 {
    font-size: 1.1rem;
    color: var(--navy);
    margin-bottom: 2px;
}
.met-header p {
    font-size: .78rem;
    color: var(--text-muted);
}

/* KPIs — fila de tarjetas numéricas */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}
.kpi-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-top: 3px solid var(--navy);
    border-radius: var(--radius-md);
    padding: 14px 16px;
}
.kpi-card.green  { border-top-color: #16A34A; }
.kpi-card.amber  { border-top-color: #D97706; }
.kpi-card.blue   { border-top-color: #1D4ED8; }
.kpi-card.purple { border-top-color: #7C3AED; }
.kpi-val {
    font-size: 1.7rem;
    font-weight: 700;
    color: var(--navy);
    line-height: 1;
    margin-bottom: 4px;
}
.kpi-val.green  { color: #16A34A; }
.kpi-val.amber  { color: #D97706; }
.kpi-val.red    { color: #DC2626; }
.kpi-lbl {
    font-size: .65rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .06em;
}

/* Fila de gráficos principal: 2 columnas → 1 en móvil */
.graf-row {
    display: grid;
    grid-template-columns: 3fr 2fr;
    gap: 12px;
    margin-bottom: 12px;
}
.graf-row-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}

/* Card de gráfico */
.graf-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 16px;
    display: flex;
    flex-direction: column;
}
.graf-title {
    font-size: .72rem;
    font-weight: 700;
    color: var(--navy);
    text-transform: uppercase;
    letter-spacing: .06em;
    padding-bottom: 10px;
    margin-bottom: 12px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.graf-badge {
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 2px 10px;
    font-size: .68rem;
    font-weight: 600;
    color: var(--navy);
    white-space: nowrap;
}
.graf-canvas-wrap {
    position: relative;
    flex: 1;
    min-height: 0;
}

/* Tabla resumen */
.resumen-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .75rem;
}
.resumen-table th {
    background: var(--navy);
    color: #fff;
    padding: 7px 10px;
    text-align: left;
    font-size: .68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.resumen-table td {
    padding: 7px 10px;
    border-bottom: 1px solid var(--border);
    color: var(--text-secondary);
    vertical-align: middle;
}
.resumen-table tr:last-child td { border-bottom: none; }
.resumen-table tr:hover td { background: var(--surface-2); }
.pct-bar-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
}
.pct-bar-bg {
    flex: 1;
    height: 6px;
    background: #E5E7EB;
    border-radius: 3px;
    overflow: hidden;
}
.pct-bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width .4s ease;
}
.pct-label {
    font-weight: 700;
    font-size: .72rem;
    min-width: 36px;
    text-align: right;
}

/* Donut centrado */
.donut-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1;
    gap: 12px;
}
.donut-canvas-wrap {
    width: 100%;
    max-width: 200px;
    position: relative;
}
.donut-legend {
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
    max-width: 200px;
}
.donut-legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .75rem;
    color: var(--text-secondary);
}
.donut-dot {
    width: 10px;
    height: 10px;
    border-radius: 3px;
    flex-shrink: 0;
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 1024px) {
    .kpi-grid   { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .graf-row   { grid-template-columns: 1fr; }
    .graf-row-3 { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 768px) {
    .met-body   { padding: 16px; }
    .met-header { margin-bottom: 16px; }
    .kpi-grid   { gap: 8px; margin-bottom: 16px; }
    .kpi-card   { padding: 12px 14px; }
    .kpi-val    { font-size: 1.5rem; margin-bottom: 2px; }
    .kpi-lbl    { font-size: .6rem; }
    .graf-row, .graf-row-3 { gap: 10px; margin-bottom: 10px; }
    .graf-card  { padding: 14px; }
    .graf-title { padding-bottom: 8px; margin-bottom: 10px; font-size: .68rem; }
}

@media (max-width: 640px) {
    .met-body   { padding: 12px; }
    .met-header { margin-bottom: 12px; }
    .met-header h2 { font-size: .95rem; margin-bottom: 2px; }
    .met-header p { font-size: .7rem; }
    .kpi-grid   { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-bottom: 12px; }
    .kpi-card   { padding: 10px 12px; }
    .kpi-val    { font-size: 1.4rem; margin-bottom: 2px; }
    .kpi-lbl    { font-size: .58rem; }
    .graf-row, .graf-row-3 { grid-template-columns: 1fr; gap: 10px; margin-bottom: 10px; }
    .graf-card  { padding: 12px; }
    .graf-title { padding-bottom: 8px; margin-bottom: 8px; font-size: .65rem; gap: 4px; }
    .graf-badge { font-size: .6rem; padding: 1px 8px; }
    .resumen-table { font-size: .7rem; }
    .resumen-table th { padding: 6px 8px; font-size: .62rem; }
    .resumen-table td { padding: 6px 8px; }
}

@media (max-width: 480px) {
    .met-body   { padding: 10px; }
    .met-header { margin-bottom: 10px; }
    .met-header h2 { font-size: .9rem; }
    .met-header p { font-size: .65rem; }
    .kpi-grid   { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; margin-bottom: 10px; }
    .kpi-card   { padding: 8px 10px; border-radius: 4px; }
    .kpi-val    { font-size: 1.2rem; margin-bottom: 1px; }
    .kpi-lbl    { font-size: .55rem; }
    .graf-row, .graf-row-3 { gap: 8px; margin-bottom: 8px; }
    .graf-card  { padding: 10px; border-radius: 4px; }
    .graf-title { padding-bottom: 6px; margin-bottom: 6px; font-size: .6rem; }
    .graf-badge { font-size: .55rem; padding: 0px 6px; }
    .donut-canvas-wrap { max-width: 150px; }
    .donut-legend { max-width: 150px; }
    .donut-legend-item { font-size: .7rem; }
    .resumen-table { font-size: .65rem; }
    .resumen-table th { padding: 5px 6px; font-size: .58rem; }
    .resumen-table td { padding: 5px 6px; }
    .pct-label { font-size: .65rem; }
} .graf-card  { padding: 12px; }

</style>
@endpush

@section('content')
<div class="met-body">

    {{-- Cabecera --}}
    <div class="met-header">
        <h2>📊 Métricas del sistema</h2>
        <p>Datos actualizados en tiempo real · {{ now()->locale('es')->isoFormat('D [de] MMMM, YYYY') }}</p>
    </div>

    {{-- ── KPIs ─────────────────────────────────────────────── --}}
    <div class="kpi-grid">
        <div class="kpi-card green">
            <div class="kpi-val green">{{ $stats['cumplimiento'] }}%</div>
            <div class="kpi-lbl">Cumplimiento global</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-val {{ $stats['pendientes'] > 5 ? 'red' : 'amber' }}">
                {{ $stats['pendientes'] }}
            </div>
            <div class="kpi-lbl">Actividades pendientes</div>
        </div>
        <div class="kpi-card blue">
            <div class="kpi-val">{{ $stats['cerradas'] }}</div>
            <div class="kpi-lbl">Actividades cerradas</div>
        </div>
        <div class="kpi-card purple">
            <div class="kpi-val">{{ $stats['minutasAnio'] }}</div>
            <div class="kpi-lbl">Minutas {{ now()->year }}</div>
        </div>
    </div>

    {{-- ── Fila 1: Barras por área + Donut ─────────────────── --}}
    <div class="graf-row">

        {{-- Barras horizontales --}}
        <div class="graf-card">
            <div class="graf-title">
                Cumplimiento por área
                <span class="graf-badge">{{ $stats['totalPlan'] }} actividades totales</span>
            </div>
            <div class="graf-canvas-wrap" style="height:280px">
                <canvas id="graficoPorArea"></canvas>
            </div>
        </div>

        {{-- Donut --}}
        <div class="graf-card">
            <div class="graf-title">Estado planificaciones</div>
            <div class="donut-wrap">
                <div class="donut-canvas-wrap">
                    <canvas id="graficoDonut"></canvas>
                </div>
                <div class="donut-legend">
                    <div class="donut-legend-item">
                        <div class="donut-dot" style="background:#16A34A"></div>
                        <span>Cerradas</span>
                        <strong style="margin-left:auto;color:#16A34A">{{ $stats['cerradas'] }}</strong>
                    </div>
                    <div class="donut-legend-item">
                        <div class="donut-dot" style="background:#D97706"></div>
                        <span>Pendientes</span>
                        <strong style="margin-left:auto;color:#D97706">{{ $stats['pendientes'] }}</strong>
                    </div>
                    <div class="donut-legend-item" style="border-top:1px solid var(--border);padding-top:6px;margin-top:2px">
                        <div class="donut-dot" style="background:var(--navy)"></div>
                        <span>Total</span>
                        <strong style="margin-left:auto">{{ $stats['totalPlan'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Fila 2: Línea minutas + Tabla detalle ────────────── --}}
    <div class="graf-row">

        {{-- Línea minutas por mes --}}
        <div class="graf-card">
            <div class="graf-title">
                Minutas por mes
                <span class="graf-badge">{{ now()->year }}</span>
            </div>
            <div class="graf-canvas-wrap" style="height:220px">
                <canvas id="graficoMinutas"></canvas>
            </div>
        </div>

        {{-- Tabla detalle por área --}}
        <div class="graf-card">
            <div class="graf-title">Detalle por área</div>
            <div style="overflow-x:auto">
                <table class="resumen-table">
                    <thead>
                        <tr>
                            <th>Área</th>
                            <th style="text-align:center">Total</th>
                            <th>Cumplimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($graficoPorArea['labels'] as $i => $label)
                        @php
                            $pct   = $graficoPorArea['cumplimiento'][$i];
                            $color = $pct >= 80 ? '#16A34A' : ($pct >= 60 ? '#D97706' : '#DC2626');
                        @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td style="text-align:center">{{ $graficoPorArea['totales'][$i] }}</td>
                            <td>
                                <div class="pct-bar-wrap">
                                    <div class="pct-bar-bg">
                                        <div class="pct-bar-fill"
                                             style="width:{{ $pct }}%;background:{{ $color }}">
                                        </div>
                                    </div>
                                    <span class="pct-label" style="color:{{ $color }}">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
var dataPorArea = @json($graficoPorArea);
var dataMinutas = @json($graficoMinutas);
var totalCerradas   = {{ $stats['cerradas'] }};
var totalPendientes = {{ $stats['pendientes'] }};

Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#64748B';

// ── Barras horizontales — cumplimiento por área ───────────────────────────
new Chart(document.getElementById('graficoPorArea'), {
    type: 'bar',
    data: {
        labels: dataPorArea.labels,
        datasets: [{
            label: 'Cumplimiento',
            data: dataPorArea.cumplimiento,
            backgroundColor: dataPorArea.cumplimiento.map(function(v) {
                return v >= 80 ? 'rgba(22,163,74,.8)'
                     : v >= 60 ? 'rgba(217,119,6,.8)'
                     :           'rgba(220,38,38,.8)';
            }),
            borderRadius: 4,
            borderSkipped: false,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        var i = ctx.dataIndex;
                        return [
                            ' Cumplimiento: ' + ctx.raw + '%',
                            ' Cerradas: '     + dataPorArea.cerradasPorArea[i],
                            ' Pendientes: '   + dataPorArea.pendientesPorArea[i],
                            ' Total: '        + dataPorArea.totales[i],
                        ];
                    }
                }
            }
        },
        scales: {
            x: {
                min: 0, max: 100,
                ticks: { callback: function(v) { return v + '%'; }, stepSize: 20 },
                grid: { color: 'rgba(0,0,0,.05)' }
            },
            y: {
                grid: { display: false },
                ticks: { font: { size: 10 } }
            }
        }
    }
});

// ── Donut — estado planificaciones ────────────────────────────────────────
new Chart(document.getElementById('graficoDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Cerradas', 'Pendientes'],
        datasets: [{
            data: [totalCerradas, totalPendientes],
            backgroundColor: ['rgba(22,163,74,.85)', 'rgba(217,119,6,.85)'],
            borderColor:     ['#16A34A', '#D97706'],
            borderWidth: 2,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        cutout: '70%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        var total = totalCerradas + totalPendientes;
                        var pct   = total > 0 ? Math.round(ctx.raw / total * 100) : 0;
                        return ' ' + ctx.raw + ' (' + pct + '%)';
                    }
                }
            }
        }
    }
});

// ── Línea — minutas por mes ───────────────────────────────────────────────
new Chart(document.getElementById('graficoMinutas'), {
    type: 'line',
    data: {
        labels: dataMinutas.labels,
        datasets: [{
            label: 'Minutas',
            data: dataMinutas.valores,
            borderColor: '#0D2B5E',
            backgroundColor: 'rgba(13,43,94,.08)',
            borderWidth: 2.5,
            pointBackgroundColor: '#0D2B5E',
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.35,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        return ' ' + ctx.raw + ' minuta' + (ctx.raw !== 1 ? 's' : '');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 5 },
                grid: { color: 'rgba(0,0,0,.05)' }
            },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
