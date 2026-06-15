<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Avisos de vencimiento Control de Instrumentos ──────────────────────────
// Ejecuta cada día a las 08:00 AM y envía correos a responsables de
// certificados que vencen dentro de los próximos 30 días.
Schedule::command('ci:avisos-vencimiento')->dailyAt('08:00');

// ── Avisos de vencimiento Certificados de Calidad ──────────────────────────
Schedule::command('cc:avisos-vencimiento')->dailyAt('08:05');
