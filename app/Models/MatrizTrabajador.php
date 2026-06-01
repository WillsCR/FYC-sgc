<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatrizTrabajador extends Model
{
    protected $table    = 'sgc_matriz_trabajadores';
    protected $fillable = ['nombres', 'apellidos', 'rut', 'cargo', 'contrato'];

    public function cursos()
    {
        return $this->hasMany(MatrizCurso::class, 'id_trabajador')->orderBy('fecha_vencimiento');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }
}
