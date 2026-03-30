<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

// ─── Rutas públicas ─────────────────────────────────────────────────────────

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Rutas protegidas (requieren sesión activa) ──────────────────────────────

Route::middleware(['auth.sgc'])->group(function () {

    Route::get('/panel', [PanelController::class, 'index'])->name('panel');

    // ── Rutas solo para administradores ──
    Route::middleware(['admin.sgc'])->prefix('admin')->name('admin.')->group(function () {
        // Sprint 4: gestión de usuarios
        // Route::resource('usuarios', UsuarioController::class);
    });

    // ── Sprint 3: gestión documental ──
    // Route::resource('carpetas', CarpetaController::class);
    // Route::post('archivos/subir', [ArchivoController::class, 'subir'])->name('archivos.subir');

    // ── Sprint 4: planificación ──
    // Route::get('planificacion', [PlanificacionController::class, 'index'])->name('planificacion.index');

    // ── Sprint 5: minutas ──
    // Route::resource('minutas', MinutaController::class);
});
