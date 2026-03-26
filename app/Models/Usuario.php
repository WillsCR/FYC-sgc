<?php
// app/Models/Usuario.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'sgc_usuarios';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'email', 'nombre', 'fecha_ingreso', 'id_perfil',
        'ver_pozos', 'carga_pozos', 'ver_cursos', 'ver_control_instrumentos',
        'ver_control_no_conformidades', 'ver_sig', 'carga_sig',
        'ver_paritario', 'carga_paritario', 'ver_minsal', 'carga_minsal',
        'ver_ds44', 'carga_ds44', 'ver_susres', 'carga_susres',
        'ver_recres', 'carga_recres', 'ver_certcal', 'carga_certcal',
        'ver_epp', 'carga_epp', 'ver_man_infra', 'carga_man_infra',
        'ver_nminutas', 'carga_nminutas'
    ];

    protected $hidden = ['quesera'];

    public function carpetas()
    {
        return $this->belongsToMany(Carpeta::class, 'sgc_carpetas_permisos', 'id_usuario', 'id_carpeta');
    }

    public function noConformidades()
    {
        return $this->hasMany(NoConformidad::class, 'id_usuario');
    }

    public function minutas()
    {
        return $this->belongsToMany(Minuta::class, 'sgc_minutas_convocados', 'id_usuario', 'id_minuta');
    }

    public function tieneAccesoA($modulo)
    {
        $columna = 'ver_' . strtolower(str_replace(' ', '_', $modulo));
        return isset($this->$columna) && $this->$columna == 1;
    }

    public function puedeCargaEn($modulo)
    {
        $columna = 'carga_' . strtolower(str_replace(' ', '_', $modulo));
        return isset($this->$columna) && $this->$columna == 1;
    }
}