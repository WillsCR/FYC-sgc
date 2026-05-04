<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publicacion extends Model
{
    protected $table      = 'sgc_publicaciones';
    public    $timestamps = false;

    protected $fillable = [
        'seccion', 'titulo', 'archivo', 'nombre_original',
        'tipo_mime', 'tamanio', 'creado_por', 'creada_el',
    ];

    protected $casts = [
        'creada_el' => 'datetime',
    ];

    /**
     * Tipos MIME que se pueden mostrar directamente en el navegador
     */
    public function esVisualizableEnLinea(): bool
    {
        return in_array($this->tipo_mime, [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ]);
    }

    /**
     * Formatea el tamaño del archivo en unidades legibles
     */
    public function tamanioFormateado(): string
    {
        $bytes = $this->tamanio;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1)    . ' KB';
        return $bytes . ' B';
    }
}
