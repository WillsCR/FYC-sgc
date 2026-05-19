<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sprint 4 (Semanas 7-8) — Fixes en sgc_carpetas_permisos
 * Origen: sgc_changes.sql — sección SPRINT 4 (carpetas_permisos)
 *
 * - correo: varchar(50) → varchar(150)
 * - clave:  varchar(50) → varchar(255)  (bcrypt genera hashes de 60+ chars)
 * - carga, descarga, crear, ocultar_raiz, eliminar, editar: DEFAULT 0
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE `sgc_carpetas_permisos`
                MODIFY COLUMN `correo`       varchar(150) NOT NULL DEFAULT \'\',
                MODIFY COLUMN `clave`        varchar(255) NOT NULL DEFAULT \'\',
                MODIFY COLUMN `carga`        tinyint(1)   NOT NULL DEFAULT 0,
                MODIFY COLUMN `descarga`     tinyint(1)   NOT NULL DEFAULT 0,
                MODIFY COLUMN `crear`        tinyint(1)   NOT NULL DEFAULT 0,
                MODIFY COLUMN `ocultar_raiz` tinyint(1)   NOT NULL DEFAULT 0,
                MODIFY COLUMN `eliminar`     tinyint(1)   NOT NULL DEFAULT 0,
                MODIFY COLUMN `editar`       tinyint(1)   NOT NULL DEFAULT 0
        ');
    }

    public function down(): void
    {
        // Revertir solo los tamaños de varchar (los DEFAULT se dejan como están)
        DB::statement('
            ALTER TABLE `sgc_carpetas_permisos`
                MODIFY COLUMN `correo` varchar(50)  NOT NULL DEFAULT \'\',
                MODIFY COLUMN `clave`  varchar(50)  NOT NULL DEFAULT \'\'
        ');
    }
};
