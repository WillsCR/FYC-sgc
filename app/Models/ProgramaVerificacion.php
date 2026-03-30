<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramaVerificacion extends Model
{
    protected $table = 'sgc_programa_verificacion';
    public $timestamps = false;

    protected $fillable = [
        'id_equipo',
        'tipo_verificacion',
        'fecha_programada',
        'fecha_realizada',
        'estado',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizada'  => 'date',
    ];

    public function equipo()
    {
        return $this->belongsTo(EquipoInterno::class, 'id_equipo');
    }
}
