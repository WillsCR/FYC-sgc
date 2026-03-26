<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoInterno extends Model
{
    protected $table = 'sgc_equipos_internos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_equipo_generico', 'serie', 'ubicacion', 'estado', 'fecha_adquisicion'
    ];

    protected $casts = [
        'fecha_adquisicion' => 'date'
    ];

    public function equipoGenerico()
    {
        return $this->belongsTo(EquipoGenerico::class, 'id_equipo_generico');
    }

    public function certificados()
    {
        return $this->hasMany(EquipoCertificado::class, 'id_equipo');
    }

    public function responsables()
    {
        return $this->hasMany(EquipoResponsable::class, 'id_equipo');
    }
}