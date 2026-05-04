<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sgc_publicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('seccion', 20);              // 'sig' | 'ambiente'
            $table->string('titulo', 300);
            $table->string('archivo', 120)->unique();   // UUID + extensión
            $table->string('nombre_original', 300);
            $table->string('tipo_mime', 100);
            $table->unsignedBigInteger('tamanio');      // bytes
            $table->unsignedInteger('creado_por')->nullable();
            $table->timestamp('creada_el')->useCurrent();

            $table->index('seccion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_publicaciones');
    }
};
