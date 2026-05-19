<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 4 — Tabla de áreas asignadas por usuario
 * Origen: sgc_changes.sql — CREATE TABLE sgc_usuarios_areas
 *
 * Almacena la relación entre usuarios y sus áreas de trabajo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sgc_usuarios_areas')) {
            Schema::create('sgc_usuarios_areas', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('id_usuario');
                $table->integer('id_area');

                $table->unique(['id_usuario', 'id_area'], 'uq_usuario_area');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_usuarios_areas');
    }
};
