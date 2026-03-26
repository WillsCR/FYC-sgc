<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrabajadorCurso extends Model
{
    protected $table = 'sgc_trabajadores_cursos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'trabajador', 'rut', 'cargo', 'area', 'id_curso', 
        'evaluado', 'aprobado', 'fecha', 'auditor'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    public function curso()
    {
        return $this->belongsTo(ControlCurso::class, 'id_curso');
    }
}