<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarpetaPermiso extends Model
{
    protected $table      = 'sgc_carpetas_permisos';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = [
        'id_carpeta',
        'id_usuario',
        'correo',
        'clave',
        'carga',
        'descarga',
        'crear',
        'ocultar_raiz',
        'eliminar',
        'editar',
    ];

    protected $casts = [
        'carga'      => 'boolean',
        'descarga'   => 'boolean',
        'crear'      => 'boolean',
        'ocultar_raiz' => 'boolean',
        'eliminar'   => 'boolean',
        'editar'     => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function carpeta()
    {
        return $this->belongsTo(Carpeta::class, 'id_carpeta');
    }
}
