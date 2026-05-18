<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #1a1a1a; padding: 28px 36px; }

  /* ── Cabecera con logo ────────────────────────── */
  .header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
  .header-table td { padding: 0; vertical-align: middle; border: none; }
  .header-border { border-bottom: 2px solid #0D2B5E; margin-bottom: 22px; padding-bottom: 10px; }
  .header-right { font-size: 11px; font-weight: 700; color: #0D2B5E; text-align: right; vertical-align: middle; }

  /* ── Título MINUTA ────────────────────────────── */
  .titulo {
      text-align: center;
      font-size: 18px;
      font-weight: 900;
      color: #0D2B5E;
      margin: 18px 0 16px;
      letter-spacing: .03em;
  }

  /* ── Tabla de datos generales ────────────────── */
  .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 18px;
      font-size: 11.5px;
  }
  .info-table td {
      border: 1px solid #b0b8c4;
      padding: 6px 9px;
  }
  .info-table td.lbl {
      font-weight: 600;
      color: #333;
      background: #f5f7fa;
      width: 13%;
      white-space: nowrap;
  }
  .info-table td.val {
      color: #1a1a1a;
      width: 24%;
  }

  /* ── Tabla participantes ─────────────────────── */
  .section-header {
      background: #0D2B5E;
      color: #fff;
      text-align: center;
      font-weight: 700;
      font-size: 11.5px;
      letter-spacing: .06em;
      padding: 7px;
      text-transform: uppercase;
  }
  .part-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 18px;
      font-size: 11.5px;
  }
  .part-table th {
      background: #0D2B5E;
      color: #fff;
      padding: 6px 9px;
      text-align: left;
      font-weight: 700;
      font-size: 11px;
      border: 1px solid #0D2B5E;
  }
  .part-table td {
      padding: 6px 9px;
      border: 1px solid #c8d0db;
      vertical-align: middle;
      color: #222;
  }
  .part-table tr:nth-child(even) td { background: #f8f9fb; }

  /* ── Tabla ITEMS ─────────────────────────────── */
  .items-header {
      background: #0D2B5E;
      color: #fff;
      text-align: center;
      font-weight: 700;
      font-size: 11.5px;
      letter-spacing: .06em;
      padding: 7px;
      text-transform: uppercase;
      border: 1px solid #0D2B5E;
      margin-bottom: 0;
  }
  .items-wrap {
      border: 1px solid #c8d0db;
      border-top: none;
      padding: 10px 14px;
      margin-bottom: 20px;
  }
  .item-row {
      margin-bottom: 9px;
      line-height: 1.55;
      font-size: 11.5px;
      color: #222;
  }
  .item-row:last-child { margin-bottom: 0; }
  .item-num { font-weight: 700; color: #0D2B5E; }

  /* Status inline */
  .st-proceso    { color: #92400E; font-weight: 700; }
  .st-cerrado    { color: #065F46; font-weight: 700; }
  .st-descartado { color: #6B7280; font-weight: 700; }

  /* ── Pie ──────────────────────────────────────── */
  .footer {
      margin-top: 24px;
      text-align: center;
      font-size: 10.5px;
      color: #555;
      border-top: 1px solid #c8d0db;
      padding-top: 10px;
      font-weight: 600;
      color: #0D2B5E;
  }
</style>
</head>
<body>

{{-- ── Cabecera ──────────────────────────────────────────── --}}
@php
    $logoPath = public_path('img/logo_fyc.png');
    $logoB64  = base64_encode(file_get_contents($logoPath));
    $logoSrc  = 'data:image/png;base64,' . $logoB64;
@endphp
<div class="header-border">
<table class="header-table">
<tr>
    <td style="width:55%">
        <img src="{{ $logoSrc }}" style="height:52px;width:auto" alt="F&C Chile SPA">
    </td>
    <td class="header-right">F&amp;C CHILE SPA - Control y Gestión Transversal</td>
</tr>
</table>
</div>

{{-- ── Título ────────────────────────────────────────────── --}}
<div class="titulo">MINUTA N° {{ $datos['id'] }}</div>

{{-- ── Tabla de datos generales ─────────────────────────── --}}
<table class="info-table">
    <tr>
        <td class="lbl">Area</td>
        <td class="val">{{ $datos['area_nombre'] }}</td>
        <td class="lbl">Empresa</td>
        <td class="val">{{ $datos['empresa'] }}</td>
    </tr>
    <tr>
        <td class="lbl">Tipo reunión</td>
        <td class="val">{{ $datos['tipo_reunion'] }}</td>
        <td class="lbl">Lugar</td>
        <td class="val">{{ $datos['lugar'] }}</td>
    </tr>
    <tr>
        <td class="lbl">Fecha</td>
        <td class="val">{{ \Carbon\Carbon::parse($datos['fecha_raw'])->format('d/m/Y') }}</td>
        <td class="lbl">Próxima reunión</td>
        <td class="val">{{ $datos['proxima_reunion'] ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Hora inicio</td>
        <td class="val">{{ \Illuminate\Support\Str::substr($datos['hora_inicio'], 0, 5) }}</td>
        <td class="lbl">Hora fin</td>
        <td class="val">{{ \Illuminate\Support\Str::substr($datos['hora_fin'], 0, 5) }}</td>
    </tr>
</table>

{{-- ── Participantes ─────────────────────────────────────── --}}
@if(!empty($datos['convocados_lista']) && count($datos['convocados_lista']) > 0)
<table class="part-table">
    <thead>
        <tr>
            <th colspan="4" style="text-align:center;font-size:12px;letter-spacing:.06em">PARTICIPANTES</th>
        </tr>
        <tr>
            <th style="width:22%">Empresa</th>
            <th style="width:40%">Nombre y Apellido</th>
            <th style="width:24%">Cargo</th>
            <th style="width:14%">Firma</th>
        </tr>
    </thead>
    <tbody>
        @foreach($datos['convocados_lista'] as $conv)
        <tr>
            <td>{{ $conv->empresa ?: '—' }}</td>
            <td>{{ $conv->nom_ape ?: '—' }}</td>
            <td>{{ $conv->cargo ?: '—' }}</td>
            <td></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ── Items / Compromisos ────────────────────────────────── --}}
@if(!empty($datos['compromisos']) && count($datos['compromisos']) > 0)
<div class="items-header">ITEMS</div>
<div class="items-wrap">
    @foreach($datos['compromisos'] as $comp)
    @php
        $estado = match((int)$comp->status) {
            1 => ['texto' => 'En proceso.',    'clase' => 'st-proceso'],
            2 => ['texto' => 'Cerrado.',        'clase' => 'st-cerrado'],
            3 => ['texto' => 'Descartado.',     'clase' => 'st-descartado'],
            default => ['texto' => '',          'clase' => ''],
        };
        $fechaComp = $comp->inicio_compromiso
            ? \Carbon\Carbon::parse($comp->inicio_compromiso)->format('d/m/Y')
            : '—';
    @endphp
    <div class="item-row">
        <span class="item-num">{{ $comp->item }})</span>
        {{ $comp->descripcion }}
        @if($comp->responsable)
            <strong>Responsable:</strong> {{ $comp->responsable }}
        @endif
        <strong>Compromiso:</strong> {{ $fechaComp }}.
        @if(trim($comp->observaciones ?? '') !== '')
            {{ $comp->observaciones }}
        @endif
        <span class="{{ $estado['clase'] }}">{{ $estado['texto'] }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- ── Pie ───────────────────────────────────────────────── --}}
<div class="footer">F&amp;C CHILE SPA</div>

</body>
</html>
