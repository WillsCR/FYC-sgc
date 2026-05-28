<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 1 — Tabla de áreas de la organización
 *
 * Almacena las áreas (departamentos) que se usan en planificación y minutas.
 * Las áreas se asignan a usuarios en sgc_usuarios_areas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sgc_areas')) {
            Schema::create('sgc_areas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('descripcion', 100);
            });
        }

        // Sembrar las áreas base solo si la tabla está vacía
        if (DB::table('sgc_areas')->count() === 0) {
            DB::table('sgc_areas')->insert([
                ['descripcion' => 'Recursos Humanos'],
                ['descripcion' => 'Seguridad y Salud en el Trabajo'],
                ['descripcion' => 'Abastecimiento y Finanzas'],
                ['descripcion' => 'Contrato Pozos'],
                ['descripcion' => 'Medio Ambiente'],
                ['descripcion' => 'Control SGI'],
                ['descripcion' => 'SGI Gestión'],
                ['descripcion' => 'Patios e Infraestructura'],
                ['descripcion' => 'Gerencia de Operaciones'],
                ['descripcion' => 'Gerencia General'],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_areas');
    }
};
