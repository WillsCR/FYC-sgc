<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $table = 'sgc_videos';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'archivo',
        'nombre_original',
        'tipo_mime',
        'tamanio',
        'creado_por',
        'creada_el',
    ];

    protected $casts = [
        'creada_el' => 'datetime',
    ];

    public function tamanioFormateado(): string
    {
        $bytes = $this->tamanio;
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $unidades[$i];
    }
}
