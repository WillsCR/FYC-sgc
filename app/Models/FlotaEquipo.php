<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FlotaEquipo extends Model
{
    protected $table = 'sgc_flota_equipos';

    protected $fillable = [
        'equipo', 'marca', 'modelo', 'patente', 'area',
        'fecha_gps', 'fecha_skynav', 'fecha_revision_tecnica', 'fecha_permiso_circulacion',
        'fecha_soap', 'fecha_cert_mlp', 'fecha_extintor', 'fecha_prueba_carga',
        'fecha_insp_camion_pluma', 'fecha_insp_gancho', 'fecha_insp_perforadora',
        'fecha_gancho_perforadora', 'fecha_cable_acero_perforadora', 'fecha_wuinche_perforadora',
        'km_actual', 'km_proxima_mantencion', 'responsable',
        'correo_aviso', 'observaciones',
        'aviso_cert_enviado', 'aviso_km_enviado',
    ];

    protected $casts = [
        'fecha_gps'                     => 'date',
        'fecha_skynav'                  => 'date',
        'fecha_revision_tecnica'        => 'date',
        'fecha_permiso_circulacion'     => 'date',
        'fecha_soap'                    => 'date',
        'fecha_cert_mlp'                => 'date',
        'fecha_extintor'                => 'date',
        'fecha_prueba_carga'            => 'date',
        'fecha_insp_camion_pluma'       => 'date',
        'fecha_insp_gancho'             => 'date',
        'fecha_insp_perforadora'        => 'date',
        'fecha_gancho_perforadora'      => 'date',
        'fecha_cable_acero_perforadora' => 'date',
        'fecha_wuinche_perforadora'     => 'date',
        'aviso_cert_enviado'            => 'boolean',
        'aviso_km_enviado'              => 'boolean',
    ];

    // ── Lista de certificaciones (campo => etiqueta) ──────────────────────
    public static function certFields(): array
    {
        return [
            'fecha_gps'                     => 'GPS',
            'fecha_skynav'                  => 'SKYNAV',
            'fecha_revision_tecnica'        => 'Revisión Técnica',
            'fecha_permiso_circulacion'     => 'Permiso Circulación',
            'fecha_soap'                    => 'SOAP',
            'fecha_cert_mlp'                => 'Cert. MLP',
            'fecha_extintor'                => 'Extintor',
            'fecha_prueba_carga'            => 'Prueba de Carga',
            'fecha_insp_camion_pluma'       => 'Insp. Camión Pluma',
            'fecha_insp_gancho'             => 'Insp. Gancho',
            'fecha_insp_perforadora'        => 'Insp. Perforadora',
            'fecha_gancho_perforadora'      => 'Gancho Perforadora',
            'fecha_cable_acero_perforadora' => 'Cable Acero Perf.',
            'fecha_wuinche_perforadora'     => 'Wuinche Perf.',
        ];
    }

    // ── Semáforo de una fecha individual ─────────────────────────────────
    public function semaforoCert(string $campo): string
    {
        $fecha = $this->$campo;
        if (!$fecha) return 'gris';
        $dias = (int) now()->startOfDay()->diffInDays($fecha, false);
        if ($dias < 0)   return 'rojo';
        if ($dias <= 30) return 'naranja';
        return 'verde';
    }

    public function diasRestantesCert(string $campo): ?int
    {
        $fecha = $this->$campo;
        if (!$fecha) return null;
        return (int) now()->startOfDay()->diffInDays($fecha, false);
    }

    // ── Semáforo general de certificaciones (el peor de todos) ───────────
    public function getSemaforoCertAttribute(): string
    {
        $peor = 'verde';
        foreach (array_keys(self::certFields()) as $campo) {
            $s = $this->semaforoCert($campo);
            if ($s === 'rojo')   return 'rojo';
            if ($s === 'naranja') $peor = 'naranja';
            if ($s === 'gris' && $peor === 'verde') $peor = 'gris';
        }
        return $peor;
    }

    // ── Km restantes y semáforo de mantención ────────────────────────────
    public function getKmRestantesAttribute(): ?int
    {
        if ($this->km_actual === null || $this->km_proxima_mantencion === null) return null;
        return $this->km_proxima_mantencion - $this->km_actual;
    }

    public function getSemaforoKmAttribute(): string
    {
        $r = $this->km_restantes;
        if ($r === null) return 'gris';
        if ($r < 0)      return 'rojo';
        if ($r <= 500)   return 'naranja';
        return 'verde';
    }

    // ── Semáforo general del equipo ───────────────────────────────────────
    public function getSemaforoAttribute(): string
    {
        $cert = $this->semaforo_cert;
        $km   = $this->semaforo_km;
        foreach ([$cert, $km] as $s) {
            if ($s === 'rojo') return 'rojo';
        }
        foreach ([$cert, $km] as $s) {
            if ($s === 'naranja') return 'naranja';
        }
        foreach ([$cert, $km] as $s) {
            if ($s === 'gris') return 'gris';
        }
        return 'verde';
    }

    // ── Helpers para la vista ─────────────────────────────────────────────
    public function certArray(): array
    {
        $arr = [];
        foreach (self::certFields() as $campo => $etiqueta) {
            $fecha = $this->$campo;
            $arr[] = [
                'campo'    => $campo,
                'etiqueta' => $etiqueta,
                'fecha'    => $fecha ? $fecha->format('d/m/Y') : null,
                'fecha_raw'=> $fecha ? $fecha->format('Y-m-d') : null,
                'semaforo' => $this->semaforoCert($campo),
                'dias'     => $this->diasRestantesCert($campo),
            ];
        }
        return $arr;
    }
}
