<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 4/5 — Agregar bloque_finanzas a sgc_usuarios
 * Origen: sgc_changes.sql — ALTER TABLE sgc_usuarios ADD COLUMN bloque_finanzas
 *
 * Habilita el acceso al módulo de Control Finanzas desde el panel.
 * Activa el bloque para cuentas Super Admin (id_perfil = 1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sgc_usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('sgc_usuarios', 'bloque_finanzas')) {
                $table->tinyInteger('bloque_finanzas')->default(0)->after('bloque_proyectos');
            }
        });

        // Activar para Super Admins
        DB::table('sgc_usuarios')
            ->where('id_perfil', 1)
            ->update(['bloque_finanzas' => 1]);
    }

    public function down(): void
    {
        Schema::table('sgc_usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('sgc_usuarios', 'bloque_finanzas')) {
                $table->dropColumn('bloque_finanzas');
            }
        });
    }
};
