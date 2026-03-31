<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\MetricasController;
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

    // Sprint 3: gestión documental
    // Route::resource('carpetas', CarpetaController::class);

    // Sprint 4: planificación y usuarios
    // Route::get('planificacion', [PlanificacionController::class, 'index'])->name('planificacion.index');
    // Route::middleware(['admin.sgc'])->resource('usuarios', UsuarioController::class);

    // Sprint 5: minutas
    // Route::resource('minutas', MinutaController::class);
});
