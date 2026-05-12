<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Verifica y corrige la configuración de PHP necesaria para el SGC.
 * Ejecutar una sola vez al configurar el entorno de desarrollo.
 *
 * Uso:  php artisan sgc:setup
 */
class SgcSetup extends Command
{
    protected $signature   = 'sgc:setup';
    protected $description = 'Verifica y corrige la configuración de PHP/XAMPP para el SGC (php.ini, directorios, caché)';

    // Valores requeridos
    private array $phpIniRequerido = [
        'upload_max_filesize' => '512M',
        'post_max_size'       => '512M',
        'memory_limit'        => '256M',
        'max_execution_time'  => '300',
        'display_errors'      => 'Off',
    ];

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=blue;options=bold>SGC — F&C Chile SPA · Verificación de entorno</>');
        $this->line('  ─────────────────────────────────────────────');
        $this->newLine();

        $errores = 0;

        // ── 1. PHP INI ────────────────────────────────────────────────────
        $this->line('  <options=bold>1. Configuración PHP (php.ini)</>');
        $iniPath = php_ini_loaded_file();
        $this->line("     Archivo: <fg=yellow>{$iniPath}</>");

        $iniContent   = file_get_contents($iniPath);
        $modificado   = false;
        $nuevoContenido = $iniContent;

        foreach ($this->phpIniRequerido as $clave => $valorReq) {
            $valorActual = ini_get($clave);
            $enBytes     = $this->enBytes($valorActual);
            $reqBytes    = $this->enBytes($valorReq);

            $ok = match ($clave) {
                'display_errors'    => (strtolower($valorActual) === 'off' || $valorActual === '' || $valorActual === '0'),
                'max_execution_time'=> ($valorActual === '0' || $enBytes >= $reqBytes), // 0 = sin límite = OK
                default             => $enBytes >= $reqBytes,
            };

            if ($ok) {
                $this->line("     <fg=green>✔</> {$clave} = {$valorActual}");
            } else {
                $this->line("     <fg=red>✘</> {$clave} = {$valorActual}  <fg=yellow>(se requiere {$valorReq})</>");
                $errores++;

                // Intentar corregir en el ini
                $nuevoContenido = $this->actualizarIni($nuevoContenido, $clave, $valorReq);
                $modificado = true;
            }
        }

        if ($modificado) {
            if (is_writable($iniPath)) {
                file_put_contents($iniPath, $nuevoContenido);
                $this->newLine();
                $this->line('     <fg=green>✔ php.ini actualizado correctamente.</>');
                $this->line('     <fg=yellow>⚠  Reinicia Apache/XAMPP para que los cambios tomen efecto.</>');
            } else {
                $this->newLine();
                $this->line("     <fg=red>✘ No se puede escribir en {$iniPath}.</>");
                $this->line('     <fg=yellow>   Edítalo manualmente y establece estos valores:</>');
                foreach ($this->phpIniRequerido as $k => $v) {
                    $this->line("       {$k} = {$v}");
                }
            }
        }

        // ── 2. DIRECTORIOS DE ALMACENAMIENTO ─────────────────────────────
        $this->newLine();
        $this->line('  <options=bold>2. Directorios de almacenamiento</>');

        $dirs = [
            storage_path('app/videos')     => 'videos',
            storage_path('app/archivos')   => 'archivos',
            storage_path('app/public')     => 'public',
            storage_path('logs')           => 'logs',
        ];

        foreach ($dirs as $ruta => $nombre) {
            if (! is_dir($ruta)) {
                mkdir($ruta, 0755, true);
                $this->line("     <fg=green>✔</> Creado: storage/app/{$nombre}");
            } else {
                $this->line("     <fg=green>✔</> Existe: storage/app/{$nombre}");
            }

            if (! is_writable($ruta)) {
                $this->line("     <fg=red>✘ Sin permisos de escritura en: {$ruta}</>");
                $errores++;
            }
        }

        // ── 3. SYMLINK STORAGE ────────────────────────────────────────────
        $this->newLine();
        $this->line('  <options=bold>3. Enlace simbólico storage</>');
        $link = public_path('storage');
        if (file_exists($link) || is_link($link)) {
            $this->line('     <fg=green>✔</> public/storage ya existe');
        } else {
            $this->call('storage:link', [], $this->output);
        }

        // ── 4. CACHÉ DE CONFIGURACIÓN ─────────────────────────────────────
        $this->newLine();
        $this->line('  <options=bold>4. Caché de Laravel</>');
        $this->call('config:clear',  [], $this->output);
        $this->call('route:clear',   [], $this->output);
        $this->call('view:clear',    [], $this->output);

        // ── 5. VARIABLES DE ENTORNO ──────────────────────────────────────
        $this->newLine();
        $this->line('  <options=bold>5. Variables de entorno (.env)</>');
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            $this->line('     <fg=red>✘ No existe el archivo .env</>');
            $this->line('     <fg=yellow>   Copia .env.example a .env y ejecuta: php artisan key:generate</>');
            $errores++;
        } else {
            $env = file_get_contents($envPath);
            $claves = ['APP_KEY', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
            foreach ($claves as $k) {
                $definida = preg_match('/^' . $k . '=.+/m', $env);
                if ($definida) {
                    $this->line("     <fg=green>✔</> {$k} definida");
                } else {
                    $this->line("     <fg=yellow>⚠</> {$k} no definida o vacía");
                }
            }
        }

        // ── Resumen ───────────────────────────────────────────────────────
        $this->newLine();
        $this->line('  ─────────────────────────────────────────────');
        if ($errores === 0) {
            $this->line('  <fg=green;options=bold>✔ Todo en orden. El entorno está listo.</>');
        } else {
            $this->line("  <fg=yellow;options=bold>⚠  Se encontraron {$errores} problema(s).</>");
            $this->line('  <fg=yellow>  Si se modificó el php.ini, reinicia Apache antes de continuar.</>');
        }
        $this->newLine();

        return $errores > 0 ? self::FAILURE : self::SUCCESS;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function enBytes(string $valor): int
    {
        $valor = trim($valor);
        if ($valor === '' || $valor === '0') return 0;
        $ultimo = strtoupper(substr($valor, -1));
        $num    = (int) $valor;
        return match ($ultimo) {
            'G'     => $num * 1024 * 1024 * 1024,
            'M'     => $num * 1024 * 1024,
            'K'     => $num * 1024,
            default => $num,
        };
    }

    private function actualizarIni(string $contenido, string $clave, string $valor): string
    {
        // Si la directiva existe (comentada o no), reemplazarla
        $patron = '/^;?\s*' . preg_quote($clave, '/') . '\s*=.*$/m';
        if (preg_match($patron, $contenido)) {
            return preg_replace($patron, "{$clave} = {$valor}", $contenido);
        }
        // Si no existe, agregarla al final
        return $contenido . PHP_EOL . "{$clave} = {$valor}";
    }
}
