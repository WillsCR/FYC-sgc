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

    Route::get('/panel',    [PanelController::class,    'index'])->name('panel');
    Route::get('/metricas', [MetricasController::class, 'index'])->name('metricas');

    // Gestión documental
    Route::get('/carpetas/{modulo}',                    [CarpetaController::class, 'index'])->name('carpetas.index');
    Route::get('/carpetas/{modulo}/carpeta/{id}',      [CarpetaController::class, 'show'])->name('carpetas.show');
    Route::get('/carpetas/{modulo}/hijos/{id}',        [CarpetaController::class, 'hijos'])->name('carpetas.hijos');
    Route::post('/carpetas/{modulo}/store/{id}',       [CarpetaController::class, 'store'])->name('carpetas.store');

    Route::post('/archivos/subir',                     [ArchivoController::class, 'subir'])->name('archivos.subir');
    Route::get('/archivos/{id}/ver',                   [ArchivoController::class, 'ver'])->name('archivos.ver');
    Route::get('/archivos/{id}/descargar',             [ArchivoController::class, 'descargar'])->name('archivos.descargar');
    Route::delete('/archivos/{id}',                    [ArchivoController::class, 'eliminar'])->name('archivos.eliminar');
});