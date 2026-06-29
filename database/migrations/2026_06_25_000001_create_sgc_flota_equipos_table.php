<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sgc_flota_equipos', function (Blueprint $table) {
            $table->id();

            // Datos del equipo
            $table->string('equipo', 100);          // CAMIONETA, CAMION, etc.
            $table->string('marca',  100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('patente', 20)->nullable();
            $table->string('area',    100)->nullable();

            // ── Fechas de certificaciones (14 tipos) ─────────────────────────
            $table->date('fecha_gps')->nullable();
            $table->date('fecha_skynav')->nullable();
            $table->date('fecha_revision_tecnica')->nullable();
            $table->date('fecha_permiso_circulacion')->nullable();
            $table->date('fecha_soap')->nullable();
            $table->date('fecha_cert_mlp')->nullable();
            $table->date('fecha_extintor')->nullable();
            $table->date('fecha_prueba_carga')->nullable();
            $table->date('fecha_insp_camion_pluma')->nullable();
            $table->date('fecha_insp_gancho')->nullable();
            $table->date('fecha_insp_perforadora')->nullable();
            $table->date('fecha_gancho_perforadora')->nullable();
            $table->date('fecha_cable_acero_perforadora')->nullable();
            $table->date('fecha_wuinche_perforadora')->nullable();

            // ── Control de kilometrajes ───────────────────────────────────────
            $table->unsignedInteger('km_actual')->nullable();
            $table->unsignedInteger('km_proxima_mantencion')->nullable();
            $table->string('responsable', 150)->nullable();

            // ── General ───────────────────────────────────────────────────────
            $table->string('correo_aviso', 500)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('aviso_cert_enviado')->default(false);
            $table->boolean('aviso_km_enviado')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_flota_equipos');
    }
};
