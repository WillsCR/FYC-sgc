<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planificacion extends Model
{
    protected $table = 'sgc_planificaciones';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'area', 'actividad', 'fecha_inicio', 'fecha_termino', 'responsable',
        'estado', 'observaciones', 'id_usuario'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_termino' => 'date'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}