# SGC — F&C Chile SPA · Control y Gestión Transversal

Sistema de gestión corporativa modernizado con Laravel 11.  
Migración incremental desde PHP legacy usando **Strangler Pattern**.

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.x + Laravel 11 |
| Frontend | Blade + Tailwind CSS (CDN) + Vanilla JS |
| Base de datos | MySQL — BD existente `cfc48507_epp` (compartida) |
| Servidor dev | XAMPP (Apache + MySQL) |

---

## Instalación local (XAMPP)

### 1. Clonar el repositorio

```bash
git clone https://github.com/WillsCR/FYC-sgc.git
cd FYC-sgc
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con los datos de conexión a la BD:

```env
DB_DATABASE=cfc48507_epp
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Verificar conexión a la BD (sin correr migraciones)

```bash
php artisan tinker
>>> DB::table('sgc_usuarios')->count();
# Debe devolver el número de usuarios — si falla, revisar .env
```

### 5. Configurar Virtual Host en XAMPP (recomendado)

Agregar en `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/FYC-sgc/public"
    ServerName sgc.local
    <Directory "C:/xampp/htdocs/FYC-sgc/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Agregar en `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1   sgc.local
```

O acceder directamente vía: `http://localhost/FYC-sgc/public`

---

## Estructura del proyecto

```
FYC-sgc/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/AuthController.php   ← Login, logout, migración hash
│   │   │   └── PanelController.php       ← Panel principal
│   │   └── Middleware/
│   │       ├── AutenticadoMiddleware.php ← Protege rutas con sesión
│   │       └── AdminMiddleware.php       ← Solo administradores
│   ├── Models/
│   │   ├── Usuario.php                   ← sgc_usuarios (60+ columnas permiso)
│   │   └── CarpetaPermiso.php            ← sgc_carpetas_permisos
│   └── Services/
│       └── PermisoService.php            ← can(), require(), usuarioActual()
├── resources/views/
│   ├── layouts/app.blade.php             ← Layout principal con navbar
│   ├── auth/login.blade.php              ← Pantalla de login
│   └── panel/index.blade.php            ← Panel (se expande en Sprint 2)
├── routes/web.php                        ← Todas las rutas del sistema
├── public/css/app.css                    ← Design system — Propuesta A
└── .env.example                          ← Variables de entorno del proyecto
```

---

## Flujo de autenticación

```
GET  /login  → login.blade.php (formulario)
POST /login  → AuthController@login
                 ├── Busca usuario por email en sgc_usuarios
                 ├── Verifica clave (bcrypt O sha1+md5 legacy)
                 ├── Si hash es legacy → migra a bcrypt automáticamente
                 ├── session_regenerate() → previene session fixation
                 ├── Guarda en sesión: usuario_id, nombre, email, perfil, es_admin
                 └── Redirige a /panel
POST /logout → AuthController@logout → Session::flush() + invalidate()
```

---

## Sistema de permisos

Los permisos viven en dos lugares de la BD:

### 1. Permisos globales por módulo (`sgc_usuarios`)
Columnas booleanas: `bloque_sig`, `bloque_seguridad`, `bloque_ambiente`, `bloque_rrhh`, `bloque_abastecimiento`, `bloque_proyectos`, `ver_pozos`, `ver_cursos`, `carga_sig`, etc.

```php
// Verificar permiso global
PermisoService::can('bloque_sig');         // true/false
PermisoService::require('bloque_sig');     // aborta con 403 si no tiene permiso
```

### 2. Permisos granulares por carpeta (`sgc_carpetas_permisos`)
Columnas: `carga`, `descarga`, `crear`, `eliminar`, `editar`, `ocultar_raiz`

```php
// Verificar permiso en carpeta específica
PermisoService::can('carga', 'carpeta', $carpetaId);
PermisoService::require('eliminar', 'carpeta', $carpetaId);
```

Los **administradores** (id_perfil = 1) tienen acceso total automáticamente.

---

## Tablas clave de la BD (solo lectura — NO modificar estructura)

| Tabla | Propósito |
|-------|-----------|
| `sgc_usuarios` | Usuarios + 60+ columnas de permisos por módulo |
| `sgc_carpetas` | Árbol documental principal |
| `sgc_carpetas_permisos` | Permisos granulares por carpeta y usuario |
| `sgc_carpetas_contenido` | Archivos asociados a carpetas |
| `sgc_carpetas2` | Árbol documental módulos secundarios |
| `sgc_carpetas_contenido2` | Archivos de carpetas secundarias |
| `sgc_minutas` | Reuniones (cabecera) |
| `sgc_trabajadores_cursos` | Trabajadores para matriz de cursos |
| `sgc_control_cursos2` | Cursos ejecutados y vencimientos |
| `ser_perfiles` | Catálogo de perfiles (1=Admin, etc.) |
| `ser_conductas` | Config global (campo `promocion` usado en hash legacy) |

> **Regla de oro:** Las nuevas tablas del proyecto usan prefijo `sgc2_`.  
> Las tablas `sgc_*` existentes **nunca** se modifican estructuralmente.

---

## Sprints

| Sprint | Módulo | Semanas | Puntos |
|--------|--------|---------|--------|
| Setup  | Laravel + BD | Sem 1 parcial | 8 |
| Sprint 1 | Login y autenticación segura | Sem 1–2 | 26 |
| Sprint 2 | Panel principal + bloques | Sem 3–4 | 29 |
| Sprint 3 | Gestión documental | Sem 5–6 | 31 |
| Sprint 4 | Usuarios + permisos + planificación | Sem 7–8 | 34 |
| Sprint 5 | Minutas + QA + deploy | Sem 9–10 | 28 |

---

## Equipo

Proyecto Capstone 2026 — F&C Chile SPA  
Repositorio: https://github.com/WillsCR/FYC-sgc
