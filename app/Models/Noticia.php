<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    protected $table = 'sgc_noticias';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'titulo', 'contenido', 'fecha_publicacion', 'autor', 'imagen'
    ];

    protected $casts = [
        'fecha_publicacion' => 'datetime'
    ];
}