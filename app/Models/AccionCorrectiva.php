<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccionCorrectiva extends Model
{
    protected $table = 'ges_acciones_correctivas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_nc', 'accion', 'causa_raiz', 'responsable', 'fecha_implementacion', 
        'estado', 'fecha_seguimiento', 'eficacia', 'fecha_cierre'
    ];

    protected $casts = [
        'fecha_implementacion' => 'date',
        'fecha_seguimiento' => 'date',
        'fecha_cierre' => 'date'
    ];

    public function noConformidad()
    {
        return $this->belongsTo(NoConformidad::class, 'id_nc');
    }

    public function responsables()
    {
        return $this->hasMany(AccionCorrectivaResp::class, 'id_accion');
    }
}