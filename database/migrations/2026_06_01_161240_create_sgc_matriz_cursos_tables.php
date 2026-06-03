<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sgc_matriz_trabajadores', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 200);
            $table->string('apellidos', 200);
            $table->string('rut', 30)->nullable();
            $table->string('cargo', 200)->nullable();
            $table->string('contrato', 200)->nullable();
            $table->timestamps();
        });

        Schema::create('sgc_matriz_cursos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_trabajador');
            $table->string('curso', 300);
            $table->string('entidad_acreditadora', 200)->nullable();
            $table->date('fecha_realizacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('correo_aviso', 200)->nullable();
            $table->string('archivo_nombre', 300)->nullable();
            $table->string('archivo_ruta', 500)->nullable();
            $table->string('archivo_mime', 100)->nullable();
            $table->timestamps();

            $table->foreign('id_trabajador')
                  ->references('id')->on('sgc_matriz_trabajadores')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_matriz_cursos');
        Schema::dropIfExists('sgc_matriz_trabajadores');
    }
};
