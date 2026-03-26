<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Minuta extends Model
{
    protected $table = 'sgc_minutas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'numero', 'fecha', 'hora', 'lugar', 'responsable', 'asistentes',
        'temas_tratados', 'acuerdos', 'fecha_proximo_reunion', 'id_usuario'
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_proximo_reunion' => 'date'
    ];

    public function convocados()
    {
        return $this->hasMany(MinutaConvocado::class, 'id_minuta');
    }

    public function compromisos()
    {
        return $this->hasMany(MinutaCompromiso::class, 'id_minuta');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}