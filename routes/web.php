<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\MetricasController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PlanificacionController;
use App\Http\Controllers\MinutaController;
use Illuminate\Support\Facades\Route;

// ─── Rutas públicas ──────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ─── Rutas protegidas ────────────────────────────────────────────────────────
Route::middleware(['auth.sgc'])->group(function () {

    // Panel y métricas
    Route::get('/panel',          [PanelController::class,    'index'])->name('panel');
    Route::get('/metricas',       [MetricasController::class, 'index'])->name('metricas');
    Route::get('/metricas/excel', [MetricasController::class, 'exportarExcel'])->name('metricas.excel');

    // Sprint 3 — Gestión documental
    Route::get('/carpetas',                  [CarpetaController::class, 'index'])->name('carpetas.index');
    Route::get('/carpetas/{id}',             [CarpetaController::class, 'show'])->name('carpetas.show');
    Route::get('/carpetas/{id}/hijos',       [CarpetaController::class, 'hijos'])->name('carpetas.hijos');
    Route::post('/carpetas/{id}/store',      [CarpetaController::class, 'store'])->name('carpetas.store');
    Route::delete('/carpetas/{id}',          [CarpetaController::class, 'destroy'])->name('carpetas.destroy');
    Route::post('/archivos/subir',           [ArchivoController::class, 'subir'])->name('archivos.subir');
    Route::delete('/archivos/lote',          [ArchivoController::class, 'eliminarLote'])->name('archivos.eliminar.lote');
    Route::get('/archivos/{id}/ver',         [ArchivoController::class, 'ver'])->name('archivos.ver');
    Route::get('/archivos/{id}/descargar',   [ArchivoController::class, 'descargar'])->name('archivos.descargar');
    Route::delete('/archivos/{id}',          [ArchivoController::class, 'eliminar'])->name('archivos.eliminar');

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

    // Sprint 5 — Minutas
    Route::get('/minutas',               [MinutaController::class, 'index'])  ->name('minutas.index');
    Route::get('/minutas/crear',         [MinutaController::class, 'create']) ->name('minutas.create');
    Route::post('/minutas',              [MinutaController::class, 'store'])  ->name('minutas.store');
    Route::get('/minutas/{id}',          [MinutaController::class, 'show'])   ->name('minutas.show');
    Route::get('/minutas/{id}/editar',   [MinutaController::class, 'edit'])   ->name('minutas.edit');
    Route::put('/minutas/{id}',          [MinutaController::class, 'update']) ->name('minutas.update');
    Route::delete('/minutas/{id}',       [MinutaController::class, 'destroy'])->name('minutas.destroy');
});
