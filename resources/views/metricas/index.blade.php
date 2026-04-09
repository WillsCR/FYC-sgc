@extends('layouts.app')

@section('title', 'Métricas')

@section('subnav')
    <a href="{{ route('panel') }}"           class="subnav-item">Inicio</a>
    <a href="{{ route('metricas') }}"         class="subnav-item active">Métricas</a>
    <a href="{{ route('carpetas.index') }}"   class="subnav-item">Documentos</a>
    <a href="#" class="subnav-item">Planificación</a>
    <a href="#" class="subnav-item">Minutas</a>
    @if(session('es_admin'))
        <a href="#" class="subnav-item">Usuarios</a>
    @endif
@endsection

@push('styles')
<style>
/* ── Layout ──────────────────────────────────────────────────────── */
.met-body { padding: 20px; max-width: 1400px; margin: 0 auto; }

/* ── Cabecera con botones de descarga ────────────────────────────── */
.met-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}
.met-header-text h2  { font-size: 1.1rem; color: var(--navy); margin-bottom: 2px; }
.met-header-text p   { font-size: .78rem; color: var(--text-muted); }

.descarga-btns { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }

.btn-descarga {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    border-radius: var(--radius-sm);
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all .15s;
    white-space: nowrap;
}
.btn-excel {
    background: #16A34A;
    color: #fff;
}
.btn-excel:hover { background: #15803D; }

.btn-pdf {
    background: #DC2626;
    color: #fff;
}
.btn-pdf:hover { background: #B91C1C; }

.btn-descarga svg { flex-shrink: 0; }

/* ── Spinner de carga ─────────────────────────────────────────────── */
.spinner-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.4);
    z-index: 500;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 14px;
}
.spinner-overlay.visible { display: flex; }
.spinner {
    width: 44px; height: 44px;
    border: 4px solid rgba(255,255,255,.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin .8s linear infinite;
}
.spinner-text { color: #fff; font-size: .9rem; font-weight: 500; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── KPIs ─────────────────────────────────────────────────────────── */
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
.kpi-val  { font-size: 1.7rem; font-weight: 700; color: var(--navy); line-height: 1; margin-bottom: 4px; }
.kpi-val.green  { color: #16A34A; }
.kpi-val.amber  { color: #D97706; }
.kpi-val.red    { color: #DC2626; }
.kpi-lbl  { font-size: .65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .06em; }

/* ── Gráficos ─────────────────────────────────────────────────────── */
.graf-row   { display: grid; grid-template-columns: 3fr 2fr; gap: 12px; margin-bottom: 12px; }
.graf-card  {
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
}
.graf-canvas-wrap { position: relative; flex: 1; min-height: 0; }

/* ── Donut ────────────────────────────────────────────────────────── */
.donut-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; gap: 12px; }
.donut-canvas-wrap { width: 100%; max-width: 200px; }
.donut-legend { display: flex; flex-direction: column; gap: 6px; width: 100%; max-width: 200px; }
.donut-legend-item { display: flex; align-items: center; gap: 8px; font-size: .75rem; color: var(--text-secondary); }
.donut-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

/* ── Tabla resumen ────────────────────────────────────────────────── */
.resumen-table { width: 100%; border-collapse: collapse; font-size: .75rem; }
.resumen-table th {
    background: var(--navy); color: #fff;
    padding: 7px 10px; text-align: left;
    font-size: .68rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .04em;
}
.resumen-table td {
    padding: 7px 10px;
    border-bottom: 1px solid var(--border);
    color: var(--text-secondary);
    vertical-align: middle;
}
.resumen-table tr:last-child td { border-bottom: none; }
.resumen-table tr:hover td { background: var(--surface-2); }
.pct-bar-wrap { display: flex; align-items: center; gap: 6px; }
.pct-bar-bg { flex: 1; height: 6px; background: #E5E7EB; border-radius: 3px; overflow: hidden; }
.pct-bar-fill { height: 100%; border-radius: 3px; }
.pct-label { font-weight: 700; font-size: .72rem; min-width: 36px; text-align: right; }

/* ── Responsive ───────────────────────────────────────────────────── */
@media (max-width: 1024px) {
    .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .graf-row  { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .met-body { padding: 12px; }
    .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    .kpi-val  { font-size: 1.4rem; }
    .met-header { flex-direction: column; }
    .descarga-btns { width: 100%; }
    .btn-descarga { flex: 1; justify-content: center; }
}

/* ── Estilos para el PDF (ocultos normalmente) ────────────────────── */
@media print {
    .subnav, .navbar, .descarga-btns, .met-header-text p { display: none !important; }
    .met-body { padding: 0; }
}
</style>
@endpush

@section('content')

{{-- Spinner de carga --}}
<div class="spinner-overlay" id="spinner">
    <div class="spinner"></div>
    <div class="spinner-text" id="spinner-text">Generando archivo...</div>
</div>

<div class="met-body" id="contenido-metricas">

    {{-- ── Cabecera con botones ────────────────────────────────────── --}}
    <div class="met-header">
        <div class="met-header-text">
            <h2>📊 Métricas del sistema</h2>
            <p>Datos actualizados en tiempo real · {{ now()->locale('es')->isoFormat('D [de] MMMM, YYYY') }}</p>
        </div>

        <div class="descarga-btns">
            {{-- Botón Excel --}}
            <a href="{{ route('metricas.excel') }}"
               class="btn-descarga btn-excel"
               onclick="mostrarSpinner('Generando Excel...')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                Descargar Excel
            </a>

            {{-- Botón PDF (genera desde el navegador con los gráficos) --}}
            <button class="btn-descarga btn-pdf" onclick="descargarPDF()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="12" y1="18" x2="12" y2="12"/>
                    <line x1="9" y1="15" x2="12" y2="18"/>
                    <line x1="15" y1="15" x2="12" y2="18"/>
                </svg>
                Descargar PDF
            </button>
        </div>
    </div>

    {{-- ── KPIs ─────────────────────────────────────────────────────── --}}
    <div class="kpi-grid">
        <div class="kpi-card green">
            <div class="kpi-val green">{{ $stats['cumplimiento'] }}%</div>
            <div class="kpi-lbl">Cumplimiento global</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-val {{ $stats['pendientes'] > 5 ? 'red' : 'amber' }}">{{ $stats['pendientes'] }}</div>
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

    {{-- ── Fila 1: Barras + Donut ──────────────────────────────────── --}}
    <div class="graf-row">
        <div class="graf-card">
            <div class="graf-title">
                Cumplimiento por área
                <span class="graf-badge">{{ $stats['totalPlan'] }} actividades totales</span>
            </div>
            <div class="graf-canvas-wrap" style="height:280px">
                <canvas id="graficoPorArea"></canvas>
            </div>
        </div>
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

    {{-- ── Fila 2: Línea + Tabla ────────────────────────────────────── --}}
    <div class="graf-row">
        <div class="graf-card">
            <div class="graf-title">
                Minutas por mes
                <span class="graf-badge">{{ now()->year }}</span>
            </div>
            <div class="graf-canvas-wrap" style="height:220px">
                <canvas id="graficoMinutas"></canvas>
            </div>
        </div>
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
                                        <div class="pct-bar-fill" style="width:{{ $pct }}%;background:{{ $color }}"></div>
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
{{-- Chart.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
{{-- html2pdf para generar PDF con los gráficos --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
var dataPorArea  = @json($graficoPorArea);
var dataMinutas  = @json($graficoMinutas);
var totalCerradas   = {{ $stats['cerradas'] }};
var totalPendientes = {{ $stats['pendientes'] }};

Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#64748B';

// ── Barras horizontales ───────────────────────────────────────────────────
new Chart(document.getElementById('graficoPorArea'), {
    type: 'bar',
    data: {
        labels: dataPorArea.labels,
        datasets: [{
            label: 'Cumplimiento',
            data: dataPorArea.cumplimiento,
            backgroundColor: dataPorArea.cumplimiento.map(function(v) {
                return v >= 80 ? 'rgba(22,163,74,.8)' : v >= 60 ? 'rgba(217,119,6,.8)' : 'rgba(220,38,38,.8)';
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
            x: { min:0, max:100, ticks:{ callback: function(v){ return v+'%'; }, stepSize:20 }, grid:{ color:'rgba(0,0,0,.05)' } },
            y: { grid:{ display:false }, ticks:{ font:{ size:10 } } }
        }
    }
});

// ── Donut ─────────────────────────────────────────────────────────────────
new Chart(document.getElementById('graficoDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Cerradas','Pendientes'],
        datasets: [{
            data: [totalCerradas, totalPendientes],
            backgroundColor: ['rgba(22,163,74,.85)','rgba(217,119,6,.85)'],
            borderColor:     ['#16A34A','#D97706'],
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

// ── Línea minutas ─────────────────────────────────────────────────────────
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
            tooltip: { callbacks: { label: function(ctx) { return ' ' + ctx.raw + ' minuta' + (ctx.raw !== 1 ? 's' : ''); } } }
        },
        scales: {
            y: { beginAtZero: true, ticks:{ stepSize:5 }, grid:{ color:'rgba(0,0,0,.05)' } },
            x: { grid:{ display: false } }
        }
    }
});

// ── Spinner ───────────────────────────────────────────────────────────────
function mostrarSpinner(texto) {
    document.getElementById('spinner-text').textContent = texto || 'Generando archivo...';
    document.getElementById('spinner').classList.add('visible');
    // Ocultar automáticamente después de 4 segundos
    setTimeout(function() {
        document.getElementById('spinner').classList.remove('visible');
    }, 4000);
}

// ── Generar PDF con html2pdf ──────────────────────────────────────────────
function descargarPDF() {
    mostrarSpinner('Generando PDF...');

    var fecha = new Date().toLocaleDateString('es-CL').replace(/\//g, '-');
    var elemento = document.getElementById('contenido-metricas');

    var opciones = {
        margin:       [10, 10, 10, 10],
        filename:     'Metricas_SGC_' + fecha + '.pdf',
        image:        { type: 'jpeg', quality: 0.95 },
        html2canvas:  { scale: 2, useCORS: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' },
        pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
    };

    html2pdf().set(opciones).from(elemento).save().then(function() {
        document.getElementById('spinner').classList.remove('visible');
    });
}

// ── Fix botón atrás ───────────────────────────────────────────────────────
window.history.pushState(null, '', window.location.href);
window.addEventListener('popstate', function() {
    window.history.pushState(null, '', window.location.href);
});
</script>
@endpush
