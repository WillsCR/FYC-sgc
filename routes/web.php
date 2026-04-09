<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\MetricasController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\ArchivoController;
use Illuminate\Support\Facades\Route;

// ─── Rutas públicas ──────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ─── Rutas protegidas ────────────────────────────────────────────────────────
Route::middleware(['auth.sgc'])->group(function () {

    // Panel
    Route::get('/panel', [PanelController::class, 'index'])->name('panel');

    // Métricas + exportaciones
    Route::get('/metricas',       [MetricasController::class, 'index'])->name('metricas');
    Route::get('/metricas/excel', [MetricasController::class, 'exportarExcel'])->name('metricas.excel');

    // Sprint 3 — Gestión documental
    Route::get('/carpetas',                [CarpetaController::class, 'index'])->name('carpetas.index');
    Route::get('/carpetas/{id}',           [CarpetaController::class, 'show'])->name('carpetas.show');
    Route::get('/carpetas/{id}/hijos',     [CarpetaController::class, 'hijos'])->name('carpetas.hijos');
    Route::post('/archivos/subir',         [ArchivoController::class, 'subir'])->name('archivos.subir');
    Route::get('/archivos/{id}/descargar', [ArchivoController::class, 'descargar'])->name('archivos.descargar');
    Route::delete('/archivos/{id}',        [ArchivoController::class, 'eliminar'])->name('archivos.eliminar');

    // Sprint 4 — próximo
    // Route::get('/planificacion', [PlanificacionController::class, 'index'])->name('planificacion.index');
    // Route::middleware(['admin.sgc'])->resource('/usuarios', UsuarioController::class);

    // Sprint 5 — próximo
    // Route::resource('/minutas', MinutaController::class);
});
