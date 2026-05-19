<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sprint 4 (Semanas 7-8) — DEFAULT 0 en columnas tinyint de sgc_usuarios
 * Origen: sgc_changes.sql — sección SPRINT 4
 *
 * Necesario para crear nuevos usuarios sin errores de NOT NULL sin default.
 * Se usan sentencias RAW porque MODIFY COLUMN requiere la definición completa
 * y Laravel's change() necesita doctrine/dbal.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE `sgc_usuarios`
                MODIFY COLUMN `planificacion`                  tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `editar_planificacion`           tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `agregar_planificacion`          tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `agregar_minutas`                tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_pozos`                      tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_cursos`                     tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_btn_matriz`                 tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_btn_maq`                    tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_btn_ctrl_pozos`             tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_btn_rrhh`                   tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ocultar_lateral`                tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_pozos`                    tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_control_instrumentos`       tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_control_no_conformidades`   tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_sig`                        tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_sig`                      tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `editar_control_instrumentos`    tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `editar_cursos`                  tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_sig`                     tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_seguridad`               tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_ambiente`                tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_rrhh`                    tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_abastecimiento`          tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_proyectos`               tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_gerencia`                tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_patio`                   tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_calidad`                 tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_docs_legales`            tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_formatos`                tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `bloque_listado_interes`         tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_no_conformidades`         tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_paritario`                  tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_paritario`                tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_minsal`                     tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_minsal`                   tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_ds44`                       tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_ds44`                     tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_susres`                     tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_susres`                   tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_recres`                     tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_recres`                   tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_certcal`                    tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_certcal`                  tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_epp`                        tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_epp`                      tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_man_infra`                  tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_man_infra`                tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `ver_nminutas`                   tinyint(1) NOT NULL DEFAULT 0,
                MODIFY COLUMN `carga_nminutas`                 tinyint(1) NOT NULL DEFAULT 0
        ');
    }

    public function down(): void
    {
        // No se puede revertir de forma segura: se desconoce el DEFAULT original
        // de cada columna. Esta migración es solo aditiva (agrega DEFAULT 0).
    }
};
