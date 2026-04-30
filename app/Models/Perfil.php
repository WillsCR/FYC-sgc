<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table      = 'ser_perfiles';
    protected $primaryKey = 'id_perfil';
    public    $timestamps = false;

    protected $fillable = ['id_perfil', 'nombre', 'estado', 'descripcion'];
}
