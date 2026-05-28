<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'sgc_areas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
    ];

    public function planificaciones()
    {
        return $this->hasMany(Planificacion::class, 'area');
    }

    public function usuariosAsignados(): int
    {
        return \App\Models\UsuarioArea::where('id_area', $this->id)->count();
    }
}