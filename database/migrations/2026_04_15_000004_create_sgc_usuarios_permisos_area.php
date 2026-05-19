<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 4 — Tabla de permisos por área (planificación y minutas)
 * Origen: sgc_changes.sql — CREATE TABLE sgc_usuarios_permisos_area
 *
 * Permite configurar, por usuario y por área, qué operaciones puede
 * realizar en planificación y en minutas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sgc_usuarios_permisos_area')) {
            Schema::create('sgc_usuarios_permisos_area', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('id_usuario');
                $table->integer('id_area');

                // Permisos de planificación
                $table->tinyInteger('ver_planificacion')->default(0);
                $table->tinyInteger('editar_planificacion')->default(0);

                // Permisos de minutas
                $table->tinyInteger('ver_minutas')->default(0);
                $table->tinyInteger('editar_minutas')->default(0);

                $table->unique(['id_usuario', 'id_area'], 'uq_usuario_area');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_usuarios_permisos_area');
    }
};
