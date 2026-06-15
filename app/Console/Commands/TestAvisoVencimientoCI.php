<?php

namespace App\Console\Commands;

use App\Mail\AvisoVencimientoCI;
use App\Models\ProgramaVerificacion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestAvisoVencimientoCI extends Command
{
    protected $signature   = 'ci:test-aviso {correo} {--dias=28 : Simular días para vencimiento}';
    protected $description = 'Envía un correo de prueba de aviso de vencimiento CI al correo indicado.';

    public function handle(): int
    {
        $correo = $this->argument('correo');
        $dias   = (int) $this->option('dias');

        // Tomar el primer equipo con datos completos
        $programa = ProgramaVerificacion::with('area')
            ->whereNotNull('descripcion')
            ->first();

        if (! $programa) {
            $this->error('No hay equipos registrados en la BD.');
            return self::FAILURE;
        }

        // Simular fecha de vencimiento próxima
        $programa->proxima     = Carbon::today()->addDays($dias);
        $programa->responsable = $programa->responsable ?: 'Responsable de Prueba';

        $this->info("Enviando correo de prueba a: {$correo}");
        $this->line("  Equipo:  {$programa->descripcion}");
        $this->line("  Vence:   {$programa->proxima->format('d-m-Y')} (en {$dias} días)");

        try {
            Mail::to($correo)->send(new AvisoVencimientoCI($programa));
            $this->info('✓ Correo enviado correctamente.');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Error al enviar: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
