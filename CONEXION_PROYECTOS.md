# 🔗 CONEXIÓN DE PROYECTOS - Legacy ↔ Laravel

## 📍 Dos Workspaces en VS Code

### Workspace 1: PHP Legacy (Original)
```
📁 c:\Users\guill\Downloads\sgc
   ├── 📄 control_instrumentos.php (Actual)
   ├── 📁 inc/
   │   ├── ArchivoEquipoManager.php ← COPIAR
   │   ├── imagensubir8_nuevo.php ← REFERENCIA
   │   ├── imagensubir9_nuevo.php ← REFERENCIA
   │   └── epa.php
   ├── 📁 db/
   │   └── migracion_archivos_equipos.sql ← COPIAR
   ├── 📁 docs/ ← REFERENCIA
   │   ├── MIGRACION_ARCHIVOS_EQUIPOS.md
   │   ├── INTEGRACION_ARCHIVO_MANAGER.md
   │   ├── FAQ.md
   │   └── INDICE_ARCHIVOS.md
   └── 📁 storage/nc_docs/ ← Nuevo (para archivos)
```

### Workspace 2: Laravel (Nuevo)
```
📁 C:\xampp\htdocs\sgc-project\FYC-sgc
   ├── 📄 INTEGRACION_CONTROL_INSTRUMENTOS.md ← AQUÍ (pasos detallados)
   ├── 📄 REFERENCIA_RAPIDA.md ← AQUÍ (resumen rápido)
   ├── 📁 app/
   │   ├── Models/
   │   │   └── ArchivoEquipo.php ← CREAR
   │   ├── Services/
   │   │   └── ArchivoEquipoService.php ← CREAR (basado en ArchivoEquipoManager)
   │   └── Http/Controllers/
   │       └── ArchivoEquipoController.php ← CREAR
   ├── 📁 database/
   │   └── migrations/
   │       └── XXXX_XX_XX_create_sgc_archivos_equipos_table.php ← CREAR
   ├── 📁 routes/
   │   └── web.php ← AGREGAR RUTAS
   └── 📁 storage/app/nc_docs/ ← CREAR
       ├── verificacion_y_control/
       ├── cert_calidad_equipos/
       └── cert_calibrac_equipos/
```

---

## 🔄 FLUJO DE INFORMACIÓN

```
PASO 1: Copiar desde Legacy
┌─────────────────────────────────────────┐
│ Código original en PHP Legacy           │
│ - ArchivoEquipoManager.php              │
│ - migracion_archivos_equipos.sql        │
│ - imagensubir8_nuevo.php (referencia)  │
│ - imagensubir9_nuevo.php (referencia)  │
└──────────────┬──────────────────────────┘
               │
               ↓
PASO 2: Convertir a Laravel
┌─────────────────────────────────────────┐
│ Nuevos componentes en Laravel           │
│ - Migration (de SQL)                    │
│ - Model ArchivoEquipo                   │
│ - Service (de Manager)                  │
│ - Controller                            │
│ - Routes                                │
└──────────────┬──────────────────────────┘
               │
               ↓
PASO 3: Base de Datos Compartida
┌─────────────────────────────────────────┐
│ Tabla: sgc_archivos_equipos             │
│ (funciona en ambos sistemas)            │
│                                          │
│ PHP Legacy → Query directo              │
│ Laravel → Eloquent Model                │
└─────────────────────────────────────────┘
```

---

## 📋 CHECKLIST: DE LEGACY A LARAVEL

### Fase 1: Preparación (5 min)
- [ ] Backup de BD actual
- [ ] Revisar `c:\Users\guill\Downloads\sgc\IMPLEMENTACION_RESUMEN.md`
- [ ] Abrir ambos workspaces en VS Code

### Fase 2: Copiar Base de Datos (10 min)
- [ ] Copiar: `db/migracion_archivos_equipos.sql`
- [ ] Crear Migration Laravel
- [ ] Ejecutar: `php artisan migrate`

### Fase 3: Crear Componentes Laravel (40 min)
- [ ] Crear Model: `ArchivoEquipo`
- [ ] Crear Service: `ArchivoEquipoService` (adaptar Manager)
- [ ] Crear Controller: `ArchivoEquipoController`
- [ ] Crear Routes

### Fase 4: Testing (20 min)
- [ ] Subir archivo de prueba
- [ ] Verificar en BD
- [ ] Descargar archivo
- [ ] Revisar auditoría

### Fase 5: Integración (30 min)
- [ ] Actualizar `control_instrumentos.php` en Laravel
- [ ] Cambiar llamadas AJAX a nuevas rutas
- [ ] Testing en vista

---

## 🎯 QUÉ HACER HOY

### En VS Code: Abrir Ambos Proyectos en Tabs

**Tab 1: PHP Legacy**
- Abrir carpeta: `c:\Users\guill\Downloads\sgc`
- Referencia para copiar código

**Tab 2: Laravel**
- Abrir carpeta: `C:\xampp\htdocs\sgc-project\FYC-sgc`
- Carpeta donde trabajarás

### En Laravel: Seguir Pasos

1. Abre: `INTEGRACION_CONTROL_INSTRUMENTOS.md` (en tu project)
2. Paso 1: Crear Migration
3. Paso 2: Crear Model
4. Paso 3: Crear Service
5. Paso 4: Crear Controller
6. Paso 5: Crear Routes

---

## 💾 GUARDAR INFORMACIÓN

### En tu cabeza: Recordar
- Legacy está en: `c:\Users\guill\Downloads\sgc`
- Nuevo Laravel en: `C:\xampp\htdocs\sgc-project\FYC-sgc`
- Tabla centralizada: `sgc_archivos_equipos`

### En archivos: Leer
- Quick Start: `REFERENCIA_RAPIDA.md` (este proyecto)
- Detailed: `INTEGRACION_CONTROL_INSTRUMENTOS.md` (este proyecto)
- Original Docs: `c:\Users\guill\Downloads\sgc\docs\`

### En BD: Ejecutar
```sql
-- Script en legacy:
c:\Users\guill\Downloads\sgc\db\migracion_archivos_equipos.sql

-- Luego Laravel migra en su propia BD:
php artisan migrate
```

---

## 🚀 INICIO RÁPIDO

### Ahora mismo
```bash
# 1. En Laravel, crear migration
php artisan make:migration create_sgc_archivos_equipos_table

# 2. Editar archivo generado y copiar estructura de:
#    c:\Users\guill\Downloads\sgc\db\migracion_archivos_equipos.sql

# 3. Ejecutar
php artisan migrate

# 4. Crear Model
php artisan make:model ArchivoEquipo

# 5. Crear Service
mkdir -p app/Services
# Copiar lógica de: c:\Users\guill\Downloads\sgc\inc\ArchivoEquipoManager.php

# 6. Crear Controller
php artisan make:controller ArchivoEquipoController

# 7. Agregar rutas en routes/web.php
```

---

## 📌 NO PIERDA LA LÓGICA

### Archivos importantes guardados en:

**Workspace 1 (Legacy)**
- ✅ Lógica original: `c:\Users\guill\Downloads\sgc\inc\ArchivoEquipoManager.php`
- ✅ SQL: `c:\Users\guill\Downloads\sgc\db\migracion_archivos_equipos.sql`
- ✅ Documentación: `c:\Users\guill\Downloads\sgc\docs\*.md`

**Workspace 2 (Laravel)**
- ✅ Referencias: `INTEGRACION_CONTROL_INSTRUMENTOS.md`
- ✅ Resumen: `REFERENCIA_RAPIDA.md`

**Memoria de Copilot**
- ✅ Guardado en: `/memories/sgc_proyectos_config.md`

---

## ✅ VERIFICACIÓN

Para no perder la lógica, verifica que tienes:

- [ ] Dos workspaces abiertos en VS Code
- [ ] Archivo: `INTEGRACION_CONTROL_INSTRUMENTOS.md` (Laravel)
- [ ] Archivo: `REFERENCIA_RAPIDA.md` (Laravel)
- [ ] Acceso a: `c:\Users\guill\Downloads\sgc\docs\` (Legacy)
- [ ] Acceso a: `c:\Users\guill\Downloads\sgc\inc\ArchivoEquipoManager.php` (Legacy)
- [ ] Acceso a: `c:\Users\guill\Downloads\sgc\db\migracion_archivos_equipos.sql` (Legacy)

Si todas las cajas están marcadas, **no perderás la lógica** ✅

---

## 🎓 PRÓXIMA SESIÓN

Si empiezas una nueva sesión y no recuerdas dónde estabas:

1. Abre: `C:\xampp\htdocs\sgc-project\FYC-sgc\REFERENCIA_RAPIDA.md`
2. Lee el resumen (2 min)
3. Abre: `C:\xampp\htdocs\sgc-project\FYC-sgc\INTEGRACION_CONTROL_INSTRUMENTOS.md`
4. Continúa desde donde paraste

---

**Última Actualización:** May 25, 2026  
**Responsable:** Copilot + Usuario (guill)
