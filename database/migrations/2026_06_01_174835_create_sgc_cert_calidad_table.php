<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sgc_cert_calidad', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('numero')->nullable();
            $table->string('contrato', 200)->nullable();
            $table->string('descripcion', 400);
            $table->string('tipo_certificado', 200)->nullable();
            $table->boolean('aplica')->default(false);
            $table->boolean('critico')->default(false);
            $table->string('procedimiento_asociado', 300)->nullable();
            $table->string('marca', 200)->nullable();
            $table->string('modelo', 200)->nullable();
            $table->date('vencimiento')->nullable();
            $table->text('observaciones')->nullable();
            // Archivo adjunto (certificado)
            $table->string('archivo_nombre', 300)->nullable();
            $table->string('archivo_ruta',   400)->nullable();
            $table->string('archivo_mime',   100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sgc_cert_calidad');
    }
};
