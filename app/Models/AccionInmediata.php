<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccionInmediata extends Model
{
    protected $table = 'ges_acciones_inmediatas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_nc', 'accion', 'responsable', 'fecha_implementacion', 'estado', 'fecha_cierre'
    ];

    protected $casts = [
        'fecha_implementacion' => 'date',
        'fecha_cierre' => 'date'
    ];

    public function noConformidad()
    {
        return $this->belongsTo(NoConformidad::class, 'id_nc');
    }

    public function responsables()
    {
        return $this->hasMany(AccionInmediataResp::class, 'id_accion');
    }
}