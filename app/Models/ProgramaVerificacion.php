<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramaVerificacion extends Model
{
    protected $table = 'sgc_programa_verificacion';
    public $timestamps = false;

    protected $fillable = [
        'id_equipo_interno',
        'id_contrato',
        'id_equipo_generico',
        'id_usuario',
        'descripcion',
        'marca',
        'modelo',
        'serie',
        'interno',
        'calibracion',
        'verificacion',
        'certificado_calibracion',
        'ultima',
        'proxima',
        'cert_calidad',
        'cert_calibracion',
        'observaciones',
        'responsable',
        'correo_aviso',
        'aviso_30d_enviado',
    ];

    protected $casts = [
        'ultima'            => 'date',
        'proxima'           => 'date',
        'aviso_30d_enviado' => 'boolean',
        'cert_calidad'    => 'boolean',
        'cert_calibracion'=> 'boolean',
    ];

    // ── Relaciones ──────────────────────────────────────────────────────────

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_contrato');
    }

    public function responsables()
    {
        return $this->hasMany(EquipoResponsable::class, 'id_programa_verificacion');
    }

    public function archivos()
    {
        return $this->hasMany(ArchivoEquipo::class, 'id_programa_verificacion')
                    ->where('estado', 'activo');
    }

    public function archivosCalidad()
    {
        return $this->hasMany(ArchivoEquipo::class, 'id_programa_verificacion')
                    ->where('tipo_documento', 'cert_calidad')
                    ->where('estado', 'activo');
    }

    public function archivosCalibra()
    {
        return $this->hasMany(ArchivoEquipo::class, 'id_programa_verificacion')
                    ->where('tipo_documento', 'cert_calibracion')
                    ->where('estado', 'activo');
    }
}
