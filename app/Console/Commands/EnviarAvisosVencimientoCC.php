<?php

namespace App\Console\Commands;

use App\Mail\AvisoVencimientoCC;
use App\Models\CertCalidad;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarAvisosVencimientoCC extends Command
{
    protected $signature   = 'cc:avisos-vencimiento';
    protected $description = 'Envía avisos por correo de certificados de calidad que vencen en 30 días.';

    public function handle(): int
    {
        $hoy    = Carbon::today();
        $limite = $hoy->copy()->addDays(30);

        $certs = CertCalidad::whereNotNull('correo_aviso')
            ->where('correo_aviso', '!=', '')
            ->whereNotNull('vencimiento')
            ->whereBetween('vencimiento', [$hoy->toDateString(), $limite->toDateString()])
            ->where('aviso_30d_enviado', false)
            ->get();

        if ($certs->isEmpty()) {
            $this->info('Sin avisos por enviar hoy.');
            return self::SUCCESS;
        }

        $enviados = 0;
        $errores  = 0;

        foreach ($certs as $cert) {
            try {
                Mail::to($cert->correo_aviso)->send(new AvisoVencimientoCC($cert));
                $cert->update(['aviso_30d_enviado' => true]);
                $enviados++;
                $this->line("  ✓ Enviado a {$cert->correo_aviso} — {$cert->descripcion}");
            } catch (\Exception $e) {
                $errores++;
                $this->error("  ✗ Error con {$cert->descripcion}: " . $e->getMessage());
                \Log::error("AvisoVencimientoCC [{$cert->id}]: " . $e->getMessage());
            }
        }

        // Resetear flag cuando el certificado fue renovado (vencimiento > 30 días)
        CertCalidad::where('aviso_30d_enviado', true)
            ->where('vencimiento', '>', $limite->toDateString())
            ->update(['aviso_30d_enviado' => false]);

        $this->info("Avisos enviados: {$enviados}" . ($errores ? " | Errores: {$errores}" : ''));
        return self::SUCCESS;
    }
}
