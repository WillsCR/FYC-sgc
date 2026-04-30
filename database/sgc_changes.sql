-- ============================================================
-- SGC F&C Chile SPA — Cambios estructurales de BD
-- Archivo de sincronización para el equipo
-- Ejecutar en orden sobre la BD cfc48507_epp
-- ============================================================

-- ============================================================
-- SPRINT 2 — Nuevos bloques de módulos en sgc_usuarios
-- Fecha: Sprint 2 (Semanas 3-4)
-- ============================================================

ALTER TABLE `sgc_usuarios`
    ADD COLUMN `bloque_gerencia`          tinyint(1) NOT NULL DEFAULT 0 AFTER `bloque_proyectos`,
    ADD COLUMN `bloque_patio`             tinyint(1) NOT NULL DEFAULT 0 AFTER `bloque_gerencia`,
    ADD COLUMN `bloque_calidad`           tinyint(1) NOT NULL DEFAULT 0 AFTER `bloque_patio`,
    ADD COLUMN `bloque_docs_legales`      tinyint(1) NOT NULL DEFAULT 0 AFTER `bloque_calidad`,
    ADD COLUMN `bloque_formatos`          tinyint(1) NOT NULL DEFAULT 0 AFTER `bloque_docs_legales`,
    ADD COLUMN `bloque_listado_interes`   tinyint(1) NOT NULL DEFAULT 0 AFTER `bloque_formatos`;

-- Activar bloques nuevos para Super Admins
UPDATE `sgc_usuarios`
SET
    `bloque_gerencia`        = 1,
    `bloque_patio`           = 1,
    `bloque_calidad`         = 1,
    `bloque_docs_legales`    = 1,
    `bloque_formatos`        = 1,
    `bloque_listado_interes` = 1
WHERE `id_perfil` = 1;

-- ============================================================
-- SPRINT 4 — DEFAULT 0 en columnas de sgc_usuarios
-- Necesario para crear nuevos usuarios sin errores
-- Fecha: Sprint 4 (Semanas 7-8)
-- ============================================================

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
    MODIFY COLUMN `carga_nminutas`                 tinyint(1) NOT NULL DEFAULT 0;

-- ============================================================
-- SPRINT 4 — Fixes en sgc_carpetas_permisos
-- DEFAULT vacío en correo/clave, ampliar clave a 255 chars
-- varchar(50) no alcanzaba para hash bcrypt (60+ chars)
-- Fecha: Sprint 4 (Semanas 7-8)
-- ============================================================

ALTER TABLE `sgc_carpetas_permisos`
    MODIFY COLUMN `correo`       varchar(150) NOT NULL DEFAULT '',
    MODIFY COLUMN `clave`        varchar(255) NOT NULL DEFAULT '',
    MODIFY COLUMN `carga`        tinyint(1)   NOT NULL DEFAULT 0,
    MODIFY COLUMN `descarga`     tinyint(1)   NOT NULL DEFAULT 0,
    MODIFY COLUMN `crear`        tinyint(1)   NOT NULL DEFAULT 0,
    MODIFY COLUMN `ocultar_raiz` tinyint(1)   NOT NULL DEFAULT 0,
    MODIFY COLUMN `eliminar`     tinyint(1)   NOT NULL DEFAULT 0,
    MODIFY COLUMN `editar`       tinyint(1)   NOT NULL DEFAULT 0;

-- ============================================================
-- VERIFICACIÓN — Ejecutar al final para confirmar cambios
-- ============================================================

SELECT 'sgc_usuarios' AS tabla, COUNT(*) AS columnas
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sgc_usuarios'
UNION ALL
SELECT 'sgc_carpetas_permisos', COUNT(*)
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sgc_carpetas_permisos';


-- ============================================================
-- SGC F&C Chile SPA — Sprint 4
-- Tabla de áreas asignadas por usuario
-- Ejecutar en phpMyAdmin sobre cfc48507_epp
-- ============================================================

CREATE TABLE `sgc_usuarios_areas` (
    `id`         int(11)    NOT NULL AUTO_INCREMENT,
    `id_usuario` int(11)    NOT NULL,
    `id_area`    int(11)    NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_usuario_area` (`id_usuario`, `id_area`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Verificar que quedó creada
SHOW COLUMNS FROM `sgc_usuarios_areas`;

-- ============================================================
-- SGC F&C Chile SPA — Sprint 4
-- Tabla de permisos por área para planificación y minutas
-- Ejecutar en phpMyAdmin sobre cfc48507_epp
-- ============================================================

CREATE TABLE `sgc_usuarios_permisos_area` (
    `id`                   int(11)    NOT NULL AUTO_INCREMENT,
    `id_usuario`           int(11)    NOT NULL,
    `id_area`              int(11)    NOT NULL,
    -- Permisos de planificación
    `ver_planificacion`    tinyint(1) NOT NULL DEFAULT 0,
    `editar_planificacion` tinyint(1) NOT NULL DEFAULT 0,
    -- Permisos de minutas
    `ver_minutas`          tinyint(1) NOT NULL DEFAULT 0,
    `editar_minutas`       tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_usuario_area` (`id_usuario`, `id_area`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Agregar también la tabla de áreas si no tienes la anterior
-- (solo si no ejecutaste el SQL de fix-areas antes)
-- CREATE TABLE `sgc_usuarios_areas` ... (ya ejecutado)

-- Verificar
SHOW COLUMNS FROM `sgc_usuarios_permisos_area`;


ALTER TABLE sgc_usuarios 
ADD COLUMN bloque_finanzas tinyint(1) NOT NULL DEFAULT 0 AFTER bloque_proyectos;

ALTER TABLE sgc_documentos MODIFY COLUMN tipo_mime VARCHAR(255) NOT NULL DEFAULT '';

-- Luego de hacer todas las querys se debe hacer un php artisan migrate