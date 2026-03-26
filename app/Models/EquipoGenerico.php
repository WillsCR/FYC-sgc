<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoGenerico extends Model
{
    protected $table = 'sgc_equipos_genericos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'tipo', 'descripcion'
    ];

    public function equiposInternos()
    {
        return $this->hasMany(EquipoInterno::class, 'id_equipo_generico');
    }
}