<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = 'sgc_documentos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'archivo',
        'nombre_original',
        'tipo_mime',
        'tamaño',
        'hash_md5',
        'creado_por',
        'creada_el',
        'modificada_el',
    ];

    protected $casts = [
        'creada_el' => 'datetime',
        'modificada_el' => 'datetime',
    ];

    public function carpetas()
    {
        return $this->belongsToMany(
            Carpeta3::class,
            'sgc_carpetas_contenido3',
            'id_documento',
            'id_carpeta'
        )->withPivot(['descripcion', 'metadata', 'creada_el', 'modificada_el']);
    }

    public function contenidos()
    {
        return $this->hasMany(CarpetaContenido3::class, 'id_documento');
    }

    public function getNombreVisibleAttribute()
    {
        return $this->nombre_original;
    }

    public function getTamañoFormateadoAttribute()
    {
        $bytes = $this->tamaño;
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $unidades[$i];
    }
}