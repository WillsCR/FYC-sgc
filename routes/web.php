<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\MetricasController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\UsuarioController;
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
    Route::get('/carpetas',                [CarpetaController::class, 'index'])->name('carpetas.index');
    Route::get('/carpetas/{id}',           [CarpetaController::class, 'show'])->name('carpetas.show');
    Route::get('/carpetas/{id}/hijos',     [CarpetaController::class, 'hijos'])->name('carpetas.hijos');
    Route::post('/archivos/subir',         [ArchivoController::class, 'subir'])->name('archivos.subir');
    Route::get('/archivos/{id}/descargar', [ArchivoController::class, 'descargar'])->name('archivos.descargar');
    Route::delete('/archivos/{id}',        [ArchivoController::class, 'eliminar'])->name('archivos.eliminar');

    // Sprint 4 — Gestión de usuarios y permisos
    // Solo accesible por SuperAdmin (id=1) y Admin (id=2)
    // El UsuarioController verifica internamente según la operación
    Route::get('/usuarios',              [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/nuevo',        [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios',             [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{id}/editar',  [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}',         [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}',      [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    // Sprint 5 — próximo
    // Route::resource('/minutas', MinutaController::class);
});
