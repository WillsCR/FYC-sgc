<?php

namespace App\Helpers;

/**
 * Metadatos visuales (color, emoji) para módulos y submódulos del SGC.
 * Centralizado aquí para que PanelController y CarpetaController usen
 * la misma fuente de verdad.
 */
class EstiloModulo
{
    /** Estilos por nombre de submódulo (icono = clase Font Awesome sin prefijo) */
    private static array $estilosSubmodulo = [
        // SIG
        'No Conformidades'                                    => ['color' => '#DC2626', 'emoji' => 'fa-triangle-exclamation'],
        'Instrumentos de Medición Certificación de Calidad'   => ['color' => '#D97706', 'emoji' => 'fa-ruler'],
        'Certificados de Calidad'                             => ['color' => '#0F6E56', 'emoji' => 'fa-certificate'],
        'Certificados de EPP'                                 => ['color' => '#B45309', 'emoji' => 'fa-vest'],
        'Formatos SIG'                                        => ['color' => '#C05621', 'emoji' => 'fa-file-lines'],
        'Documentos del SIG'                                  => ['color' => '#1D4ED8', 'emoji' => 'fa-folder-open'],
        'Capacitaciones'                                      => ['color' => '#0369A1', 'emoji' => 'fa-graduation-cap'],
        'Informes'                                            => ['color' => '#7C3AED', 'emoji' => 'fa-chart-bar'],
        'Auditorías'                                          => ['color' => '#059669', 'emoji' => 'fa-magnifying-glass'],
        // Medio Ambiente
        'Sustancias y Residuos Peligrosos'                    => ['color' => '#DC2626', 'emoji' => 'fa-recycle'],
        'Control de Recursos'                                 => ['color' => '#D97706', 'emoji' => 'fa-leaf'],
        'Huellas de Carbono'                                  => ['color' => '#0F6E56', 'emoji' => 'fa-earth-americas'],
        'Control Operativo'                                   => ['color' => '#B45309', 'emoji' => 'fa-gears'],
        // Seguridad
        'Protocolo Minsal'                                    => ['color' => '#C05621', 'emoji' => 'fa-hospital'],
        'DS 44'                                               => ['color' => '#1D4ED8', 'emoji' => 'fa-scale-balanced'],
        'CPHS'                                                => ['color' => '#0369A1', 'emoji' => 'fa-users'],
        // RRHH / Abastecimiento
        'Control Plan e Infraestructura'                      => ['color' => '#7C3AED', 'emoji' => 'fa-building'],
        'Cursos'                                              => ['color' => '#059669', 'emoji' => 'fa-book-open'],
        'Contrato pozos'                                      => ['color' => '#6366F1', 'emoji' => 'fa-hammer'],
        'Formatos'                                            => ['color' => '#C05621', 'emoji' => 'fa-file-lines'],
        'Documentos'                                          => ['color' => '#1D4ED8', 'emoji' => 'fa-folder-open'],
    ];

    /** Estilos por clave de módulo raíz */
    private static array $estilosModulo = [
        'sig'            => ['color' => '#0D2B5E', 'emoji' => 'fa-clipboard-check',  'badge' => 'SIG'],
        'ambiente'       => ['color' => '#15803D', 'emoji' => 'fa-seedling',          'badge' => 'MA'],
        'seguridad'      => ['color' => '#991B1B', 'emoji' => 'fa-shield-halved',     'badge' => 'SST'],
        'abastecimiento' => ['color' => '#B45309', 'emoji' => 'fa-truck',             'badge' => 'ABI'],
        'rrhh'           => ['color' => '#7C3AED', 'emoji' => 'fa-user-tie',          'badge' => 'RRHH'],
        'gerencia'       => ['color' => '#0C4A6E', 'emoji' => 'fa-building',          'badge' => 'GER'],
        'proyectos'      => ['color' => '#1D4ED8', 'emoji' => 'fa-chart-line',        'badge' => 'PRY'],
        'finanzas'       => ['color' => '#065F46', 'emoji' => 'fa-coins',             'badge' => 'FIN'],
    ];

    /** Mapeo carpeta_id → clave de módulo */
    private static array $mapaCarpetaModulo = [
        1 => 'sig',
        2 => 'ambiente',
        3 => 'seguridad',
        4 => 'abastecimiento',
        5 => 'rrhh',
        6 => 'gerencia',
        7 => 'proyectos',
        8 => 'finanzas',
    ];

    /**
     * Devuelve [color, emoji] para un submódulo por su nombre.
     */
    public static function submodulo(string $nombre): array
    {
        return self::$estilosSubmodulo[$nombre] ?? ['color' => '#6B7280', 'emoji' => '📁'];
    }

    /**
     * Devuelve [color, emoji, badge] para un módulo raíz por su clave.
     */
    public static function modulo(string $clave): array
    {
        return self::$estilosModulo[$clave] ?? ['color' => '#6B7280', 'emoji' => '📁', 'badge' => ''];
    }

    /**
     * Devuelve la clave de módulo dado el ID de carpeta raíz.
     */
    public static function claveDesdeId(int $carpetaId): ?string
    {
        return self::$mapaCarpetaModulo[$carpetaId] ?? null;
    }

    /**
     * Devuelve el ID de carpeta dado la clave de módulo.
     */
    public static function idDesdeClav(string $clave): ?int
    {
        $flip = array_flip(self::$mapaCarpetaModulo);
        return $flip[$clave] ?? null;
    }

    /**
     * Devuelve todos los IDs de carpetas raíz.
     */
    public static function idsRaiz(): array
    {
        return array_keys(self::$mapaCarpetaModulo);
    }
}
