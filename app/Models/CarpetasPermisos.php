<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarpetaPermisos extends Model
{
    protected $table = 'sgc_carpetas_permisos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_carpeta', 'id_usuario', 'carga', 'descarga', 'crear', 'eliminar', 'editar'
    ];

    public function carpeta()
    {
        return $this->belongsTo(Carpeta::class, 'id_carpeta');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}