<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinutaCompromiso extends Model
{
    protected $table = 'sgc_minutas_compromisos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_minuta', 'compromiso', 'responsable', 'fecha_cumplimiento', 'estado'
    ];

    protected $casts = [
        'fecha_cumplimiento' => 'date'
    ];

    public function minuta()
    {
        return $this->belongsTo(Minuta::class, 'id_minuta');
    }
}