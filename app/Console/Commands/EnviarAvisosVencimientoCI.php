<?php

namespace App\Console\Commands;

use App\Mail\AvisoVencimientoCI;
use App\Models\ProgramaVerificacion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarAvisosVencimientoCI extends Command
{
    protected $signature   = 'ci:avisos-vencimiento';
    protected $description = 'Envía avisos por correo a los responsables de certificados que vencen en 30 días.';

    public function handle(): int
    {
        $hoy    = Carbon::today();
        $limite = $hoy->copy()->addDays(30);

        // Certificados que vencen en los próximos 30 días,
        // con correo configurado y aviso aún no enviado.
        $programas = ProgramaVerificacion::with('area')
            ->whereNotNull('correo_aviso')
            ->where('correo_aviso', '!=', '')
            ->whereNotNull('proxima')
            ->whereBetween('proxima', [$hoy->toDateString(), $limite->toDateString()])
            ->where('aviso_30d_enviado', false)
            ->get();

        if ($programas->isEmpty()) {
            $this->info('Sin avisos por enviar hoy.');
            return self::SUCCESS;
        }

        $enviados = 0;
        $errores  = 0;

        foreach ($programas as $programa) {
            try {
                Mail::to($programa->correo_aviso)->send(new AvisoVencimientoCI($programa));
                $programa->update(['aviso_30d_enviado' => true]);
                $enviados++;
                $this->line("  ✓ Enviado a {$programa->correo_aviso} — {$programa->descripcion}");
            } catch (\Exception $e) {
                $errores++;
                $this->error("  ✗ Error con {$programa->descripcion}: " . $e->getMessage());
                \Log::error("AvisoVencimientoCI [{$programa->id}]: " . $e->getMessage());
            }
        }

        // Resetear flag cuando el certificado fue renovado (proxima > 30 días)
        ProgramaVerificacion::where('aviso_30d_enviado', true)
            ->where('proxima', '>', $limite->toDateString())
            ->update(['aviso_30d_enviado' => false]);

        $this->info("Avisos enviados: {$enviados}" . ($errores ? " | Errores: {$errores}" : ''));
        return self::SUCCESS;
    }
}
