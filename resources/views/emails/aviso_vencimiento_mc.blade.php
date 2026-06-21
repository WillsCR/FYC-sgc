<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aviso de vencimiento</title>
<style>
  body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; color: #1e293b; }
  .wrap { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
  .header { background: #0D2B5E; padding: 28px 32px; text-align: center; }
  .header h1 { color: #fff; font-size: 1.15rem; margin: 12px 0 4px; }
  .header p  { color: rgba(255,255,255,.7); font-size: .82rem; margin: 0; }
  .alert-bar { background: #FEF3C7; border-left: 5px solid #F59E0B; padding: 14px 20px; display: flex; align-items: center; gap: 12px; }
  .alert-bar .icon { font-size: 1.6rem; }
  .alert-bar .text { font-size: .88rem; color: #92400E; font-weight: 600; }
  .body { padding: 28px 32px; }
  .body p  { font-size: .9rem; color: #374151; line-height: 1.6; margin: 0 0 16px; }
  .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 20px; margin-bottom: 20px; }
  .card-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #e2e8f0; font-size: .85rem; }
  .card-row:last-child { border-bottom: none; }
  .card-row .label { color: #6b7280; font-weight: 600; }
  .card-row .value { color: #1e293b; font-weight: 500; text-align: right; }
  .dias-badge { display: inline-block; background: #FEF3C7; color: #92400E; border: 1px solid #F59E0B; border-radius: 6px; padding: 4px 12px; font-weight: 700; font-size: .9rem; margin: 4px 0 16px; }
  .dias-badge.rojo { background: #FEE2E2; color: #991B1B; border-color: #EF4444; }
  .footer { background: #f1f5f9; padding: 16px 32px; text-align: center; font-size: .75rem; color: #94a3b8; }
</style>
</head>
<body>
<div class="wrap">

  <div class="header">
    <h1>⚠️ Aviso de Vencimiento de Curso</h1>
    <p>Matriz Control Cursos — F&amp;C Chile SPA</p>
  </div>

  @php
    $dias = \Carbon\Carbon::today()->diffInDays($curso->fecha_vencimiento, false);
    $badgeClass = $dias <= 7 ? 'rojo' : '';
  @endphp
  <div class="alert-bar">
    <span class="icon">🔔</span>
    <span class="text">
      El curso vence en <strong>{{ $dias }} día{{ $dias !== 1 ? 's' : '' }}</strong>.
      Se requiere acción antes del <strong>{{ \Carbon\Carbon::parse($curso->fecha_vencimiento)->format('d-m-Y') }}</strong>.
    </span>
  </div>

  <div class="body">
    <p>Estimado/a <strong>{{ $trabajador->nombres }} {{ $trabajador->apellidos }}</strong>,</p>
    <p>
      Le informamos que el curso indicado a continuación tiene su vigencia próxima a vencer.
      Por favor, coordine su renovación con anticipación para mantener
      la conformidad del sistema de gestión.
    </p>

    <span class="dias-badge {{ $badgeClass }}">
      Vence en {{ $dias }} día{{ $dias !== 1 ? 's' : '' }} — {{ \Carbon\Carbon::parse($curso->fecha_vencimiento)->format('d \d\e F, Y') }}
    </span>

    <div class="card">
      <div class="card-row">
        <span class="label">Trabajador</span>
        <span class="value">{{ $trabajador->nombres }} {{ $trabajador->apellidos }}</span>
      </div>
      <div class="card-row">
        <span class="label">RUT</span>
        <span class="value">{{ $trabajador->rut ?: '—' }}</span>
      </div>
      <div class="card-row">
        <span class="label">Cargo</span>
        <span class="value">{{ $trabajador->cargo ?: '—' }}</span>
      </div>
      <div class="card-row">
        <span class="label">Contrato</span>
        <span class="value">{{ $trabajador->contrato ?: '—' }}</span>
      </div>
      <div class="card-row">
        <span class="label">Curso</span>
        <span class="value">{{ $curso->curso }}</span>
      </div>
      @if($curso->entidad_acreditadora)
      <div class="card-row">
        <span class="label">Entidad acreditadora</span>
        <span class="value">{{ $curso->entidad_acreditadora }}</span>
      </div>
      @endif
      @if($curso->fecha_realizacion)
      <div class="card-row">
        <span class="label">Fecha de realización</span>
        <span class="value">{{ \Carbon\Carbon::parse($curso->fecha_realizacion)->format('d-m-Y') }}</span>
      </div>
      @endif
      <div class="card-row">
        <span class="label">Fecha de vencimiento</span>
        <span class="value" style="color:#D97706;font-weight:700">{{ \Carbon\Carbon::parse($curso->fecha_vencimiento)->format('d-m-Y') }}</span>
      </div>
    </div>

    <p style="font-size:.82rem;color:#6b7280;margin:0">
      Este aviso fue generado automáticamente por el sistema SGC de F&amp;C Chile SPA.
      Por favor, no responda este correo directamente.
    </p>
  </div>

  <div class="footer">
    © {{ date('Y') }} F&amp;C Chile SPA · Ingeniería &amp; Construcción<br>
    Sistema de Gestión y Control Transversal
  </div>
</div>
</body>
</html>
