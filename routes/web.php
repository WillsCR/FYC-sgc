<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\MetricasController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PlanificacionController;
use App\Http\Controllers\MinutaController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ArchivoEquipoController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\NoConformidadController;
use Illuminate\Support\Facades\Route;

// ─── Rutas públicas ──────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ─── Rutas protegidas ────────────────────────────────────────────────────────
Route::middleware(['auth.sgc'])->group(function () {

    // Panel y métricas
    Route::get('/panel',          [PanelController::class,    'index'])->name('panel');
    Route::post('/panel/modulo',             [PanelController::class, 'crearModulo'])   ->name('panel.crear.modulo');
    Route::post('/panel/modulo/{id}/submodulo', [PanelController::class, 'crearSubmódulo'])->name('panel.crear.submodulo');
    Route::put('/panel/modulo/{id}',         [PanelController::class, 'actualizarModulo'])->name('panel.actualizar.modulo');
    Route::delete('/panel/modulo/{id}',      [PanelController::class, 'destroyModulo'])  ->name('panel.eliminar.modulo');
    Route::get('/metricas',       [MetricasController::class, 'index'])->name('metricas');
    Route::get('/metricas/excel', [MetricasController::class, 'exportarExcel'])->name('metricas.excel');
    Route::post('/metricas/pdf',  [MetricasController::class, 'exportarPdf'])  ->name('metricas.pdf');

    // Sprint 3 — Gestión documental
    Route::get('/carpetas',                  [CarpetaController::class, 'index'])->name('carpetas.index');
    Route::get('/carpetas/{id}',             [CarpetaController::class, 'show'])->name('carpetas.show');
    Route::get('/carpetas/{id}/hijos',       [CarpetaController::class, 'hijos'])->name('carpetas.hijos');
    Route::get('/carpetas/{id}/buscar',      [CarpetaController::class, 'buscar'])->name('carpetas.buscar');
    Route::post('/carpetas/{id}/store',         [CarpetaController::class, 'store'])->name('carpetas.store');
    Route::delete('/submodulos/{id}/cascade',   [CarpetaController::class, 'destroySubmodulo'])->name('submodulos.destroy');
    Route::delete('/carpetas/{id}',             [CarpetaController::class, 'destroy'])->name('carpetas.destroy');
    Route::post('/archivos/subir',           [ArchivoController::class, 'subir'])->name('archivos.subir');
    Route::delete('/archivos/lote',          [ArchivoController::class, 'eliminarLote'])->name('archivos.eliminar.lote');
    Route::get('/archivos/{id}/ver',         [ArchivoController::class, 'ver'])->name('archivos.ver');
    Route::get('/archivos/{id}/descargar',   [ArchivoController::class, 'descargar'])->name('archivos.descargar');
    Route::delete('/archivos/{id}',          [ArchivoController::class, 'eliminar'])->name('archivos.eliminar');

    // Gestión de áreas
    Route::get('/areas',            [AreaController::class, 'index'])  ->name('areas.index');
    Route::post('/areas',           [AreaController::class, 'store'])  ->name('areas.store');
    Route::put('/areas/{id}',       [AreaController::class, 'update']) ->name('areas.update');
    Route::delete('/areas/{id}',    [AreaController::class, 'destroy'])->name('areas.destroy');

    // Sprint 4 — Gestión de usuarios
    Route::get('/usuarios',              [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/nuevo',        [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios',             [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{id}/editar',  [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}',         [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}',      [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    // Sprint 4 — Planificación
    Route::get('/planificacion',                   [PlanificacionController::class, 'index'])->name('planificacion.index');
    Route::get('/planificacion/nueva',             [PlanificacionController::class, 'create'])->name('planificacion.create');
    Route::post('/planificacion',                  [PlanificacionController::class, 'store'])->name('planificacion.store');
    Route::get('/planificacion/{id}/editar',       [PlanificacionController::class, 'edit'])->name('planificacion.edit');
    Route::put('/planificacion/{id}',              [PlanificacionController::class, 'update'])->name('planificacion.update');
    Route::post('/planificacion/{id}/cerrar',      [PlanificacionController::class, 'cerrar'])->name('planificacion.cerrar');
    Route::delete('/planificacion/{id}',           [PlanificacionController::class, 'destroy'])->name('planificacion.destroy');
    Route::get('/planificacion/{id}/descargar',    [PlanificacionController::class, 'descargar'])->name('planificacion.descargar');

    // Sprint 5 — Información SIG y Medio Ambiente
    Route::get('/sig',                              [PublicacionController::class, 'sig'])        ->name('sig.index');
    Route::get('/ambiente',                         [PublicacionController::class, 'ambiente'])   ->name('ambiente.index');
    Route::post('/publicaciones',                   [PublicacionController::class, 'store'])      ->name('publicaciones.store');
    Route::patch('/publicaciones/{id}',             [PublicacionController::class, 'actualizar']) ->name('publicaciones.actualizar');
    Route::get('/publicaciones/{id}/ver',           [PublicacionController::class, 'ver'])        ->name('publicaciones.ver');
    Route::get('/publicaciones/{id}/descargar',     [PublicacionController::class, 'descargar'])  ->name('publicaciones.descargar');
    Route::delete('/publicaciones/{id}',            [PublicacionController::class, 'eliminar'])   ->name('publicaciones.eliminar');

    // Sprint 5 — Minutas
    Route::get('/minutas',               [MinutaController::class, 'index'])  ->name('minutas.index');
    Route::get('/minutas/crear',         [MinutaController::class, 'create']) ->name('minutas.create');
    Route::post('/minutas',              [MinutaController::class, 'store'])  ->name('minutas.store');
    Route::get('/minutas/{id}',          [MinutaController::class, 'show'])     ->name('minutas.show');
    Route::get('/minutas/{id}/editar',   [MinutaController::class, 'edit'])     ->name('minutas.edit');
    Route::get('/minutas/{id}/pdf',      [MinutaController::class, 'descargar'])->name('minutas.pdf');
    Route::put('/minutas/{id}',          [MinutaController::class, 'update'])   ->name('minutas.update');
    Route::delete('/minutas/{id}',       [MinutaController::class, 'destroy'])  ->name('minutas.destroy');

    // No Conformidades
    Route::get('/no-conformidades',            [NoConformidadController::class, 'index'])      ->name('nc.index');
    Route::post('/no-conformidades/importar',  [NoConformidadController::class, 'importar'])   ->name('nc.importar');
    Route::get('/no-conformidades/exportar',   [NoConformidadController::class, 'exportar'])   ->name('nc.exportar');
    Route::post('/no-conformidades',           [NoConformidadController::class, 'store'])      ->name('nc.store');
    Route::get('/no-conformidades/{id}/datos', [NoConformidadController::class, 'datos'])      ->name('nc.datos');
    Route::put('/no-conformidades/{id}',       [NoConformidadController::class, 'update'])     ->name('nc.update');
    Route::delete('/no-conformidades/{id}',    [NoConformidadController::class, 'destroy'])    ->name('nc.destroy');
    Route::delete('/nc/doc/{id}',              [NoConformidadController::class, 'eliminarDoc'])->name('nc.doc.eliminar');
    Route::get('/nc/doc/{id}/ver',             [NoConformidadController::class, 'verDoc'])     ->name('nc.doc.ver');

    // Sprint 6 — Videos
    Route::get('/videos',                [VideoController::class, 'index'])    ->name('videos.index');
    Route::post('/videos',               [VideoController::class, 'store'])    ->name('videos.store');
    Route::get('/videos/{id}/stream',    [VideoController::class, 'stream'])         ->name('videos.stream');
    Route::get('/videos/{id}/descargar', [VideoController::class, 'descargar'])       ->name('videos.descargar');
    Route::put('/videos/{id}/bienvenida',[VideoController::class, 'setBienvenida'])   ->name('videos.bienvenida');
    Route::delete('/videos/{id}',        [VideoController::class, 'eliminar'])        ->name('videos.eliminar');

    // — Control de Instrumentos (Archivos de Equipos)
    Route::post('/archivos-equipos/subir-certificado',     [ArchivoEquipoController::class, 'subirCertificado'])->name('archivos-equipos.subir-certificado');
    Route::post('/archivos-equipos/subir-imagen',          [ArchivoEquipoController::class, 'subirImagen'])->name('archivos-equipos.subir-imagen');
    Route::get('/archivos-equipos/{archivo}/descargar',    [ArchivoEquipoController::class, 'descargar'])->name('archivos-equipos.descargar');
    Route::get('/archivos-equipos/{archivo}/ver',          [ArchivoEquipoController::class, 'ver'])->name('archivos-equipos.ver');
    Route::get('/archivos-equipos/programa/{id}',          [ArchivoEquipoController::class, 'archivosPrograma'])->name('archivos-equipos.programa');
    Route::get('/archivos-equipos/equipo/{id}',            [ArchivoEquipoController::class, 'archivosEquipo'])->name('archivos-equipos.equipo');
    Route::get('/archivos-equipos/{id}/historial',         [ArchivoEquipoController::class, 'historial'])->name('archivos-equipos.historial');
    Route::delete('/archivos-equipos/{archivo}',           [ArchivoEquipoController::class, 'eliminar'])->name('archivos-equipos.eliminar');
    Route::get('/archivos-equipos',                        [ArchivoEquipoController::class, 'index'])->name('archivos-equipos.index');
        // ─── Control de Instrumentos ─────────────────────────────────────────────────
    Route::prefix('control-instrumentos')->name('control-instrumentos.')->group(function () {
        Route::get('/',         [App\Http\Controllers\ControlInstrumentosController::class, 'index'])  ->name('index');
        Route::post('/',        [App\Http\Controllers\ControlInstrumentosController::class, 'store'])  ->name('store');
        Route::get('/exportar', [App\Http\Controllers\ControlInstrumentosController::class, 'exportar'])->name('exportar');
        Route::post('/importar', [App\Http\Controllers\ControlInstrumentosController::class, 'importar'])->name('importar');
        Route::get('/{programaVerificacion}/edit', [App\Http\Controllers\ControlInstrumentosController::class, 'edit'])->name('edit');
        Route::put('/{programaVerificacion}', [App\Http\Controllers\ControlInstrumentosController::class, 'update'])->name('update');
        Route::delete('/{programaVerificacion}', [App\Http\Controllers\ControlInstrumentosController::class, 'destroy'])->name('destroy');
        Route::get('/{programaVerificacion}/archivos', [App\Http\Controllers\ControlInstrumentosController::class, 'archivos'])->name('archivos');
        Route::post('/actualizar-permisos', [App\Http\Controllers\ControlInstrumentosController::class, 'actualizarPermisos'])->name('actualizar-permisos');
    });

    // ─── Certificados de Calidad ─────────────────────────────────────────────────
    Route::prefix('cert-calidad')->name('cert-calidad.')->group(function () {
        Route::get('/',                  [App\Http\Controllers\CertCalidadController::class, 'index'])   ->name('index');
        Route::post('/',                 [App\Http\Controllers\CertCalidadController::class, 'store'])   ->name('store');
        Route::post('/importar',         [App\Http\Controllers\CertCalidadController::class, 'importar'])->name('importar');
        Route::get('/exportar',          [App\Http\Controllers\CertCalidadController::class, 'exportar'])->name('exportar');
        Route::get('/{cert}/datos',      [App\Http\Controllers\CertCalidadController::class, 'datos'])  ->name('datos');
        Route::post('/{cert}',           [App\Http\Controllers\CertCalidadController::class, 'update']) ->name('update');
        Route::delete('/{cert}',          [App\Http\Controllers\CertCalidadController::class, 'destroy'])        ->name('destroy');
        Route::delete('/{cert}/archivo', [App\Http\Controllers\CertCalidadController::class, 'eliminarArchivo'])->name('archivo.destroy');
        Route::get('/{cert}/descargar',  [App\Http\Controllers\CertCalidadController::class, 'descargar'])->name('descargar');
    });

    // ─── Matriz Control de Competencias ─────────────────────────────────────────
    Route::prefix('matriz-competencias')->name('matriz-competencias.')->group(function () {
        Route::get('/',          [App\Http\Controllers\MatrizCompetenciasController::class, 'index'])   ->name('index');
        Route::post('/',         [App\Http\Controllers\MatrizCompetenciasController::class, 'store'])   ->name('store');
        Route::post('/importar', [App\Http\Controllers\MatrizCompetenciasController::class, 'importar'])->name('importar');
        Route::get('/exportar',  [App\Http\Controllers\MatrizCompetenciasController::class, 'exportar'])->name('exportar');
        Route::put('/{trabajador}',                     [App\Http\Controllers\MatrizCompetenciasController::class, 'update'])   ->name('update');
        Route::delete('/{trabajador}',                  [App\Http\Controllers\MatrizCompetenciasController::class, 'destroy'])  ->name('destroy');
        Route::get('/{trabajador}/datos',               [App\Http\Controllers\MatrizCompetenciasController::class, 'datos'])    ->name('datos');
        Route::get('/{trabajador}/descargar/{tipo}',    [App\Http\Controllers\MatrizCompetenciasController::class, 'descargar'])->name('descargar');
        Route::get('/{trabajador}/ver/{tipo}',          [App\Http\Controllers\MatrizCompetenciasController::class, 'ver'])->name('ver');
        Route::delete('/{trabajador}/archivo/{tipo}',   [App\Http\Controllers\MatrizCompetenciasController::class, 'destroyArchivo'])->name('archivo.destroy');
    });

    // ─── Matriz Cursos ───────────────────────────────────────────────────────────
    Route::prefix('matriz-cursos')->name('matriz-cursos.')->group(function () {
        Route::get('/',          [App\Http\Controllers\MatrizCursosController::class, 'index'])   ->name('index');
        Route::post('/importar', [App\Http\Controllers\MatrizCursosController::class, 'importar'])->name('importar');
        Route::get('/exportar',  [App\Http\Controllers\MatrizCursosController::class, 'exportar'])->name('exportar');

        // Trabajadores
        Route::post('/trabajadores',                  [App\Http\Controllers\MatrizCursosController::class, 'storeTrabajador'])->name('trabajadores.store');
        Route::put('/trabajadores/{trabajador}',      [App\Http\Controllers\MatrizCursosController::class, 'updateTrabajador'])->name('trabajadores.update');
        Route::delete('/trabajadores/{trabajador}',   [App\Http\Controllers\MatrizCursosController::class, 'destroyTrabajador'])->name('trabajadores.destroy');
        Route::get('/trabajadores/{trabajador}/datos',[App\Http\Controllers\MatrizCursosController::class, 'datosTrabajador'])->name('trabajadores.datos');

        // Cursos
        Route::post('/trabajadores/{trabajador}/cursos',  [App\Http\Controllers\MatrizCursosController::class, 'storeCurso'])->name('cursos.store');
        Route::post('/cursos/{curso}',                    [App\Http\Controllers\MatrizCursosController::class, 'updateCurso'])->name('cursos.update');
        Route::delete('/cursos/{curso}',                  [App\Http\Controllers\MatrizCursosController::class, 'destroyCurso'])->name('cursos.destroy');
        Route::get('/cursos/{curso}/descargar',           [App\Http\Controllers\MatrizCursosController::class, 'descargarArchivo'])->name('cursos.descargar');
    });

});