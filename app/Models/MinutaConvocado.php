<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinutaConvocado extends Model
{
    protected $table = 'sgc_minutas_convocados';
    public $timestamps = false;

    protected $fillable = [
        'id_minuta',
        'id_usuario',
        'asistencia',
    ];

    public function minuta()
    {
        return $this->belongsTo(Minuta::class, 'id_minuta');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
