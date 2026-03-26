<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoConformidad extends Model
{
    protected $table = 'ges_no_conformidades';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'num_nc', 'fecha_deteccion', 'origen', 'area', 'tipo_accion',
        'nombre', 'cargo', 'descripcion', 'status_correccion',
        'fecha_implem_acc_corr', 'fecha_seguim_acc_corr', 'status_acc_corr',
        'status_seguim_acc_corr', 'accion_eficaz', 'fecha_cierre',
        'registros', 'observaciones', 'id_usuario', 'fecha_ingreso'
    ];

    protected $casts = [
        'fecha_deteccion' => 'date',
        'fecha_implem_acc_corr' => 'date',
        'fecha_seguim_acc_corr' => 'date',
        'fecha_cierre' => 'date',
        'fecha_ingreso' => 'datetime'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function accionesInmediatas()
    {
        return $this->hasMany(AccionInmediata::class, 'id_nc');
    }

    public function accionesCorrectivas()
    {
        return $this->hasMany(AccionCorrectiva::class, 'id_nc');
    }

    public function documentos()
    {
        return $this->hasMany(NoConformidadDoc::class, 'id_nc');
    }

    public function estadoTexto()
    {
        $estados = [
            0 => 'Abierta',
            1 => 'En proceso',
            2 => 'Cerrada'
        ];
        return $estados[$this->status_correccion] ?? 'Desconocido';
    }
}