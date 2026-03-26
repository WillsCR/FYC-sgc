<?php
// app/Models/Carpeta.php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carpeta extends Model
{
    protected $table = 'sgc_carpetas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'descripcion', 'id_padre', 'nivel', 'creada_el', 'ruta'
    ];

    public function padre()
    {
        return $this->belongsTo(Carpeta::class, 'id_padre');
    }

    public function hijos()
    {
        return $this->hasMany(Carpeta::class, 'id_padre');
    }

    public function contenidos()
    {
        return $this->hasMany(CarpetaContenido::class, 'id_carpeta');
    }

    public function permisos()
    {
        return $this->hasMany(CarpetaPermisos::class, 'id_carpeta');
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'sgc_carpetas_permisos', 'id_carpeta', 'id_usuario');
    }

    public function tieneHijos()
    {
        return $this->hijos()->count() > 0;
    }
}