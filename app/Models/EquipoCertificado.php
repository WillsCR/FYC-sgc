<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipoCertificado extends Model
{
    protected $table = 'sgc_equipos_certificados';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_equipo', 'certificado', 'fecha_vigencia', 'ruta_documento'
    ];

    protected $casts = [
        'fecha_vigencia' => 'date'
    ];

    public function equipo()
    {
        return $this->belongsTo(EquipoInterno::class, 'id_equipo');
    }

    public function estaVigente()
    {
        return $this->fecha_vigencia >= now()->toDateString();
    }
}