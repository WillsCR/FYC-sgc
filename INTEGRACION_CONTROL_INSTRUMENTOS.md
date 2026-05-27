# 🚀 INTEGRACIÓN: Control de Instrumentos - PHP Legacy a Laravel

## 📍 UBICACIONES
- **PHP Legacy**: `c:\Users\guill\Downloads\sgc`
- **Nuevo Laravel**: `C:\xampp\htdocs\sgc-project\FYC-sgc`

---

## 📦 ARCHIVOS A COPIAR DEL PROYECTO LEGACY

### 1. Script SQL (Base de Datos)
**Origen:**
```
c:\Users\guill\Downloads\sgc\db\migracion_archivos_equipos.sql
```

**Destino en Laravel:**
```
C:\xampp\htdocs\sgc-project\FYC-sgc\database\migrations\2026_05_25_000000_create_sgc_archivos_equipos_table.php
```

---

### 2. Clase Manager (PHP)
**Origen:**
```
c:\Users\guill\Downloads\sgc\inc\ArchivoEquipoManager.php
```

**Destino en Laravel - convertida a Service:**
```
C:\xampp\htdocs\sgc-project\FYC-sgc\app\Services\ArchivoEquipoService.php
```

**Cambios necesarios:**
```php
// Cambiar constructor
// ANTES: public function __construct($mysqli)
// DESPUÉS: public function __construct(private Application $app)

// Cambiar queries
// ANTES: $this->mysqli->query()
// DESPUÉS: DB::query() o Model::query()

// Cambiar rutas
// ANTES: './storage/nc_docs/'
// DESPUÉS: storage_path('app/nc_docs/')
```

---

### 3. Ejemplos de Upload (Referencia)
**Origen:**
```
c:\Users\guill\Downloads\sgc\inc\imagensubir8_nuevo.php
c:\Users\guill\Downloads\sgc\inc\imagensubir9_nuevo.php
```

**Destino en Laravel - Controller:**
```
C:\xampp\htdocs\sgc-project\FYC-sgc\app\Http\Controllers\ArchivoEquipoController.php
```

---

## 🔧 PASOS DE IMPLEMENTACIÓN

### PASO 1: Crear Migration desde SQL
```bash
cd C:\xampp\htdocs\sgc-project\FYC-sgc

# Crear archivo migration
php artisan make:migration create_sgc_archivos_equipos_table
```

**Archivo:** `database/migrations/XXXX_XX_XX_create_sgc_archivos_equipos_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sgc_archivos_equipos', function (Blueprint $table) {
            $table->id();
            
            // Identificación archivo
            $table->string('archivo_hash', 64)->unique()->comment('SHA256');
            $table->string('archivo_nombre');
            $table->string('archivo_extension', 10);
            $table->string('archivo_mime', 50);
            $table->integer('archivo_tamanio');
            
            // Rutas
            $table->string('ruta_legado')->nullable();
            $table->enum('ruta_tipo', ['legacy', 'hashed'])->default('hashed');
            $table->string('ruta_almacenamiento');
            
            // Relaciones
            $table->unsignedBigInteger('id_equipo_generico')->nullable();
            $table->unsignedBigInteger('id_programa_verificacion')->nullable();
            $table->unsignedBigInteger('id_equipo_interno')->nullable();
            
            // Tipo documento
            $table->enum('tipo_documento', [
                'imagen_general',
                'cert_calidad',
                'cert_calibracion',
                'manual',
                'protocolo',
                'inspecciones',
                'mantenimiento',
                'otro'
            ])->default('otro');
            
            // Metadata
            $table->unsignedBigInteger('id_usuario');
            $table->timestamp('fecha_subida')->useCurrent();
            $table->timestamp('fecha_modificacion')->useCurrent()->useCurrentOnUpdate();
            $table->text('descripcion')->nullable();
            $table->date('vigencia_hasta')->nullable();
            
            // Control
            $table->enum('estado', ['activo', 'inactivo', 'eliminado'])->default('activo');
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('reemplazo_de')->nullable();
            
            // Índices
            $table->index('tipo_documento');
            $table->index('id_programa_verificacion');
            $table->index('id_equipo_generico');
            $table->index('fecha_subida');
            $table->index('vigencia_hasta');
        });

        Schema::create('sgc_archivos_equipos_auditoria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_archivo');
            $table->string('accion');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->json('detalles')->nullable();
            
            $table->foreign('id_archivo')
                ->references('id')
                ->on('sgc_archivos_equipos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_archivos_equipos_auditoria');
        Schema::dropIfExists('sgc_archivos_equipos');
    }
};
```

**Ejecutar:**
```bash
php artisan migrate
```

---

### PASO 2: Crear Model
```bash
php artisan make:model ArchivoEquipo
```

**Archivo:** `app/Models/ArchivoEquipo.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoEquipo extends Model
{
    protected $table = 'sgc_archivos_equipos';
    public $timestamps = false;

    protected $fillable = [
        'archivo_hash',
        'archivo_nombre',
        'archivo_extension',
        'archivo_mime',
        'archivo_tamanio',
        'ruta_legado',
        'ruta_tipo',
        'ruta_almacenamiento',
        'id_equipo_generico',
        'id_programa_verificacion',
        'id_equipo_interno',
        'tipo_documento',
        'id_usuario',
        'descripcion',
        'vigencia_hasta',
        'estado',
        'version',
        'reemplazo_de',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
        'fecha_modificacion' => 'datetime',
        'vigencia_hasta' => 'date',
    ];

    // Accesors
    public function getUrlAttribute()
    {
        if ($this->ruta_tipo === 'legacy') {
            return url('inc/' . $this->ruta_almacenamiento);
        }
        return route('archivos.descargar', ['hash' => $this->archivo_hash]);
    }

    public function getEstadoVigenciaAttribute()
    {
        if (!$this->vigencia_hasta) {
            return 'sin_vigencia';
        }
        
        $dias = now()->diffInDays($this->vigencia_hasta);
        
        if ($dias < 0) return 'vencido';
        if ($dias < 30) return 'proximo_a_vencer';
        return 'vigente';
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function historial()
    {
        return ArchivoEquipo::where('id', $this->id)
            ->orWhere('reemplazo_de', $this->id)
            ->orderBy('version');
    }
}
```

---

### PASO 3: Crear Service (conversión de ArchivoEquipoManager)
```bash
mkdir -p app/Services
```

**Archivo:** `app/Services/ArchivoEquipoService.php`

```php
<?php

namespace App\Services;

use App\Models\ArchivoEquipo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ArchivoEquipoService
{
    private array $config = [
        'max_size' => 10485760, // 10 MB
        'tipos_permitidos' => [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ]
    ];

    private array $directorios = [
        'imagen_general' => 'verificacion_y_control',
        'cert_calidad' => 'cert_calidad_equipos',
        'cert_calibracion' => 'cert_calibrac_equipos',
        'manual' => 'manuales_equipos',
        'protocolo' => 'protocolos_equipos',
        'inspecciones' => 'inspecciones_equipos',
        'mantenimiento' => 'mantenimiento_equipos',
    ];

    public function subirArchivo($file, $id_usuario, $tipo_documento, 
                                 $id_equipo_generico = null, 
                                 $id_programa_verificacion = null,
                                 $id_equipo_interno = null,
                                 $descripcion = null,
                                 $vigencia_hasta = null)
    {
        // Validación
        $validacion = $this->validarArchivo($file, $tipo_documento);
        if (!$validacion['success']) {
            return $validacion;
        }

        // Hash
        $archivo_hash = hash_file('sha256', $file->getPathname());

        // Verificar duplicado
        $existe = ArchivoEquipo::where('archivo_hash', $archivo_hash)
            ->where('estado', 'activo')
            ->first();
        
        if ($existe) {
            return [
                'success' => false,
                'mensaje' => 'El archivo ya existe en el sistema',
                'id' => $existe->id
            ];
        }

        // Información del archivo
        $extension = $file->getClientOriginalExtension();
        $mime = $this->config['tipos_permitidos'][strtolower($extension)] ?? 'application/octet-stream';
        $tamanio = $file->getSize();

        // Guardar en storage
        $nombre_guardado = $archivo_hash . '.' . $extension;
        $directorio = $this->directorios[$tipo_documento];
        $path = Storage::disk('local')->putAs(
            'nc_docs/' . $directorio,
            $file,
            $nombre_guardado
        );

        // Crear registro en BD
        $archivo = ArchivoEquipo::create([
            'archivo_hash' => $archivo_hash,
            'archivo_nombre' => $file->getClientOriginalName(),
            'archivo_extension' => $extension,
            'archivo_mime' => $mime,
            'archivo_tamanio' => $tamanio,
            'ruta_tipo' => 'hashed',
            'ruta_almacenamiento' => $path,
            'id_equipo_generico' => $id_equipo_generico,
            'id_programa_verificacion' => $id_programa_verificacion,
            'id_equipo_interno' => $id_equipo_interno,
            'tipo_documento' => $tipo_documento,
            'id_usuario' => $id_usuario,
            'descripcion' => $descripcion,
            'vigencia_hasta' => $vigencia_hasta,
            'estado' => 'activo',
        ]);

        return [
            'success' => true,
            'id' => $archivo->id,
            'hash' => $archivo_hash,
            'mensaje' => 'Archivo subido correctamente'
        ];
    }

    public function obtenerArchivosPorPrograma($id_programa, $tipo_documento = null)
    {
        $query = ArchivoEquipo::where('id_programa_verificacion', $id_programa)
            ->where('estado', 'activo');

        if ($tipo_documento) {
            $query->where('tipo_documento', $tipo_documento);
        }

        return $query->orderBy('fecha_subida', 'desc')->get();
    }

    public function obtenerArchivosPorEquipo($id_equipo, $tipo_documento = null)
    {
        $query = ArchivoEquipo::where('id_equipo_generico', $id_equipo)
            ->where('estado', 'activo');

        if ($tipo_documento) {
            $query->where('tipo_documento', $tipo_documento);
        }

        return $query->orderBy('fecha_subida', 'desc')->get();
    }

    public function descargarArchivo($id_archivo, $id_usuario = null)
    {
        $archivo = ArchivoEquipo::findOrFail($id_archivo);

        if ($archivo->estado !== 'activo') {
            abort(404, 'Archivo no disponible');
        }

        // Registrar auditoria
        if ($id_usuario) {
            $archivo->auditoria()->create([
                'accion' => 'DESCARGA',
                'usuario_id' => $id_usuario,
            ]);
        }

        return Storage::download($archivo->ruta_almacenamiento, $archivo->archivo_nombre);
    }

    private function validarArchivo($file, $tipo_documento)
    {
        if (!$file->isValid()) {
            return ['success' => false, 'mensaje' => 'Error en la subida'];
        }

        if ($file->getSize() > $this->config['max_size']) {
            return ['success' => false, 'mensaje' => 'El archivo excede 10 MB'];
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!isset($this->config['tipos_permitidos'][$extension])) {
            return ['success' => false, 'mensaje' => 'Tipo de archivo no permitido'];
        }

        if (!isset($this->directorios[$tipo_documento])) {
            return ['success' => false, 'mensaje' => 'Tipo de documento inválido'];
        }

        return ['success' => true];
    }
}
```

---

### PASO 4: Crear Controller
```bash
php artisan make:controller ArchivoEquipoController --resource
```

**Archivo:** `app/Http/Controllers/ArchivoEquipoController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\ArchivoEquipo;
use App\Services\ArchivoEquipoService;
use Illuminate\Http\Request;

class ArchivoEquipoController extends Controller
{
    public function __construct(private ArchivoEquipoService $service) {}

    public function subirCertificado(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240',
            'id_programa' => 'required|integer',
            'tipo' => 'required|in:1,2', // 1=calidad, 2=calibracion
        ]);

        $tipo_documento = $request->tipo == 1 ? 'cert_calidad' : 'cert_calibracion';

        $resultado = $this->service->subirArchivo(
            $request->file('archivo'),
            auth()->id(),
            $tipo_documento,
            null,
            $request->id_programa,
            null,
            'Certificado subido',
            null
        );

        if ($resultado['success']) {
            return response()->json([
                'success' => true,
                'mensaje' => '✅ Archivo subido correctamente',
                'id' => $resultado['id']
            ]);
        }

        return response()->json([
            'success' => false,
            'mensaje' => '❌ ' . $resultado['mensaje']
        ], 400);
    }

    public function descargar(ArchivoEquipo $archivo)
    {
        return $this->service->descargarArchivo($archivo->id, auth()->id());
    }

    public function archivosPrograma($id_programa)
    {
        $archivos = $this->service->obtenerArchivosPorPrograma($id_programa);
        return response()->json($archivos);
    }
}
```

---

### PASO 5: Crear Rutas
**Archivo:** `routes/api.php` o `routes/web.php`

```php
Route::middleware('auth')->group(function () {
    Route::post('/archivos/subir', [ArchivoEquipoController::class, 'subirCertificado'])->name('archivos.subir');
    Route::get('/archivos/{archivo}/descargar', [ArchivoEquipoController::class, 'descargar'])->name('archivos.descargar');
    Route::get('/archivos/programa/{id}', [ArchivoEquipoController::class, 'archivosPrograma'])->name('archivos.programa');
});
```

---

## 🎯 CHECKLIST DE IMPLEMENTACIÓN

- [ ] Crear migration desde script SQL
- [ ] Ejecutar `php artisan migrate`
- [ ] Crear Model ArchivoEquipo
- [ ] Crear Service ArchivoEquipoService
- [ ] Crear Controller ArchivoEquipoController
- [ ] Definir rutas
- [ ] Crear carpeta `storage/app/nc_docs/`
- [ ] Actualizar controller de control_instrumentos
- [ ] Agregar AJAX para nueva ruta
- [ ] Testing con archivo de prueba

---

## 📞 REFERENCIAS

**Documentación original:** `c:\Users\guill\Downloads\sgc\docs\`
- MIGRACION_ARCHIVOS_EQUIPOS.md
- INTEGRACION_ARCHIVO_MANAGER.md
- FAQ.md

**Código original:** `c:\Users\guill\Downloads\sgc\`
- inc/ArchivoEquipoManager.php
- db/migracion_archivos_equipos.sql
- inc/imagensubir8_nuevo.php
- inc/imagensubir9_nuevo.php
