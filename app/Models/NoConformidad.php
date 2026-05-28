<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoConformidad extends Model
{
    protected $table      = 'sgc_no_conformidades';
    protected $primaryKey = 'id';
    const CREATED_AT      = 'creada_el';
    const UPDATED_AT      = 'actualizada_el';

    protected $fillable = [
        'num_nc', 'fecha_deteccion', 'origen', 'id_area', 'tipo_accion',
        'detectada_por', 'cargo', 'descripcion',
        'resp_inmediata_nombre', 'resp_inmediata_cargo', 'status_correccion',
        'resp_correctiva_nombre', 'resp_correctiva_cargo',
        'fecha_implem_acc_corr', 'fecha_seguim_acc_corr', 'status_acc_corr',
        'status_seguim_acc_corr', 'accion_eficaz',
        'fecha_cierre', 'registros', 'observaciones', 'id_usuario',
    ];

    protected $casts = [
        'fecha_deteccion'       => 'date',
        'fecha_implem_acc_corr' => 'date',
        'fecha_seguim_acc_corr' => 'date',
        'fecha_cierre'          => 'date',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function accionesInmediatas()
    {
        return $this->hasMany(AccionInmediata::class, 'id_nc');
    }

    public function accionesCorrectivas()
    {
        return $this->hasMany(AccionCorrectiva::class, 'id_nc');
    }

    public function documentos()
    {
        return $this->hasMany(NoConformidadDoc::class, 'id_nc');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    // ── Helpers de texto ─────────────────────────────────────────────────────

    public function tipoNombre(): string
    {
        return match ((int) $this->tipo_accion) {
            1       => 'Correctiva',
            2       => 'Oportunidad de Mejora',
            default => '—',
        };
    }

    public function statusNombre(string $campo): string
    {
        return (int) $this->$campo === 1 ? 'Resuelta' : 'Pendiente';
    }

    public function accionEficazNombre(): string
    {
        return match ((int) $this->accion_eficaz) {
            1       => 'Sí',
            2       => 'No',
            default => '-',
        };
    }

    public function fmtFecha($fecha): string
    {
        if (! $fecha || $fecha === '0000-00-00') return '—';
        try { return \Carbon\Carbon::parse($fecha)->format('d-m-Y'); }
        catch (\Exception $e) { return '—'; }
    }
}