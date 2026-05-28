<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Control de No Conformidades — 4 tablas
 *
 * Réplica del sistema legacy (ges_no_conformidades y tablas relacionadas)
 * adaptada a la convención sgc_ y con FK en InnoDB.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tabla principal ───────────────────────────────────────────
        Schema::create('sgc_no_conformidades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('num_nc');                        // Número correlativo visible
            $table->date('fecha_deteccion');
            $table->string('origen', 100)->default('');
            $table->unsignedInteger('id_area')->nullable();   // FK a sgc_areas
            $table->tinyInteger('tipo_accion')->default(1);   // 1=Correctiva  2=Oportunidad de mejora

            // Detectada por
            $table->string('detectada_por', 150)->default('');
            $table->string('cargo', 100)->default('');
            $table->text('descripcion');                      // Hallazgo

            // Medidas inmediatas — responsable y status
            $table->string('resp_inmediata_nombre', 150)->default('');
            $table->string('resp_inmediata_cargo', 100)->default('');
            $table->tinyInteger('status_correccion')->default(0); // 0=Pendiente 1=Resuelta

            // Acciones correctivas — responsable, fechas, status
            $table->string('resp_correctiva_nombre', 150)->default('');
            $table->string('resp_correctiva_cargo', 100)->default('');
            $table->date('fecha_implem_acc_corr')->nullable();
            $table->date('fecha_seguim_acc_corr')->nullable();
            $table->tinyInteger('status_acc_corr')->default(0);

            // Verificación eficacia
            $table->tinyInteger('status_seguim_acc_corr')->default(0);
            $table->tinyInteger('accion_eficaz')->default(0); // 0=Pendiente 1=Sí 2=No
            $table->date('fecha_cierre')->nullable();
            $table->text('registros')->nullable();
            $table->string('observaciones', 500)->nullable();

            // Metadatos
            $table->unsignedInteger('id_usuario')->nullable();
            $table->timestamp('creada_el')->useCurrent();
            $table->timestamp('actualizada_el')->useCurrent()->useCurrentOnUpdate();

            $table->index('num_nc');
            $table->index('fecha_deteccion');
        });

        // ── 2. Acciones inmediatas (múltiples por NC) ────────────────────
        Schema::create('sgc_nc_acciones_inmediatas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_nc');
            $table->text('descripcion');

            $table->foreign('id_nc')
                  ->references('id')->on('sgc_no_conformidades')
                  ->onDelete('cascade');
        });

        // ── 3. Acciones correctivas (múltiples por NC) ───────────────────
        Schema::create('sgc_nc_acciones_correctivas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_nc');
            $table->text('descripcion');

            $table->foreign('id_nc')
                  ->references('id')->on('sgc_no_conformidades')
                  ->onDelete('cascade');
        });

        // ── 4. Documentos / evidencias ───────────────────────────────────
        Schema::create('sgc_nc_documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_nc');
            $table->string('archivo', 120);          // UUID + extensión
            $table->string('nombre_original', 300);
            $table->tinyInteger('tipo');              // 1=inmediata 2=correctiva
            $table->unsignedInteger('id_usuario')->nullable();
            $table->timestamp('creada_el')->useCurrent();

            $table->foreign('id_nc')
                  ->references('id')->on('sgc_no_conformidades')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_nc_documentos');
        Schema::dropIfExists('sgc_nc_acciones_correctivas');
        Schema::dropIfExists('sgc_nc_acciones_inmediatas');
        Schema::dropIfExists('sgc_no_conformidades');
    }
};
