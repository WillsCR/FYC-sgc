<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoResponsable extends Model
{
    protected $table = 'sgc_equipos_responsables';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_equipo_interno',
        'id_programa_verificacion',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function programa()
    {
        return $this->belongsTo(ProgramaVerificacion::class, 'id_programa_verificacion');
    }
}
