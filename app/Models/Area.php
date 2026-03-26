<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'sgc_areas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'descripcion', 'jefe', 'email'
    ];

    public function planificaciones()
    {
        return $this->hasMany(Planificacion::class, 'area');
    }
}