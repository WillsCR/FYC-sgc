<?php

namespace App\Console\Commands;

use App\Mail\AvisoVencimientoMC;
use App\Models\MatrizCurso;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarAvisosVencimientoMC extends Command
{
    protected $signature   = 'mc:avisos-vencimiento';
    protected $description = 'Envía avisos por correo cuando un curso de la Matriz vence en 30 días.';

    public function handle(): int
    {
        $hoy    = Carbon::today();
        $limite = $hoy->copy()->addDays(30);

        $cursos = MatrizCurso::with('trabajador')
            ->whereNotNull('correo_aviso')
            ->where('correo_aviso', '!=', '')
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [$hoy->toDateString(), $limite->toDateString()])
            ->where('aviso_30d_enviado', false)
            ->get();

        if ($cursos->isEmpty()) {
            $this->info('Sin avisos por enviar hoy.');
            return self::SUCCESS;
        }

        $enviados = 0;
        $errores  = 0;

        foreach ($cursos as $curso) {
            if (! $curso->trabajador) continue;
            try {
                $destinatarios = array_filter(array_map('trim', explode(',', $curso->correo_aviso)));
                Mail::to($destinatarios)->send(new AvisoVencimientoMC($curso, $curso->trabajador));
                $curso->update(['aviso_30d_enviado' => true]);
                $enviados++;
                $nombre = $curso->trabajador->nombres . ' ' . $curso->trabajador->apellidos;
                $this->line("  ✓ Enviado a {$curso->correo_aviso} — {$curso->curso} ({$nombre})");
            } catch (\Exception $e) {
                $errores++;
                $this->error("  ✗ Error con {$curso->curso}: " . $e->getMessage());
                \Log::error("AvisoVencimientoMC [{$curso->id}]: " . $e->getMessage());
            }
        }

        // Resetear flag cuando el curso fue renovado (vencimiento > 30 días)
        MatrizCurso::where('aviso_30d_enviado', true)
            ->where('fecha_vencimiento', '>', $limite->toDateString())
            ->update(['aviso_30d_enviado' => false]);

        $this->info("Avisos enviados: {$enviados}" . ($errores ? " | Errores: {$errores}" : ''));
        return self::SUCCESS;
    }
}
