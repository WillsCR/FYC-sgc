<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarArchivosLegacy extends Command
{
    protected $signature   = 'sgc:importar-legacy {--dry-run : Solo mostrar qué se importaría, sin insertar}';
    protected $description = 'Escanea sgc/inc/ e importa archivos no registrados en sgc_carpetas_contenido';

    // Extensiones de archivo a importar (misma lista que ArchivoController)
    private const EXTS_PERMITIDAS = [
        'pdf','doc','docx','xls','xlsx','ppt','pptx',
        'jpg','jpeg','png','gif','webp','bmp',
        'txt','csv','xml','zip','rar',
    ];

    // Directorios y archivos a ignorar completamente
    private const IGNORAR = [
        'fpdf182', 'cursos', 'pozos', 'epp', 'ds44', 'minsal',
        'cert_calibrac_equipos', 'cert_calidad_equipos', 'certcal',
        'evidencias_nc', 'control_pozos.php', 'epa.php', 'error_log',
    ];

    private string $baseInc = 'C:/xampp/htdocs/sgc/inc';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('── MODO SIMULACIÓN (--dry-run): no se insertan datos ──');
        }

        // Cargar todas las carpetas con ruta no nula, indexadas por ruta normalizada
        $carpetas = DB::table('sgc_carpetas')
            ->whereNotNull('ruta')
            ->where('ruta', '!=', '')
            ->get()
            ->keyBy(fn($c) => strtolower(trim($c->ruta, '/')));

        $this->info("Carpetas con ruta en BD: {$carpetas->count()}");

        // Cargar todos los registros existentes en sgc_carpetas_contenido
        // para evitar duplicados sin consultar por cada archivo
        $yaRegistrados = DB::table('sgc_carpetas_contenido')
            ->get(['id_carpeta', 'archivo'])
            ->groupBy('id_carpeta')
            ->map(fn($rows) => $rows->pluck('archivo')->map('strtolower')->flip());

        $importados = 0;
        $omitidos   = 0;
        $sinCarpeta = 0;

        $this->info("Escaneando {$this->baseInc}...\n");

        $archivos = $this->escanearArchivos($this->baseInc);

        $bar = $this->output->createProgressBar(count($archivos));
        $bar->start();

        foreach ($archivos as $rutaAbsoluta) {
            $bar->advance();

            $relativo  = ltrim(str_replace('\\', '/', substr($rutaAbsoluta, strlen($this->baseInc))), '/');
            $directorio = ltrim(dirname($relativo), '/');
            $nombreArchivo = basename($relativo);

            // Buscar carpeta que corresponda a este directorio (o algún padre)
            $carpeta = $this->buscarCarpeta($directorio, $carpetas);

            if (! $carpeta) {
                $sinCarpeta++;
                continue;
            }

            // Verificar si ya está registrado
            $clave = strtolower($nombreArchivo);
            if (isset($yaRegistrados[$carpeta->id]) && $yaRegistrados[$carpeta->id]->has($clave)) {
                $omitidos++;
                continue;
            }

            if (! $dryRun) {
                DB::table('sgc_carpetas_contenido')->insert([
                    'id_carpeta'  => $carpeta->id,
                    'descripcion' => $nombreArchivo,
                    'archivo'     => $nombreArchivo,
                    'creada_el'   => now(),
                ]);

                // Añadir al índice en memoria para no duplicar dentro del mismo run
                if (! isset($yaRegistrados[$carpeta->id])) {
                    $yaRegistrados[$carpeta->id] = collect();
                }
                $yaRegistrados[$carpeta->id]->put($clave, true);
            } else {
                $this->newLine();
                $this->line("  + [{$carpeta->id}] {$carpeta->descripcion} ← {$nombreArchivo}");
            }

            $importados++;
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Archivos importados   : {$importados}");
        $this->line("Ya registrados (omit.): {$omitidos}");
        $this->line("Sin carpeta en BD     : {$sinCarpeta}");

        if ($dryRun && $importados > 0) {
            $this->newLine();
            $this->warn("Ejecuta sin --dry-run para insertar los {$importados} archivos.");
        }

        return Command::SUCCESS;
    }

    /**
     * Escanea recursivamente sgc/inc/ y devuelve archivos con extensión permitida.
     */
    private function escanearArchivos(string $dir): array
    {
        $resultado = [];
        $items     = @scandir($dir);

        if (! $items) {
            return $resultado;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            // Ignorar directorios/archivos no deseados
            if (in_array(strtolower($item), array_map('strtolower', self::IGNORAR))) {
                continue;
            }

            $ruta = $dir . '/' . $item;

            if (is_dir($ruta)) {
                $resultado = array_merge($resultado, $this->escanearArchivos($ruta));
            } elseif (is_file($ruta)) {
                $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                if (in_array($ext, self::EXTS_PERMITIDAS)) {
                    $resultado[] = $ruta;
                }
            }
        }

        return $resultado;
    }

    /**
     * Busca la carpeta en BD que mejor coincide con el directorio del archivo.
     * Sube por el árbol de directorios hasta encontrar match.
     */
    private function buscarCarpeta(string $directorio, \Illuminate\Support\Collection $carpetas): ?object
    {
        $dir = strtolower(trim($directorio, '/'));

        // Intentar match exacto primero
        if ($carpetas->has($dir)) {
            return $carpetas->get($dir);
        }

        // Subir por segmentos hasta encontrar una carpeta padre registrada
        $segmentos = explode('/', $dir);
        while (count($segmentos) > 0) {
            array_pop($segmentos);
            $intento = implode('/', $segmentos);
            if ($intento !== '' && $carpetas->has($intento)) {
                return $carpetas->get($intento);
            }
        }

        return null;
    }
}
