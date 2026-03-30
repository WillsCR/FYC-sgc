<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $table = 'sgc_videos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'url',
        'descripcion',
        'fecha_carga',
    ];

    protected $casts = [
        'fecha_carga' => 'datetime',
    ];
}
