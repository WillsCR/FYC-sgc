<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccionCorrectivaResp extends Model
{
    protected $table = 'ges_acciones_correctivas_resp';
    public $timestamps = false;

    protected $fillable = [
        'id_accion',
        'id_usuario',
        'responsable',
    ];

    public function accionCorrectiva()
    {
        return $this->belongsTo(AccionCorrectiva::class, 'id_accion');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
