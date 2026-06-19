<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatrizTrabajador extends Model
{
    protected $table    = 'sgc_matriz_trabajadores';
    protected $fillable = [
        'nombres', 'apellidos', 'rut', 'cargo', 'contrato',
        'nivel_estudios', 'especialidad',
        'cert_titulo_nombre', 'cert_titulo_ruta', 'cert_titulo_mime',
        'cv_nombre', 'cv_ruta', 'cv_mime',
        'cumple',
    ];

    protected $casts = ['cumple' => 'boolean'];

    public function cursos()
    {
        return $this->hasMany(MatrizCurso::class, 'id_trabajador')->orderBy('fecha_vencimiento');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }
}
