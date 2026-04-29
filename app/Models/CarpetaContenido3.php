<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarpetaContenido3 extends Model
{
    protected $table = 'sgc_carpetas_contenido3';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_carpeta',
        'id_documento',
        'descripcion',
        'metadata',
        'creada_el',
        'modificada_el',
    ];

    protected $casts = [
        'metadata' => 'array',
        'creada_el' => 'datetime',
        'modificada_el' => 'datetime',
    ];

    public function carpeta()
    {
        return $this->belongsTo(Carpeta3::class, 'id_carpeta');
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class, 'id_documento');
    }
}