<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table      = 'sgc_usuarios';
    protected $primaryKey = 'id';
    public    $timestamps = false;

    protected $authPasswordName = 'quesera';

    protected $fillable = [
        'email', 'quesera', 'fecha_ingreso', 'id_perfil', 'nombre',
        'bloque_sig', 'bloque_seguridad', 'bloque_ambiente', 'bloque_rrhh',
        'bloque_abastecimiento', 'bloque_proyectos', 'bloque_gerencia',
        'bloque_patio', 'bloque_calidad', 'bloque_docs_legales',
        'bloque_formatos', 'bloque_listado_interes',
    ];

    protected $hidden = ['quesera'];

    protected $casts = [
        'carga'                       => 'boolean',
        'planificacion'               => 'boolean',
        'editar_planificacion'        => 'boolean',
        'agregar_planificacion'       => 'boolean',
        'agregar_minutas'             => 'boolean',
        'descarga'                    => 'boolean',
        'crear'                       => 'boolean',
        'ocultar_raiz'                => 'boolean',
        'eliminar'                    => 'boolean',
        'editar'                      => 'boolean',
        'ver_pozos'                   => 'boolean',
        'ver_cursos'                  => 'boolean',
        'ver_btn_matriz'              => 'boolean',
        'ver_btn_maq'                 => 'boolean',
        'ver_btn_ctrl_pozos'          => 'boolean',
        'ver_btn_rrhh'                => 'boolean',
        'ocultar_lateral'             => 'boolean',
        'carga_pozos'                 => 'boolean',
        'ver_control_instrumentos'    => 'boolean',
        'ver_control_no_conformidades'=> 'boolean',
        'ver_sig'                     => 'boolean',
        'carga_sig'                   => 'boolean',
        'editar_control_instrumentos' => 'boolean',
        'editar_cursos'               => 'boolean',
        'bloque_sig'                  => 'boolean',
        'bloque_seguridad'            => 'boolean',
        'bloque_ambiente'             => 'boolean',
        'bloque_rrhh'                 => 'boolean',
        'bloque_abastecimiento'       => 'boolean',
        'bloque_proyectos'            => 'boolean',
        'bloque_gerencia'             => 'boolean',
        'bloque_patio'                => 'boolean',
        'bloque_calidad'              => 'boolean',
        'bloque_docs_legales'         => 'boolean',
        'bloque_formatos'             => 'boolean',
        'bloque_listado_interes'      => 'boolean',
        'carga_no_conformidades'      => 'boolean',
        'ver_paritario'               => 'boolean',
        'carga_paritario'             => 'boolean',
        'ver_minsal'                  => 'boolean',
        'carga_minsal'                => 'boolean',
        'ver_ds44'                    => 'boolean',
        'carga_ds44'                  => 'boolean',
        'ver_susres'                  => 'boolean',
        'carga_susres'                => 'boolean',
        'ver_recres'                  => 'boolean',
        'carga_recres'                => 'boolean',
        'ver_certcal'                 => 'boolean',
        'carga_certcal'               => 'boolean',
        'ver_epp'                     => 'boolean',
        'carga_epp'                   => 'boolean',
        'ver_man_infra'               => 'boolean',
        'carga_man_infra'             => 'boolean',
        'ver_nminutas'                => 'boolean',
        'carga_nminutas'              => 'boolean',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    /**
     * Fix: usar App\Models\Perfil en lugar de Eloquent\Model (abstracta)
     */
    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'id_perfil', 'id_perfil');
    }

    public function permisosCarpetas()
    {
        return $this->hasMany(CarpetasPermisos::class, 'id_usuario');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function esAdmin(): bool
    {
        return in_array((int) $this->id_perfil, [1, 2]);
    }

    public function esSuperAdmin(): bool
    {
        return (int) $this->id_perfil === 1;
    }

    public function nombrePerfil(): string
    {
        return match((int) $this->id_perfil) {
            1       => 'Super Administrador',
            2       => 'Administrador',
            4       => 'Trabajador',
            default => 'Perfil ' . $this->id_perfil,
        };
    }

    public function colorPerfil(): string
    {
        return match((int) $this->id_perfil) {
            1       => '#0D2B5E',
            2       => '#1D4ED8',
            4       => '#64748B',
            default => '#94A3B8',
        };
    }

    public function puedeVer(string $permiso): bool
    {
        if ($this->esSuperAdmin()) return true;
        return (bool) ($this->$permiso ?? false);
    }
}
