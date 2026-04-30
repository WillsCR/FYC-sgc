<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioArea extends Model
{
    protected $table      = 'sgc_usuarios_areas';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $fillable = ['id_usuario', 'id_area'];
}
