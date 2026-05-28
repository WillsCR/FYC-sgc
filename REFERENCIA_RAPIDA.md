# 📌 REFERENCIA RÁPIDA - Control de Instrumentos

## 🎯 SOLUCIÓN COMPLETA ENTREGADA

### Problema Original
- Archivos sin hash (certificado.pdf, certificado(1).pdf)
- Rutas hardcodeadas
- Sin auditoría
- Difícil migrar a Laravel

### Solución Implementada
- ✅ Tabla centralizada: `sgc_archivos_equipos`
- ✅ SHA256 hash único
- ✅ Auditoría completa
- ✅ Versionado automático
- ✅ Compatible PHP + Laravel

---

## 📁 ARCHIVOS ORIGINALES (PHP Legacy)

En: `c:\Users\guill\Downloads\sgc\`

| Archivo | Descripción | Ubicación |
|---------|-------------|-----------|
| `db/migracion_archivos_equipos.sql` | Script SQL con tabla + migración | BD |
| `inc/ArchivoEquipoManager.php` | Clase principal (10+ métodos) | Service |
| `inc/imagensubir8_nuevo.php` | Upload imágenes | Controller |
| `inc/imagensubir9_nuevo.php` | Upload certificados | Controller |
| `descargar_archivo.php` | Descarga segura | Route |

---

## 🚀 ADAPTACIÓN A LARAVEL

### 1. Crear Migration
```bash
php artisan make:migration create_sgc_archivos_equipos_table
```

Copiar estructura de `migracion_archivos_equipos.sql`

### 2. Crear Model
```bash
php artisan make:model ArchivoEquipo
```

### 3. Crear Service
Convertir `ArchivoEquipoManager.php` a Laravel Service

### 4. Crear Controller
```bash
php artisan make:controller ArchivoEquipoController --resource
```

Lógica: Inspirarse en `imagensubir8_nuevo.php` y `imagensubir9_nuevo.php`

### 5. Crear Rutas
```php
Route::post('/archivos/subir', [ArchivoEquipoController::class, 'subirCertificado']);
Route::get('/archivos/{archivo}/descargar', [ArchivoEquipoController::class, 'descargar']);
```

---

## 💡 COMPONENTES CLAVE

### Tabla: `sgc_archivos_equipos`
```
- id (PK)
- archivo_hash (UNIQUE SHA256)
- archivo_nombre (original)
- tipo_documento (enum: imagen, cert_calidad, cert_calibracion, etc)
- id_programa_verificacion (FK)
- vigencia_hasta (DATE nullable)
- version (INT)
- estado (ENUM: activo, inactivo, eliminado)
- reemplazo_de (FK a versión anterior)
```

### Tabla: `sgc_archivos_equipos_auditoria`
```
- id (PK)
- id_archivo (FK)
- accion (DESCARGA, SUBIDA, ELIMINACION, etc)
- usuario_id (FK)
- fecha (TIMESTAMP)
- detalles (JSON)
```

---

## 📋 MÉTODOS PRINCIPALES (Service)

```php
// Subir
subirArchivo($file, $id_usuario, $tipo_documento, ...): array

// Obtener
obtenerArchivosPorPrograma($id_programa, $tipo = null): Collection
obtenerArchivosPorEquipo($id_equipo, $tipo = null): Collection
obtenerArchivo($id): Model

// Descargar
descargarArchivo($id_archivo, $id_usuario): Response

// Gestionar
reemplazarArchivo($id_viejo, $file, $id_usuario): array
eliminarArchivo($id, $id_usuario): array
obtenerHistorialVersiones($id): Collection
```

---

## 🔗 FLUJO EN LARAVEL

```
POST /archivos/subir (AJAX)
    ↓
ArchivoEquipoController@subirCertificado
    ↓
ArchivoEquipoService@subirArchivo
    ↓
✅ Validación
✅ Hash SHA256
✅ Detectar duplicados
✅ Guardar en storage/app/nc_docs/
✅ INSERT sgc_archivos_equipos
✅ Registrar auditoría
    ↓
Return JSON { success: true, id: 42, hash: "abc123..." }
```

### Descargar
```
GET /archivos/{archivo}/descargar
    ↓
ArchivoEquipoController@descargar
    ↓
✅ Validar autenticación
✅ Validar permiso
✅ Registrar en auditoría
    ↓
response()->download()
```

---

## ✨ VENTAJAS

| Antes | Después |
|-------|---------|
| Nombres genéricos | SHA256 único ✅ |
| Rutas hardcodeadas | Centralizadas ✅ |
| Duplicados | Detectados ✅ |
| Sin auditoría | Tabla completa ✅ |
| Difícil migrar | Arquitectura limpia ✅ |

---

## 📚 DOCUMENTACIÓN DETALLADA

Ver: `c:\Users\guill\Downloads\sgc\docs\`

1. `IMPLEMENTACION_RESUMEN.md` - Resumen ejecutivo (10 min)
2. `MIGRACION_ARCHIVOS_EQUIPOS.md` - Guía completa (15 min)
3. `INTEGRACION_ARCHIVO_MANAGER.md` - Integración en PHP (20 min)
4. `FAQ.md` - Preguntas frecuentes (30 min)
5. `INDICE_ARCHIVOS.md` - Índice completo (10 min)

---

## ⏱️ TIEMPO ESTIMADO

- Crear Migration: 15 min
- Crear Model: 10 min
- Crear Service: 30 min
- Crear Controller: 20 min
- Crear Rutas: 5 min
- Testing: 20 min
- **Total: ~1.5 horas**

---

## 🎯 SIGUIENTE PASO

1. Abre: `C:\xampp\htdocs\sgc-project\FYC-sgc\INTEGRACION_CONTROL_INSTRUMENTOS.md`
2. Sigue los 5 pasos de implementación
3. Consulta archivos originales en `c:\Users\guill\Downloads\sgc\`

---

**Fecha**: May 25, 2026  
**Proyecto Legacy**: `c:\Users\guill\Downloads\sgc`  
**Nuevo Proyecto**: `C:\xampp\htdocs\sgc-project\FYC-sgc`
