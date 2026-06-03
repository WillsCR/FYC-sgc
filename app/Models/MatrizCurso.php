<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MatrizCurso extends Model
{
    protected $table    = 'sgc_matriz_cursos';
    protected $fillable = [
        'id_trabajador', 'curso', 'entidad_acreditadora',
        'fecha_realizacion', 'fecha_vencimiento', 'correo_aviso',
        'archivo_nombre', 'archivo_ruta', 'archivo_mime',
    ];

    protected $casts = [
        'fecha_realizacion' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function trabajador()
    {
        return $this->belongsTo(MatrizTrabajador::class, 'id_trabajador');
    }

    /**
     * semaforo: 'verde' | 'naranja' | 'rojo'
     * verde   → vence en más de 30 días
     * naranja → vence en 0-30 días
     * rojo    → vencido o sin fecha
     */
    public function getSemaforoAttribute(): string
    {
        if (! $this->fecha_vencimiento) return 'rojo';
        $hoy = Carbon::today();
        if ($this->fecha_vencimiento->lt($hoy))                       return 'rojo';
        if ($this->fecha_vencimiento->lte($hoy->copy()->addDays(30))) return 'naranja';
        return 'verde';
    }
}
