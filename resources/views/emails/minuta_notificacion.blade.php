<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notificación de minuta</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #f0f4f8; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #222; }
  .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.12); }

  /* Cabecera azul */
  .header { background: #0D2B5E; padding: 24px 32px; display: flex; align-items: center; gap: 18px; }
  .header-logo { font-size: 28px; color: #fff; font-weight: 900; letter-spacing: -1px; line-height: 1; }
  .header-logo span { display: block; font-size: 11px; font-weight: 400; letter-spacing: .08em; text-transform: uppercase; opacity: .8; margin-top: 2px; }
  .header-pillars { display: flex; gap: 5px; }
  .pillar { width: 10px; border-radius: 2px 2px 0 0; background: #fff; opacity: .9; }

  /* Franja de acción */
  .action-bar { background: {{ $accion === 'creada' ? '#1565C0' : '#0277BD' }}; color: #fff; padding: 10px 32px; font-size: 12px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }

  /* Cuerpo */
  .body { padding: 28px 32px; }
  .saludo { margin-bottom: 16px; color: #444; line-height: 1.5; }
  .intro { margin-bottom: 20px; color: #333; line-height: 1.6; }
  .intro strong { color: #0D2B5E; }

  /* Tabla de detalles */
  .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13px; }
  .detail-table tr { border-bottom: 1px solid #e8edf2; }
  .detail-table tr:last-child { border-bottom: none; }
  .detail-table td { padding: 9px 12px; vertical-align: top; }
  .detail-table td:first-child { font-weight: 600; color: #0D2B5E; width: 42%; background: #f7f9fc; border-radius: 3px; }
  .detail-table td:last-child { color: #333; }

  /* Compromisos */
  .comp-title { font-weight: 700; color: #0D2B5E; font-size: 13px; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 2px solid #0D2B5E; }
  .comp-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 22px; }
  .comp-table th { background: #0D2B5E; color: #fff; padding: 7px 10px; text-align: left; font-weight: 600; }
  .comp-table td { padding: 7px 10px; border-bottom: 1px solid #e8edf2; vertical-align: top; color: #333; }
  .comp-table tr:last-child td { border-bottom: none; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; }
  .badge-proceso { background: #FEF3C7; color: #92400E; }
  .badge-cerrado  { background: #D1FAE5; color: #065F46; }
  .badge-descartado { background: #FEE2E2; color: #991B1B; }

  /* Separador */
  .divider { border: none; border-top: 1px solid #e8edf2; margin: 0 0 20px 0; }

  /* Pie */
  .footer-note { font-size: 12px; color: #666; line-height: 1.6; margin-bottom: 20px; }
  .footer { background: #f7f9fc; padding: 16px 32px; border-top: 1px solid #e8edf2; text-align: center; font-size: 11px; color: #888; }
  .footer strong { color: #0D2B5E; font-size: 12px; display: block; margin-bottom: 3px; }
</style>
</head>
<body>
<div class="wrapper">

  {{-- Cabecera --}}
  <div class="header">
    <div class="header-pillars">
      <div class="pillar" style="height:38px"></div>
      <div class="pillar" style="height:30px"></div>
      <div class="pillar" style="height:22px"></div>
    </div>
    <div class="header-logo">
      F&amp;C CHILE SPA
      <span>Ingeniería &amp; Construcción</span>
    </div>
  </div>

  {{-- Barra de acción --}}
  <div class="action-bar">
    @if($accion === 'creada')
      ✦ Nueva minuta registrada
    @else
      ✦ Minuta actualizada
    @endif
  </div>

  {{-- Cuerpo --}}
  <div class="body">

    <p class="saludo">Estimados:</p>

    <p class="intro">
      @if($accion === 'creada')
        Se ha ingresado una nueva minuta del área <strong>{{ $datos['area_nombre'] }}</strong>
        del <strong>{{ $datos['fecha_formateada'] }}</strong> con el siguiente detalle:
      @else
        Se han actualizado los datos de la minuta del área <strong>{{ $datos['area_nombre'] }}</strong>
        del <strong>{{ $datos['fecha_formateada'] }}</strong>:
      @endif
    </p>

    {{-- Detalles generales --}}
    <table class="detail-table">
      <tr><td>ID</td><td>{{ $datos['id'] }}</td></tr>
      <tr><td>Área</td><td>{{ $datos['area_nombre'] }}</td></tr>
      <tr><td>Empresa</td><td>{{ $datos['empresa'] }}</td></tr>
      <tr><td>Tipo de reunión</td><td>{{ $datos['tipo_reunion'] }}</td></tr>
      <tr><td>Lugar</td><td>{{ $datos['lugar'] }}</td></tr>
      <tr><td>Inicio</td><td>{{ $datos['hora_inicio'] }}</td></tr>
      <tr><td>Término</td><td>{{ $datos['hora_fin'] }}</td></tr>
      <tr><td>Próxima reunión</td><td>{{ $datos['proxima_reunion'] ?? '—' }}</td></tr>
    </table>

    {{-- Compromisos --}}
    @if(!empty($datos['compromisos']))
    <div class="comp-title">Compromisos ({{ count($datos['compromisos']) }})</div>
    <table class="comp-table">
      <tr>
        <th style="width:6%">#</th>
        <th>Descripción</th>
        <th>Responsable</th>
        <th>Fecha</th>
        <th>Estado</th>
      </tr>
      @foreach($datos['compromisos'] as $comp)
      <tr>
        <td>{{ $comp->item }}</td>
        <td>{{ $comp->descripcion }}</td>
        <td>{{ $comp->responsable ?: '—' }}</td>
        <td>{{ $comp->inicio_compromiso ? \Carbon\Carbon::parse($comp->inicio_compromiso)->format('d/m/Y') : '—' }}</td>
        <td>
          @php $s = (int)$comp->status; @endphp
          @if($s === 1)<span class="badge badge-proceso">En Proceso</span>
          @elseif($s === 2)<span class="badge badge-cerrado">Cerrado</span>
          @else<span class="badge badge-descartado">Descartado</span>
          @endif
        </td>
      </tr>
      @endforeach
    </table>
    @endif

    <hr class="divider">

    <p class="footer-note">
      Detalles completos en PDF adjunto.<br>
      Sin otro particular, le saluda atentamente,
    </p>

  </div>

  {{-- Pie --}}
  <div class="footer">
    <strong>F&amp;C CHILE SPA — Software de Control y Gestión Transversal</strong>
    Este correo fue generado automáticamente. Por favor no responda a este mensaje.
  </div>

</div>
</body>
</html>
