<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carpeta3 extends Model
{
    protected $table = 'sgc_carpetas3';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'id_padre',
        'nivel',
        'creada_el',
        'ruta',
    ];

    protected $casts = [
        'creada_el' => 'datetime',
    ];

    public function padre()
    {
        return $this->belongsTo(Carpeta3::class, 'id_padre');
    }

    public function hijos()
    {
        return $this->hasMany(Carpeta3::class, 'id_padre');
    }

    public function documentos()
    {
        return $this->belongsToMany(
            Documento::class,
            'sgc_carpetas_contenido3',
            'id_carpeta',
            'id_documento'
        )->withPivot(['descripcion', 'metadata', 'creada_el', 'modificada_el']);
    }

    public function contenidos()
    {
        return $this->hasMany(CarpetaContenido3::class, 'id_carpeta');
    }

    public function obtenerRuta(): string
    {
        $ruta = [];
        $actual = $this;

        while ($actual) {
            array_unshift($ruta, $actual->descripcion);
            $actual = $actual->padre;
        }

        return implode('/', $ruta);
    }

    public function tieneHijos(): bool
    {
        return $this->hijos()->count() > 0;
    }

    public function obtenerBreadcrumb(): array
    {
        $breadcrumb = [];
        $actual = $this;

        while ($actual) {
            array_unshift($breadcrumb, [
                'id' => $actual->id,
                'nombre' => $actual->descripcion,
            ]);
            $actual = $actual->padre;
        }

        return $breadcrumb;
    }
}