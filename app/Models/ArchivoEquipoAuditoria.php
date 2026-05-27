<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoEquipoAuditoria extends Model
{
    protected $table = 'sgc_archivos_equipos_auditoria';
    public $timestamps = false;

    protected $fillable = [
        'id_archivo',
        'accion',
        'usuario_id',
        'detalles',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'detalles' => 'array',
    ];

    public function archivo()
    {
        return $this->belongsTo(ArchivoEquipo::class, 'id_archivo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
