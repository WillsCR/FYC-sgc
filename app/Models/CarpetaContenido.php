<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarpetaContenido extends Model
{
    protected $table = 'sgc_carpetas_contenido';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_carpeta', 'correo', 'titulo', 'fecha_carga', 'ruta'
    ];

    protected $casts = [
        'fecha_carga' => 'datetime'
    ];

    public function carpeta()
    {
        return $this->belongsTo(Carpeta::class, 'id_carpeta');
    }

    public function getNombreArchivoAttribute()
    {
        return basename($this->ruta);
    }
}