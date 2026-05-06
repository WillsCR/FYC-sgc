<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Actualizar módulos principales (nivel 0)
        DB::table('sgc_carpetas3')
            ->where('id', 1)
            ->where('nivel', 0)
            ->update(['color' => '#0D2B5E', 'icono' => '📋']);

        DB::table('sgc_carpetas3')
            ->where('id', 2)
            ->where('nivel', 0)
            ->update(['color' => '#15803D', 'icono' => '🌿']);

        DB::table('sgc_carpetas3')
            ->where('id', 3)
            ->where('nivel', 0)
            ->update(['color' => '#991B1B', 'icono' => '🛡️']);

        DB::table('sgc_carpetas3')
            ->where('id', 4)
            ->where('nivel', 0)
            ->update(['color' => '#B45309', 'icono' => '🏗️']);

        DB::table('sgc_carpetas3')
            ->where('id', 5)
            ->where('nivel', 0)
            ->update(['color' => '#7C3AED', 'icono' => '👨‍💼']);

        DB::table('sgc_carpetas3')
            ->where('id', 6)
            ->where('nivel', 0)
            ->update(['color' => '#0C4A6E', 'icono' => '🏢']);

        DB::table('sgc_carpetas3')
            ->where('id', 7)
            ->where('nivel', 0)
            ->update(['color' => '#1D4ED8', 'icono' => '📈']);

        DB::table('sgc_carpetas3')
            ->where('id', 8)
            ->where('nivel', 0)
            ->update(['color' => '#065F46', 'icono' => '💰']);

        // Actualizar submódulos (nivel 1) con sus colores e iconos
        $submódulos = [
            'No Conformidades' => ['color' => '#DC2626', 'icono' => '⚠️'],
            'Instrumentos de Medición Certificación de Calidad' => ['color' => '#D97706', 'icono' => '📏'],
            'Certificados de Calidad' => ['color' => '#0F6E56', 'icono' => '✅'],
            'Certificados de EPP' => ['color' => '#B45309', 'icono' => '🦺'],
            'Formatos SIG' => ['color' => '#C05621', 'icono' => '📝'],
            'Documentos del SIG' => ['color' => '#1D4ED8', 'icono' => '📁'],
            'Capacitaciones' => ['color' => '#0369A1', 'icono' => '🎓'],
            'Informes' => ['color' => '#7C3AED', 'icono' => '📊'],
            'Auditorías' => ['color' => '#059669', 'icono' => '🔍'],
            'Sustancias y Residuos Peligrosos' => ['color' => '#DC2626', 'icono' => '♻️'],
            'Control de Recursos' => ['color' => '#D97706', 'icono' => '🌱'],
            'Huellas de Carbono' => ['color' => '#0F6E56', 'icono' => '🌍'],
            'Control Operativo' => ['color' => '#B45309', 'icono' => '⚙️'],
            'Protocolo Minsal' => ['color' => '#C05621', 'icono' => '🏥'],
            'DS 44' => ['color' => '#1D4ED8', 'icono' => '⚖️'],
            'CPHS' => ['color' => '#0369A1', 'icono' => '👥'],
            'Control Plan e Infraestructura' => ['color' => '#7C3AED', 'icono' => '🏗️'],
            'Cursos' => ['color' => '#059669', 'icono' => '📚'],
            'Contrato pozos' => ['color' => '#6366F1', 'icono' => '⛏️'],
        ];

        foreach ($submódulos as $descripcion => $estilo) {
            DB::table('sgc_carpetas3')
                ->where('descripcion', $descripcion)
                ->where('nivel', 1)
                ->update([
                    'color' => $estilo['color'],
                    'icono' => $estilo['icono'],
                ]);
        }
    }

    public function down(): void
    {
        // Limpiar colores e iconos
        DB::table('sgc_carpetas3')
            ->whereIn('nivel', [0, 1])
            ->update(['color' => null, 'icono' => null]);
    }
};
