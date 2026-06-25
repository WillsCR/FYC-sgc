<?php
/**
 * Script de inicialización para producción.
 * Ejecutar UNA VEZ después de subir archivos.
 * ELIMINAR este archivo después de ejecutar.
 */

$appPath = dirname(__DIR__, 2) . '/sgc_app';

echo "<pre style='font-family:monospace; background:#1e1e1e; color:#d4d4d4; padding:20px;'>";
echo "=== SGC Setup Script ===\n\n";

// 1. Verificar rutas
echo "[1] Verificando rutas...\n";
echo "    public:  " . __DIR__ . "\n";
echo "    sgc_app: " . $appPath . "\n";
echo "    Existe:  " . (is_dir($appPath) ? "✓ SÍ" : "✗ NO") . "\n\n";

// 2. Crear directorios de storage necesarios
echo "[2] Creando directorios storage...\n";
$dirs = [
    $appPath . '/storage/app',
    $appPath . '/storage/app/public',
    $appPath . '/storage/framework',
    $appPath . '/storage/framework/cache',
    $appPath . '/storage/framework/cache/data',
    $appPath . '/storage/framework/sessions',
    $appPath . '/storage/framework/views',
    $appPath . '/storage/logs',
    $appPath . '/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "    ✓ Creado: $dir\n";
        } else {
            echo "    ✗ Error al crear: $dir\n";
        }
    } else {
        echo "    ✓ Ya existe: $dir\n";
    }
}

// 3. Crear enlace simbólico storage
echo "\n[3] Enlace simbólico storage...\n";
$link = __DIR__ . '/storage';
$target = $appPath . '/storage/app/public';

if (is_link($link)) {
    echo "    ✓ Symlink ya existe: $link\n";
} elseif (file_exists($link)) {
    echo "    ✗ Ya existe un archivo/carpeta en: $link (elimínalo manualmente)\n";
} else {
    if (symlink($target, $link)) {
        echo "    ✓ Symlink creado: $link → $target\n";
    } else {
        echo "    ✗ No se pudo crear symlink. Crear manualmente:\n";
        echo "      ln -s $target $link\n";
    }
}

// 4. Verificar .env
echo "\n[4] Verificando .env...\n";
$envFile = $appPath . '/.env';
if (file_exists($envFile)) {
    echo "    ✓ .env existe\n";
    $env = file_get_contents($envFile);
    $appKey = '';
    if (preg_match('/APP_KEY=(.+)/', $env, $m)) {
        $appKey = trim($m[1]);
    }
    echo "    APP_KEY: " . ($appKey && $appKey !== 'base64:' ? "✓ Configurado" : "✗ FALTA - ver paso 5") . "\n";
    if (preg_match('/DB_DATABASE=(.+)/', $env, $m)) {
        echo "    DB_DATABASE: " . trim($m[1]) . "\n";
    }
} else {
    echo "    ✗ NO existe .env en $envFile\n";
    echo "    → Crea el archivo .env con las credenciales\n";
}

// 5. Intentar generar APP_KEY si falta
echo "\n[5] Verificando APP_KEY...\n";
if (is_dir($appPath . '/vendor')) {
    require_once $appPath . '/vendor/autoload.php';
    try {
        $app = require_once $appPath . '/bootstrap/app.php';
        $key = 'base64:' . base64_encode(random_bytes(32));
        echo "    APP_KEY generado: $key\n";
        echo "    → Copia esta línea en tu .env:\n";
        echo "    APP_KEY=$key\n";
    } catch (\Throwable $e) {
        echo "    Error al cargar app: " . $e->getMessage() . "\n";
    }
} else {
    echo "    ✗ vendor/ no encontrado en $appPath\n";
}

// 6. Probar conexión a BD
echo "\n[6] Probando conexión a base de datos...\n";
if (file_exists($appPath . '/.env')) {
    $env = parse_ini_file($appPath . '/.env');
    $host = $env['DB_HOST'] ?? 'localhost';
    $db   = $env['DB_DATABASE'] ?? '';
    $user = $env['DB_USERNAME'] ?? '';
    $pass = $env['DB_PASSWORD'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        echo "    ✓ Conexión exitosa a BD: $db\n";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "    Tablas encontradas: " . count($tables) . "\n";
        if (count($tables) > 0) {
            echo "    Primeras 5: " . implode(', ', array_slice($tables, 0, 5)) . "\n";
        }
    } catch (\PDOException $e) {
        echo "    ✗ Error de conexión: " . $e->getMessage() . "\n";
    }
} else {
    echo "    ✗ No se puede probar sin .env\n";
}

// 7. Verificar PHP
echo "\n[7] Entorno PHP...\n";
echo "    PHP Version: " . PHP_VERSION . " " . (version_compare(PHP_VERSION, '8.2.0', '>=') ? "✓" : "✗ Requiere 8.2+") . "\n";
echo "    Extensions requeridas:\n";
$exts = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'fileinfo', 'json', 'tokenizer', 'xml', 'ctype'];
foreach ($exts as $ext) {
    echo "        " . $ext . ": " . (extension_loaded($ext) ? "✓" : "✗ FALTA") . "\n";
}

echo "\n=== FIN DEL SETUP ===\n";
echo "\n⚠️  IMPORTANTE: Elimina este archivo después de ejecutarlo.\n";
echo "    rm " . __FILE__ . "\n";
echo "</pre>";
