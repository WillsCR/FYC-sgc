<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #1a1a1a; padding: 28px 36px; }

  /* ── Cabecera ────────────────────────────────────── */
  .header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
  .header-table td { padding: 0; vertical-align: middle; border: none; }
  .header-border { border-bottom: 2px solid #0D2B5E; margin-bottom: 22px; padding-bottom: 10px; }
  .header-right { font-size: 11px; font-weight: 700; color: #0D2B5E; text-align: right; vertical-align: middle; }

  /* ── Título ─────────────────────────────────────── */
  .titulo {
      text-align: center;
      font-size: 17px;
      font-weight: 900;
      color: #0D2B5E;
      margin: 18px 0 16px;
      letter-spacing: .03em;
      text-transform: uppercase;
  }

  /* ── Semáforo badge ──────────────────────────────── */
  .semaforo-wrap {
      text-align: center;
      margin-bottom: 18px;
  }
  .semaforo-badge {
      display: inline-block;
      padding: 4px 14px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 700;
  }
  .sem-verde    { background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; }
  .sem-amarillo { background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D; }
  .sem-rojo     { background: #FEE2E2; color: #B91C1C; border: 1px solid #FCA5A5; }
  .sem-gris     { background: #F1F5F9; color: #64748B; border: 1px solid #CBD5E1; }

  /* ── Tabla de datos ──────────────────────────────── */
  .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 18px;
      font-size: 11.5px;
  }
  .info-table td {
      border: 1px solid #b0b8c4;
      padding: 7px 10px;
      vertical-align: top;
  }
  .info-table td.lbl {
      font-weight: 700;
      color: #333;
      background: #f5f7fa;
      width: 18%;
      white-space: nowrap;
  }
  .info-table td.val {
      color: #1a1a1a;
  }

  /* ── Sección actividad ───────────────────────────── */
  .section-header {
      background: #0D2B5E;
      color: #fff;
      font-weight: 700;
      font-size: 11px;
      letter-spacing: .06em;
      padding: 7px 10px;
      text-transform: uppercase;
      border: 1px solid #0D2B5E;
  }
  .section-body {
      border: 1px solid #c8d0db;
      border-top: none;
      padding: 11px 14px;
      margin-bottom: 18px;
      font-size: 11.5px;
      color: #222;
      line-height: 1.6;
  }

  /* ── Badge estado ────────────────────────────────── */
  .estado-badge {
      display: inline-block;
      padding: 2px 9px;
      border-radius: 10px;
      font-size: 10.5px;
      font-weight: 700;
  }
  .estado-pendiente { background: #FEF3C7; color: #B45309; }
  .estado-cerrado   { background: #DCFCE7; color: #15803D; }
  .estado-sin       { background: #F1F5F9; color: #64748B; }

  /* ── Pie ─────────────────────────────────────────── */
  .footer {
      margin-top: 28px;
      text-align: center;
      font-size: 10.5px;
      border-top: 1px solid #c8d0db;
      padding-top: 10px;
      font-weight: 700;
      color: #0D2B5E;
  }
  .footer-sub {
      font-size: 9.5px;
      color: #888;
      font-weight: 400;
      margin-top: 3px;
  }
</style>
</head>
<body>

@php
    $logoPath = public_path('img/logo_fyc.png');
    $logoB64  = base64_encode(file_get_contents($logoPath));
    $logoSrc  = 'data:image/png;base64,' . $logoB64;

    $semTexto = match($plan->semaforo) {
        'verde'    => '● A tiempo',
        'amarillo' => '● Por vencer',
        'rojo'     => '● Vencida',
        default    => '● Sin fecha',
    };
    $semClass = match($plan->semaforo) {
        'verde'    => 'sem-verde',
        'amarillo' => 'sem-amarillo',
        'rojo'     => 'sem-rojo',
        default    => 'sem-gris',
    };
    $badgeClass = match((int)$plan->id_estado) {
        1 => 'estado-pendiente',
        2 => 'estado-cerrado',
        default => 'estado-sin',
    };
    $diasTexto = match(true) {
        is_null($plan->dias_restantes)  => '',
        (int)$plan->id_estado === 2     => '',
        $plan->dias_restantes < 0        => abs($plan->dias_restantes) . ' días vencida',
        $plan->dias_restantes === 0      => 'Vence hoy',
        $plan->dias_restantes === 1      => 'Vence mañana',
        default                          => $plan->dias_restantes . ' días restantes',
    };
    $inicioFmt  = $plan->inicio  ? \Carbon\Carbon::parse($plan->inicio)->format('d/m/Y')  : '—';
    $terminoFmt = $plan->termino ? \Carbon\Carbon::parse($plan->termino)->format('d/m/Y') : '—';
@endphp

{{-- ── Cabecera ──────────────────────────────────────────── --}}
<div class="header-border">
<table class="header-table">
<tr>
    <td style="width:55%">
        <img src="{{ $logoSrc }}" style="height:48px;width:auto" alt="F&C Chile SPA">
    </td>
    <td class="header-right">F&amp;C CHILE SPA - Control y Gestión Transversal</td>
</tr>
</table>
</div>

{{-- ── Título ────────────────────────────────────────────── --}}
<div class="titulo">Registro de Planificación N° {{ $plan->id }}</div>

{{-- ── Datos generales ───────────────────────────────────── --}}
<table class="info-table">
    <tr>
        <td class="lbl">Área</td>
        <td class="val">{{ $plan->area_nombre }}</td>
        <td class="lbl">Estado</td>
        <td class="val"><span class="estado-badge {{ $badgeClass }}">{{ $plan->estado_nombre }}</span></td>
    </tr>
    <tr>
        <td class="lbl">Responsable</td>
        <td class="val">{{ $plan->responsable ?: '—' }}</td>
        <td class="lbl">Correo</td>
        <td class="val">{{ $plan->correo ?: '—' }}</td>
    </tr>
    <tr>
        <td class="lbl">Fecha inicio</td>
        <td class="val">{{ $inicioFmt }}</td>
        <td class="lbl">Fecha término</td>
        <td class="val">
            {{ $terminoFmt }}
            @if($diasTexto)
                <br><span style="font-size:10px;font-weight:700;
                    color:{{ $plan->semaforo === 'rojo' ? '#DC2626' : ($plan->semaforo === 'amarillo' ? '#D97706' : '#15803D') }}">
                    {{ $diasTexto }}
                </span>
            @endif
        </td>
    </tr>
</table>

{{-- ── Actividad ─────────────────────────────────────────── --}}
<div class="section-header">ACTIVIDAD</div>
<div class="section-body">{{ $plan->actividades }}</div>

{{-- ── Observaciones (si existen) ───────────────────────── --}}
@if($plan->observaciones)
<div class="section-header">OBSERVACIONES</div>
<div class="section-body" style="color:#444;font-style:italic">{{ $plan->observaciones }}</div>
@endif

{{-- ── Pie ───────────────────────────────────────────────── --}}
<div class="footer">
    F&amp;C CHILE SPA
    <div class="footer-sub">Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }} hrs · Sistema de Gestión Corporativa (SGC)</div>
</div>

</body>
</html>
