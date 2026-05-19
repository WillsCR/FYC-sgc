<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3 — Tablas base del gestor documental
 * Origen: files_tables.sql
 *
 * - sgc_carpetas3           : Árbol de carpetas/módulos/submódulos
 * - sgc_documentos          : Repositorio centralizado de archivos
 * - sgc_carpetas_contenido3 : Relación N:M carpeta ↔ documento
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Carpetas (árbol jerárquico) ──────────────────────────────
        if (! Schema::hasTable('sgc_carpetas3')) {
            Schema::create('sgc_carpetas3', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('descripcion', 255);
                $table->unsignedBigInteger('id_padre')->default(0)
                      ->comment('ID de la carpeta padre (0 = raíz)');
                $table->integer('nivel')->default(0)
                      ->comment('Profundidad en la jerarquía');
                $table->timestamp('creada_el')->useCurrent();
                $table->string('ruta', 500)->nullable()
                      ->comment('Ruta legacy si aplica');
                $table->index('id_padre');
                $table->index('nivel');
            });
        }

        // ── 2. Documentos (repositorio de archivos) ─────────────────────
        if (! Schema::hasTable('sgc_documentos')) {
            Schema::create('sgc_documentos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('archivo', 100)->unique()
                      ->comment('UUID del archivo, ej: 550e8400-...pdf');
                $table->string('nombre_original', 255)
                      ->comment('Nombre del archivo original para mostrar');
                $table->string('tipo_mime', 50)->default('application/pdf');
                $table->bigInteger('tamaño')->default(0)
                      ->comment('Tamaño en bytes');
                $table->string('hash_md5', 32)->nullable()->unique()
                      ->comment('Hash MD5 para detectar duplicados');
                $table->string('creado_por', 100)->nullable()
                      ->comment('Email del usuario que subió');
                $table->timestamp('creada_el')->useCurrent();
                $table->timestamp('modificada_el')->useCurrent()->useCurrentOnUpdate();
                $table->index('creada_el');
            });
        }

        // ── 3. Contenido: carpeta ↔ documento (N:M) ─────────────────────
        if (! Schema::hasTable('sgc_carpetas_contenido3')) {
            Schema::create('sgc_carpetas_contenido3', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_carpeta');
                $table->unsignedBigInteger('id_documento');
                $table->string('descripcion', 500)
                      ->comment('Ej: "Auditoría Q1 2026"');
                $table->json('metadata')->nullable()
                      ->comment('Datos extra en JSON: {"version":"2.0", "revision":"1"}');
                $table->timestamp('creada_el')->useCurrent();
                $table->timestamp('modificada_el')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['id_carpeta', 'id_documento'], 'uq_carpeta_documento');
                $table->index('id_documento');
                $table->index('creada_el');

                $table->foreign('id_carpeta', 'fk_carpetas_contenido3_carpeta')
                      ->references('id')->on('sgc_carpetas3')->onDelete('cascade');
                $table->foreign('id_documento', 'fk_carpetas_contenido3_documento')
                      ->references('id')->on('sgc_documentos')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_carpetas_contenido3');
        Schema::dropIfExists('sgc_documentos');
        Schema::dropIfExists('sgc_carpetas3');
    }
};
