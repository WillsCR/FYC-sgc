<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoResponsable extends Model
{
    protected $table = 'sgc_equipos_responsables';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_equipo', 'nombre', 'cargo', 'email'
    ];

    public function equipo()
    {
        return $this->belongsTo(EquipoInterno::class, 'id_equipo');
    }
}