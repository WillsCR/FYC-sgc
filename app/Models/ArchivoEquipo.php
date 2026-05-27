<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArchivoEquipo extends Model
{
    protected $table = 'sgc_archivos_equipos';
    public $timestamps = false;

    protected $fillable = [
        'archivo_hash',
        'archivo_nombre',
        'archivo_extension',
        'archivo_mime',
        'archivo_tamanio',
        'ruta_legado',
        'ruta_tipo',
        'ruta_almacenamiento',
        'id_equipo_generico',
        'id_programa_verificacion',
        'id_equipo_interno',
        'tipo_documento',
        'id_usuario',
        'descripcion',
        'vigencia_hasta',
        'estado',
        'version',
        'reemplazo_de',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
        'fecha_modificacion' => 'datetime',
        'vigencia_hasta' => 'date',
    ];

    // Relaciones
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function programaVerificacion(): BelongsTo
    {
        return $this->belongsTo(ProgramaVerificacion::class, 'id_programa_verificacion');
    }

    public function equipoGenerico(): BelongsTo
    {
        return $this->belongsTo(EquipoGenerico::class, 'id_equipo_generico');
    }

    public function equipoInterno(): BelongsTo
    {
        return $this->belongsTo(EquipoInterno::class, 'id_equipo_interno');
    }

    public function auditoria(): HasMany
    {
        return $this->hasMany(ArchivoEquipoAuditoria::class, 'id_archivo');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorPrograma($query, $id_programa)
    {
        return $query->where('id_programa_verificacion', $id_programa);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }

    public function scopePorEquipo($query, $id_equipo)
    {
        return $query->where('id_equipo_generico', $id_equipo);
    }

    // Métodos auxiliares
    public function esVigente(): bool
    {
        if (!$this->vigencia_hasta) {
            return true;
        }
        return now()->lessThanOrEqualTo($this->vigencia_hasta);
    }

    public function getMbAttribute(): float
    {
        return round($this->archivo_tamanio / 1048576, 2);
    }
}

