<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sgc_archivos_equipos', function (Blueprint $table) {
            $table->id();
            
            // Identificación archivo
            $table->string('archivo_hash', 64)->unique()->comment('SHA256');
            $table->string('archivo_nombre');
            $table->string('archivo_extension', 10);
            $table->string('archivo_mime', 50);
            $table->integer('archivo_tamanio');
            
            // Rutas
            $table->string('ruta_legado')->nullable();
            $table->enum('ruta_tipo', ['legacy', 'hashed'])->default('hashed');
            $table->string('ruta_almacenamiento');
            
            // Relaciones
            $table->unsignedBigInteger('id_equipo_generico')->nullable();
            $table->unsignedBigInteger('id_programa_verificacion')->nullable();
            $table->unsignedBigInteger('id_equipo_interno')->nullable();
            
            // Tipo documento
            $table->enum('tipo_documento', [
                'imagen_general',
                'cert_calidad',
                'cert_calibracion',
                'manual',
                'protocolo',
                'inspecciones',
                'mantenimiento',
                'otro'
            ])->default('otro');
            
            // Metadata
            $table->unsignedBigInteger('id_usuario');
            $table->timestamp('fecha_subida')->useCurrent();
            $table->timestamp('fecha_modificacion')->useCurrent()->useCurrentOnUpdate();
            $table->text('descripcion')->nullable();
            $table->date('vigencia_hasta')->nullable();
            
            // Control
            $table->enum('estado', ['activo', 'inactivo', 'eliminado'])->default('activo');
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('reemplazo_de')->nullable();
            
            // Índices
            $table->index('tipo_documento');
            $table->index('id_programa_verificacion');
            $table->index('id_equipo_generico');
            $table->index('fecha_subida');
            $table->index('vigencia_hasta');
        });

        Schema::create('sgc_archivos_equipos_auditoria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_archivo');
            $table->string('accion');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->json('detalles')->nullable();
            
            $table->foreign('id_archivo')
                ->references('id')
                ->on('sgc_archivos_equipos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_archivos_equipos_auditoria');
        Schema::dropIfExists('sgc_archivos_equipos');
    }
};
