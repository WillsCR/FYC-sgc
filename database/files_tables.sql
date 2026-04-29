
-- 1. Tabla de carpetas con jerarquía
CREATE TABLE IF NOT EXISTS `sgc_carpetas3` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_padre` bigint unsigned NOT NULL DEFAULT 0 COMMENT 'ID de la carpeta padre (0 = raíz)',
  `nivel` int NOT NULL DEFAULT 0 COMMENT 'Profundidad en la jerarquía',
  `creada_el` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ruta` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta legacy si aplica',
  PRIMARY KEY (`id`),
  KEY `id_padre` (`id_padre`),
  KEY `nivel` (`nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla centralizada de documentos (PDFs/archivos)
CREATE TABLE IF NOT EXISTS `sgc_documentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `archivo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID del archivo, ej: 550e8400-e29b-41d4-a716-446655440000.pdf',
  `nombre_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del archivo original para mostrar',
  `tipo_mime` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'application/pdf',
  `tamaño` bigint NOT NULL DEFAULT 0 COMMENT 'Tamaño en bytes',
  `hash_md5` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Hash MD5 para detectar duplicados',
  `creado_por` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email del usuario que subió',
  `creada_el` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modificada_el` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `archivo` (`archivo`),
  UNIQUE KEY `hash_md5` (`hash_md5`),
  KEY `creada_el` (`creada_el`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de relación: carpeta → documento (N:M)
CREATE TABLE IF NOT EXISTS `sgc_carpetas_contenido3` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_carpeta` bigint unsigned NOT NULL,
  `id_documento` bigint unsigned NOT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: "Auditoría Q1 2026"',
  `metadata` json DEFAULT NULL COMMENT 'Datos extra en JSON: {"version":"2.0", "revision":"1"}',
  `creada_el` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modificada_el` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_carpeta_documento` (`id_carpeta`, `id_documento`),
  KEY `id_documento` (`id_documento`),
  KEY `creada_el` (`creada_el`),
  CONSTRAINT `fk_carpetas_contenido3_carpeta` FOREIGN KEY (`id_carpeta`) REFERENCES `sgc_carpetas3` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_carpetas_contenido3_documento` FOREIGN KEY (`id_documento`) REFERENCES `sgc_documentos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS DE PRUEBA
-- =====================================================

-- Insertar carpetas de ejemplo
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('RRHH', 0, 0, NOW()),
('Calidad', 0, 0, NOW()),
('Procesos', 0, 0, NOW());

-- Submódulos
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('Capacitaciones', 1, 1, NOW()),     -- id: 4, padre: RRHH (id: 1)
('Evaluaciones', 1, 1, NOW()),       -- id: 5, padre: RRHH
('Auditorías', 2, 1, NOW()),         -- id: 6, padre: Calidad (id: 2)
('Reportes', 3, 1, NOW());           -- id: 7, padre: Procesos (id: 3)

-- Sub-subcarpetas
INSERT INTO `sgc_carpetas3` (`descripcion`, `id_padre`, `nivel`, `creada_el`) VALUES
('2024', 4, 2, NOW()),               -- id: 8, padre: Capacitaciones
('2025', 4, 2, NOW()),               -- id: 9, padre: Capacitaciones
('Internas', 6, 2, NOW());           -- id: 10, padre: Auditorías

-- Insertar un documento de ejemplo
INSERT INTO `sgc_documentos` (`archivo`, `nombre_original`, `tipo_mime`, `tamaño`, `hash_md5`, `creado_por`, `creada_el`) VALUES
('550e8400-e29b-41d4-a716-446655440000.pdf', 'Informe_Calidad_Q1_2026.pdf', 'application/pdf', 2500000, 'abc123def456ghi789jkl012mno345pq', 'admin@empresa.com', NOW()),
('660e8400-e29b-41d4-a716-446655440001.pdf', 'Capacitacion_Excel_Basico.pdf', 'application/pdf', 1800000, 'xyz987uvw654tsr321onm098lkj765ih', 'rrhh@empresa.com', NOW());

-- Relacionar documentos con carpetas
INSERT INTO `sgc_carpetas_contenido3` (`id_carpeta`, `id_documento`, `descripcion`, `metadata`, `creada_el`) VALUES
(6, 1, 'Auditoría Interna Q1 2026', JSON_OBJECT('revisada', true, 'revisor', 'gerente'), NOW()),
(8, 2, 'Capacitación Excel - Nivel Básico', JSON_OBJECT('duracion_horas', 4, 'participantes', 15), NOW());