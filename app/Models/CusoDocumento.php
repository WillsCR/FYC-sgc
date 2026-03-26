<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CursoDocumento extends Model
{
    protected $table = 'sgc_cursos_documentos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_curso', 'documento', 'ruta', 'fecha_carga'
    ];

    protected $casts = [
        'fecha_carga' => 'datetime'
    ];

    public function curso()
    {
        return $this->belongsTo(ControlCurso::class, 'id_curso');
    }
}