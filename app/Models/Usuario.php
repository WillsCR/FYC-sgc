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
    ];

    protected $hidden = ['quesera'];

    protected $casts = [
        // Permisos de módulos y acciones
        'carga'                      => 'boolean',
        'planificacion'              => 'boolean',
        'editar_planificacion'       => 'boolean',
        'agregar_planificacion'      => 'boolean',
        'agregar_minutas'            => 'boolean',
        'descarga'                   => 'boolean',
        'crear'                      => 'boolean',
        'ocultar_raiz'               => 'boolean',
        'eliminar'                   => 'boolean',
        'editar'                     => 'boolean',
        'ver_pozos'                  => 'boolean',
        'ver_cursos'                 => 'boolean',
        'ver_btn_matriz'             => 'boolean',
        'ver_btn_maq'                => 'boolean',
        'ver_btn_ctrl_pozos'         => 'boolean',
        'ver_btn_rrhh'               => 'boolean',
        'ocultar_lateral'            => 'boolean',
        'carga_pozos'                => 'boolean',
        'ver_control_instrumentos'   => 'boolean',
        'ver_control_no_conformidades'=> 'boolean',
        'ver_sig'                    => 'boolean',
        'carga_sig'                  => 'boolean',
        'editar_control_instrumentos'=> 'boolean',
        'editar_cursos'              => 'boolean',

        // Bloques originales
        'bloque_sig'                 => 'boolean',
        'bloque_seguridad'           => 'boolean',
        'bloque_ambiente'            => 'boolean',
        'bloque_rrhh'                => 'boolean',
        'bloque_abastecimiento'      => 'boolean',
        'bloque_proyectos'           => 'boolean',

        // Bloques nuevos (requieren ALTER TABLE)
        'bloque_gerencia'            => 'boolean',
        'bloque_patio'               => 'boolean',
        'bloque_calidad'             => 'boolean',
        'bloque_docs_legales'        => 'boolean',
        'bloque_formatos'            => 'boolean',
        'bloque_listado_interes'     => 'boolean',

        // Otros permisos
        'carga_no_conformidades'     => 'boolean',
        'ver_paritario'              => 'boolean',
        'carga_paritario'            => 'boolean',
        'ver_minsal'                 => 'boolean',
        'carga_minsal'               => 'boolean',
        'ver_ds44'                   => 'boolean',
        'carga_ds44'                 => 'boolean',
        'ver_susres'                 => 'boolean',
        'carga_susres'               => 'boolean',
        'ver_recres'                 => 'boolean',
        'carga_recres'               => 'boolean',
        'ver_certcal'                => 'boolean',
        'carga_certcal'              => 'boolean',
        'ver_epp'                    => 'boolean',
        'carga_epp'                  => 'boolean',
        'ver_man_infra'              => 'boolean',
        'carga_man_infra'            => 'boolean',
        'ver_nminutas'               => 'boolean',
        'carga_nminutas'             => 'boolean',
    ];

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function esAdmin(): bool
    {
        return (int) $this->id_perfil === 1;
    }

    public function puedeVer(string $permiso): bool
    {
        return (bool) ($this->$permiso ?? false);
    }

    public function bloquesVisibles(): array
    {
        $bloques = [
            'sig'             => 'bloque_sig',
            'seguridad'       => 'bloque_seguridad',
            'ambiente'        => 'bloque_ambiente',
            'rrhh'            => 'bloque_rrhh',
            'abastecimiento'  => 'bloque_abastecimiento',
            'proyectos'       => 'bloque_proyectos',
            'gerencia'        => 'bloque_gerencia',
            'patio'           => 'bloque_patio',
            'calidad'         => 'bloque_calidad',
            'docs_legales'    => 'bloque_docs_legales',
            'formatos'        => 'bloque_formatos',
            'listado_interes' => 'bloque_listado_interes',
        ];

        return array_keys(array_filter(
            $bloques,
            fn($col) => $this->puedeVer($col)
        ));
    }

    // ─── Relaciones ─────────────────────────────────────────────────────────

    public function permisosCarpetas()
    {
        return $this->hasMany(CarpetasPermisos::class, 'id_usuario');
    }
}
