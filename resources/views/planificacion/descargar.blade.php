<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planificación #{{ $plan->id }} — F&C Chile SPA</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f5f7fa;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.6;
        }

        .page {
            max-width: 800px;
            margin: 16px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(0,0,0,.12);
            overflow: hidden;
        }

        /* Encabezado */
        .header {
            background: #0D2B5E;
            padding: 24px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .header-left h1 {
            color: #fff;
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 3px;
        }
        .header-left .subtitle {
            color: #90B4E8;
            font-size: .78rem;
        }
        .header-right {
            text-align: right;
            color: #90B4E8;
            font-size: .75rem;
            line-height: 1.8;
        }

        /* Semáforo badge */
        .semaforo-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 700;
            margin-top: 10px;
        }
        .sem-verde    { background: #DCFCE7; color: #15803D; }
        .sem-amarillo { background: #FEF3C7; color: #B45309; }
        .sem-rojo     { background: #FEE2E2; color: #B91C1C; }
        .sem-gris     { background: #F1F5F9; color: #64748B; }
        .sem-dot { width: 9px; height: 9px; border-radius: 50%; }
        .sem-verde    .sem-dot { background: #16A34A; }
        .sem-amarillo .sem-dot { background: #D97706; }
        .sem-rojo     .sem-dot { background: #DC2626; }
        .sem-gris     .sem-dot { background: #94A3B8; }

        /* Estado badge */
        .estado-badge {
            display: inline-flex; align-items: center;
            padding: 3px 10px; border-radius: 12px;
            font-size: .72rem; font-weight: 700;
        }
        .estado-pendiente { background: #FEF3C7; color: #B45309; }
        .estado-cerrado   { background: #DCFCE7; color: #15803D; }
        .estado-sin       { background: #F1F5F9; color: #64748B; }

        /* Cuerpo */
        .body { padding: 28px 32px; }

        /* Sección */
        .section-title {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #94A3B8;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #E2E8F0;
        }

        /* Grid de campos */
        .fields-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        .field { }
        .field.full { grid-column: 1 / -1; }
        .field label {
            display: block;
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94A3B8;
            margin-bottom: 4px;
        }
        .field .value {
            font-size: .85rem;
            color: #1e293b;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 9px 12px;
            min-height: 38px;
            line-height: 1.55;
            word-break: break-word;
            white-space: pre-wrap;
        }
        .field .value.actividad {
            font-size: .88rem;
            font-weight: 500;
        }
        .field .value.obs {
            color: #475569;
            font-style: italic;
        }

        /* Fechas */
        .fecha-wrap { display: flex; align-items: center; gap: 10px; }
        .fecha-dias {
            font-size: .72rem;
            font-weight: 700;
        }

        /* Footer */
        .footer {
            background: #F8FAFC;
            border-top: 1px solid #E2E8F0;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: .72rem;
            color: #94A3B8;
        }

        @media print {
            body { background: #fff; }
            .page { margin: 0; border-radius: 0; box-shadow: none; }
            @page { size: A4; margin: 1.5cm; }
        }
    </style>
</head>
<body>

<div class="page">

    {{-- Encabezado --}}
    <div class="header">
        <div class="header-left">
            <h1>Registro de Planificación</h1>
            <div class="subtitle">F&C Chile SPA — Sistema de Gestión Corporativa</div>
            @php
                $semClass = match($plan->semaforo) {
                    'verde'    => 'sem-verde',
                    'amarillo' => 'sem-amarillo',
                    'rojo'     => 'sem-rojo',
                    default    => 'sem-gris',
                };
                $semTexto = match($plan->semaforo) {
                    'verde'    => 'A tiempo',
                    'amarillo' => 'Por vencer',
                    'rojo'     => 'Vencida',
                    default    => 'Sin fecha',
                };
            @endphp
            <div class="semaforo-badge {{ $semClass }}">
                <div class="sem-dot"></div>
                {{ $semTexto }}
            </div>
        </div>
        <div class="header-right">
            <div><strong style="color:#fff">ID #{{ $plan->id }}</strong></div>
            <div>Generado el {{ now()->format('d/m/Y') }}</div>
            <div>{{ now()->format('H:i') }} hrs</div>
        </div>
    </div>

    {{-- Cuerpo --}}
    <div class="body">

        {{-- Actividad --}}
        <div class="section-title">Actividad</div>
        <div class="fields-grid" style="margin-bottom:24px">
            <div class="field full">
                <label>Descripción de la actividad</label>
                <div class="value actividad">{{ $plan->actividades }}</div>
            </div>
        </div>

        {{-- Responsable y Área --}}
        <div class="section-title">Responsable y Área</div>
        <div class="fields-grid" style="margin-bottom:24px">
            <div class="field">
                <label>Responsable</label>
                <div class="value">{{ $plan->responsable ?: '—' }}</div>
            </div>
            <div class="field">
                <label>Área</label>
                <div class="value">{{ $plan->area_nombre }}</div>
            </div>
            @if($plan->correo)
            <div class="field full">
                <label>Correo electrónico</label>
                <div class="value">{{ $plan->correo }}</div>
            </div>
            @endif
        </div>

        {{-- Fechas y Estado --}}
        @php
            $terminoFmt = $plan->termino ? \Carbon\Carbon::parse($plan->termino)->format('d/m/Y') : '—';
            $diasTexto = match(true) {
                is_null($plan->dias_restantes)  => '',
                (int)$plan->id_estado === 2     => '',
                $plan->dias_restantes < 0        => abs($plan->dias_restantes) . 'd vencida',
                $plan->dias_restantes === 0      => 'Vence hoy',
                $plan->dias_restantes === 1      => 'Mañana',
                default                          => $plan->dias_restantes . 'd restantes',
            };
            $diasColor = match($plan->semaforo) {
                'rojo'     => '#DC2626',
                'amarillo' => '#D97706',
                default    => '#16A34A',
            };
        @endphp
        <div class="section-title">Fechas y Estado</div>
        <div class="fields-grid" style="margin-bottom:{{ $plan->observaciones ? '24px' : '0' }}">
            <div class="field">
                <label>Fecha de Inicio</label>
                <div class="value" style="height:42px;display:flex;align-items:center">
                    {{ $plan->inicio ? \Carbon\Carbon::parse($plan->inicio)->format('d/m/Y') : '—' }}
                </div>
            </div>
            <div class="field">
                <label>Fecha de Término</label>
                <div class="value" style="height:42px;display:flex;align-items:center;gap:10px">
                    {{ $terminoFmt }}
                    @if($diasTexto)
                    <span style="font-size:.72rem;font-weight:700;color:{{ $diasColor }}">
                        ({{ $diasTexto }})
                    </span>
                    @endif
                </div>
            </div>
            <div class="field">
                <label>Estado</label>
                <div class="value" style="height:42px;display:flex;align-items:center">
                    @php
                        $badgeClass = match((int)$plan->id_estado) {
                            1 => 'estado-pendiente',
                            2 => 'estado-cerrado',
                            default => 'estado-sin',
                        };
                    @endphp
                    <span class="estado-badge {{ $badgeClass }}">{{ $plan->estado_nombre }}</span>
                </div>
            </div>
        </div>

        {{-- Observaciones --}}
        @if($plan->observaciones)
        <div class="section-title" style="margin-top:24px">Observaciones</div>
        <div class="fields-grid">
            <div class="field full">
                <div class="value obs">{{ $plan->observaciones }}</div>
            </div>
        </div>
        @endif

    </div>

    {{-- Pie --}}
    <div class="footer">
        <span>F&C Chile SPA · Sistema de Gestión Corporativa (SGC)</span>
        <span>Planificación #{{ $plan->id }} · {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</div>

<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>
</body>
</html>
