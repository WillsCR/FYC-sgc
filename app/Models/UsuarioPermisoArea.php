<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioPermisoArea extends Model
{
    protected $table      = 'sgc_usuarios_permisos_area';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_area',
        'ver_planificacion',
        'editar_planificacion',
        'ver_minutas',
        'editar_minutas',
    ];

    protected $casts = [
        'ver_planificacion'    => 'boolean',
        'editar_planificacion' => 'boolean',
        'ver_minutas'          => 'boolean',
        'editar_minutas'       => 'boolean',
    ];
}
