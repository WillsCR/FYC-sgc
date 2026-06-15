<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CertCalidad extends Model
{
    protected $table    = 'sgc_cert_calidad';
    protected $fillable = [
        'numero', 'contrato', 'descripcion', 'tipo_certificado',
        'aplica', 'critico', 'procedimiento_asociado',
        'marca', 'modelo', 'vencimiento', 'observaciones',
        'correo_aviso', 'aviso_30d_enviado',
        'archivo_nombre', 'archivo_ruta', 'archivo_mime',
    ];

    protected $casts = [
        'aplica'            => 'boolean',
        'critico'           => 'boolean',
        'vencimiento'       => 'date',
        'aviso_30d_enviado' => 'boolean',
    ];

    /**
     * Semáforo basado en fecha de vencimiento:
     *  - verde:   > 30 días
     *  - naranja: 0–30 días
     *  - rojo:    vencido o sin fecha
     */
    public function getSemaforoAttribute(): string
    {
        if (! $this->vencimiento) return 'gris';
        $hoy = Carbon::today();
        if ($this->vencimiento->lt($hoy)) return 'rojo';
        if ($this->vencimiento->lte($hoy->copy()->addDays(30))) return 'naranja';
        return 'verde';
    }
}
