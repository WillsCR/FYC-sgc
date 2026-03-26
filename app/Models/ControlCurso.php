<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlCurso extends Model
{
    protected $table = 'sgc_control_cursos2';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'area', 'tema', 'cantidad', 'cantidad_evaluados', 'cantidad_aprobados',
        'auditor', 'fecha', 'documento', 'email_responsable'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    public function documentos()
    {
        return $this->hasMany(CursoDocumento::class, 'id_curso');
    }

    public function trabajadores()
    {
        return $this->belongsToMany(TrabajadorCurso::class, 'sgc_trabajadores_cursos', 'id_curso', 'id_trabajador');
    }
}