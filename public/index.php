<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Detectar si estamos en producción (fuera de public_html/sgc2)
// En producción: public está en public_html/sgc2, app está en sgc_app (2 niveles arriba)
$appPath = dirname(__DIR__, 2) . '/sgc_app';
$localPath = dirname(__DIR__);

// Usar la ruta correcta según el entorno
if (is_dir($appPath . '/storage')) {
    // Producción: app está dos niveles arriba en sgc_app/
    if (file_exists($appPath . '/storage/framework/maintenance.php')) {
        require $appPath . '/storage/framework/maintenance.php';
    }
    require $appPath . '/vendor/autoload.php';
    $app = require_once $appPath . '/bootstrap/app.php';
    $app->usePublicPath(__DIR__);
} else {
    // Local: estructura estándar de Laravel
    if (file_exists($localPath . '/storage/framework/maintenance.php')) {
        require $localPath . '/storage/framework/maintenance.php';
    }
    require $localPath . '/vendor/autoload.php';
    $app = require_once $localPath . '/bootstrap/app.php';
}

/** @var Application $app */
$app->handleRequest(Request::capture());
