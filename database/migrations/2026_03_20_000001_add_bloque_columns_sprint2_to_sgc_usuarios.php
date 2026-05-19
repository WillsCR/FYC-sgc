<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 2 (Semanas 3-4) — Nuevos bloques de módulos en sgc_usuarios
 * Origen: sgc_changes.sql — sección SPRINT 2
 *
 * Agrega columnas de acceso a los nuevos módulos:
 * bloque_gerencia, bloque_patio, bloque_calidad,
 * bloque_docs_legales, bloque_formatos, bloque_listado_interes
 *
 * Activa todos los bloques para cuentas Super Admin (id_perfil = 1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sgc_usuarios', function (Blueprint $table) {
            if (! Schema::hasColumn('sgc_usuarios', 'bloque_gerencia')) {
                $table->tinyInteger('bloque_gerencia')->default(0)->after('bloque_proyectos');
            }
            if (! Schema::hasColumn('sgc_usuarios', 'bloque_patio')) {
                $table->tinyInteger('bloque_patio')->default(0)->after('bloque_gerencia');
            }
            if (! Schema::hasColumn('sgc_usuarios', 'bloque_calidad')) {
                $table->tinyInteger('bloque_calidad')->default(0)->after('bloque_patio');
            }
            if (! Schema::hasColumn('sgc_usuarios', 'bloque_docs_legales')) {
                $table->tinyInteger('bloque_docs_legales')->default(0)->after('bloque_calidad');
            }
            if (! Schema::hasColumn('sgc_usuarios', 'bloque_formatos')) {
                $table->tinyInteger('bloque_formatos')->default(0)->after('bloque_docs_legales');
            }
            if (! Schema::hasColumn('sgc_usuarios', 'bloque_listado_interes')) {
                $table->tinyInteger('bloque_listado_interes')->default(0)->after('bloque_formatos');
            }
        });

        // Activar todos los bloques nuevos para Super Admins
        DB::table('sgc_usuarios')
            ->where('id_perfil', 1)
            ->update([
                'bloque_gerencia'        => 1,
                'bloque_patio'           => 1,
                'bloque_calidad'         => 1,
                'bloque_docs_legales'    => 1,
                'bloque_formatos'        => 1,
                'bloque_listado_interes' => 1,
            ]);
    }

    public function down(): void
    {
        Schema::table('sgc_usuarios', function (Blueprint $table) {
            $cols = [
                'bloque_gerencia',
                'bloque_patio',
                'bloque_calidad',
                'bloque_docs_legales',
                'bloque_formatos',
                'bloque_listado_interes',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('sgc_usuarios', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
