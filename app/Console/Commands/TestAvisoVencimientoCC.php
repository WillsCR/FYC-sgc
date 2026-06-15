<?php

namespace App\Console\Commands;

use App\Mail\AvisoVencimientoCC;
use App\Models\CertCalidad;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestAvisoVencimientoCC extends Command
{
    protected $signature   = 'cc:test-aviso {correo} {--dias=28 : Simular días para vencimiento}';
    protected $description = 'Envía un correo de prueba de aviso de vencimiento CC al correo indicado.';

    public function handle(): int
    {
        $correo = $this->argument('correo');
        $dias   = (int) $this->option('dias');

        $cert = CertCalidad::whereNotNull('descripcion')->first();

        if (! $cert) {
            $this->error('No hay certificados registrados en la BD.');
            return self::FAILURE;
        }

        // Simular fecha de vencimiento próxima
        $cert->vencimiento = Carbon::today()->addDays($dias);

        $this->info("Enviando correo de prueba a: {$correo}");
        $this->line("  Certificado: {$cert->descripcion}");
        $this->line("  Vence:       {$cert->vencimiento->format('d-m-Y')} (en {$dias} días)");

        try {
            Mail::to($correo)->send(new AvisoVencimientoCC($cert));
            $this->info('✓ Correo enviado correctamente.');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Error al enviar: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
