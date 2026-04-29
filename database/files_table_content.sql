-- =====================================================
-- ESTRUCTURA JERÁRQUICA DE MÓDULOS Y SUBMÓDULOS
-- =====================================================

-- Limpiar datos anteriores (opcional)
-- DELETE FROM sgc_carpetas3 WHERE id_padre = 0 OR id_padre > 0;
-- ALTER TABLE sgc_carpetas3 AUTO_INCREMENT = 1;

-- ─── MÓDULOS PRINCIPALES (nivel 0, id_padre = 0) ───────────────────────────

INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('Sistema Integrado Gestión',                 0, 0, NOW()),  -- id: 1
('Control Medio Ambiente',                    0, 0, NOW()),  -- id: 2
('Control Seguridad y Salud en el Trabajo',   0, 0, NOW()),  -- id: 3
('Control Abastecimiento e Infraestructura',  0, 0, NOW()),  -- id: 4
('Control Recursos Humanos',                  0, 0, NOW()),  -- id: 5
('Control Gerencia',                          0, 0, NOW()),  -- id: 6
('Control Proyectos',                         0, 0, NOW()),  -- id: 7
('Control Finanzas',                          0, 0, NOW());   -- id: 8

-- ─── SUBMÓDULOS SISTEMA INTEGRADO GESTIÓN (nivel 1, id_padre = 1) ──────────
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('No Conformidades',                           1, 1, NOW()),
('Instrumentos de Medición Certificación de Calidad', 1, 1, NOW()),
('Certificados de Calidad',                    1, 1, NOW()),
('Certificados de EPP',                        1, 1, NOW()),
('Formatos SIG',                               1, 1, NOW()),
('Documentos del SIG',                         1, 1, NOW()),
('Capacitaciones',                             1, 1, NOW()),
('Informes',                                   1, 1, NOW()),
('Auditorías',                                 1, 1, NOW());

-- ─── SUBMÓDULOS CONTROL MEDIO AMBIENTE (nivel 1, id_padre = 2) ──────────────
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('Sustancias y Residuos Peligrosos',          2, 1, NOW()),
('Control de Recursos',                        2, 1, NOW()),
('Huellas de Carbono',                         2, 1, NOW()),
('Capacitaciones',                             2, 1, NOW()),
('Informes',                                   2, 1, NOW()),
('Formatos',                                   2, 1, NOW()),
('Documentos',                                 2, 1, NOW()),
('Control Operativo',                          2, 1, NOW());

-- ─── SUBMÓDULOS CONTROL SST (nivel 1, id_padre = 3) ────────────────────────
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('Protocolo Minsal',                           3, 1, NOW()),
('DS 44',                                      3, 1, NOW()),
('CPHS',                                       3, 1, NOW()),
('Informes',                                   3, 1, NOW()),
('Capacitaciones',                             3, 1, NOW()),
('Formatos',                                   3, 1, NOW()),
('Documentos',                                 3, 1, NOW()),
('Control Operativo',                          3, 1, NOW());

-- ─── SUBMÓDULOS CONTROL ABASTECIMIENTO (nivel 1, id_padre = 4) ─────────────
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('Control Plan e Infraestructura',             4, 1, NOW()),
('Informes',                                   4, 1, NOW()),
('Formatos',                                   4, 1, NOW()),
('Documentos',                                 4, 1, NOW()),
('Capacitaciones',                             4, 1, NOW());

-- ─── SUBMÓDULOS CONTROL RRHH (nivel 1, id_padre = 5) ──────────────────────
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('Formatos',                                   5, 1, NOW()),
('Documentos',                                 5, 1, NOW()),
('Informes',                                   5, 1, NOW()),
('Capacitaciones',                             5, 1, NOW()),
('Cursos',                                     5, 1, NOW());

-- ─── SUBMÓDULOS CONTROL GERENCIA (nivel 1, id_padre = 6) ───────────────────
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('Informes',                                   6, 1, NOW());

-- ─── SUBMÓDULOS CONTROL PROYECTOS (nivel 1, id_padre = 7) ──────────────────
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('Contrato pozos',                             7, 1, NOW());

-- ─── SUBMÓDULOS CONTROL FINANZAS (nivel 1, id_padre = 8) ───────────────────
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('Informes',                                   8, 1, NOW());

-- =====================================================
-- VERIFICACIÓN
-- =====================================================
-- SELECT id, descripcion, id_padre, nivel FROM sgc_carpetas3 ORDER BY id;