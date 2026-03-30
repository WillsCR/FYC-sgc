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

    /**
     * Columna de contraseña — Laravel la usa para Auth::attempt()
     */
    protected $authPasswordName = 'quesera';

    protected $fillable = [
        'email',
        'quesera',
        'clave',
        'fecha_ingreso',
        'id_perfil',
        'nombre',
    ];

    protected $hidden = ['quesera'];

    protected $casts = [
        // Permisos booleanos — el legacy los guarda como tinyint/varchar
        'carga'                    => 'boolean',
        'planificacion'            => 'boolean',
        'editar_planificacion'     => 'boolean',
        'agregar_planificacion'    => 'boolean',
        'agregar_minutas'          => 'boolean',
        'descarga'                 => 'boolean',
        'crear'                    => 'boolean',
        'ocultar_raiz'             => 'boolean',
        'eliminar'                 => 'boolean',
        'editar'                   => 'boolean',
        'ver_pozos'                => 'boolean',
        'ver_cursos'               => 'boolean',
        'ver_btn_matriz'           => 'boolean',
        'ver_btn_maq'              => 'boolean',
        'ver_btn_control_pozos'    => 'boolean',
        'ver_btn_rrhh'             => 'boolean',
        'ocultar_lateral'          => 'boolean',
        'carga_pozos'              => 'boolean',
        'ver_control_instrumentos' => 'boolean',
        'ver_control_no_conformida'=> 'boolean',
        'ver_sig'                  => 'boolean',
        'carga_sig'                => 'boolean',
        'editar_control_instrumento'=> 'boolean',
        'editar_cursos'            => 'boolean',
        'bloque_sig'               => 'boolean',
        'bloque_seguridad'         => 'boolean',
        'bloque_ambiente'          => 'boolean',
        'bloque_rrhh'              => 'boolean',
        'bloque_abastecimiento'    => 'boolean',
        'bloque_proyectos'         => 'boolean',
        'carga_no_conformidades'   => 'boolean',
        'ver_paritario'            => 'boolean',
        'carga_paritario'          => 'boolean',
        'ver_minsal'               => 'boolean',
        'carga_minsal'             => 'boolean',
        'ver_ds44'                 => 'boolean',
        'carga_ds44'               => 'boolean',
        'ver_susres'               => 'boolean',
        'carga_susres'             => 'boolean',
        'ver_recres'               => 'boolean',
        'carga_recres'             => 'boolean',
        'ver_certcal'              => 'boolean',
        'carga_certcal'            => 'boolean',
        'ver_epp'                  => 'boolean',
        'carga_epp'                => 'boolean',
        'ver_man_infra'            => 'boolean',
        'carga_man_infra'          => 'boolean',
        'ver_nminutas'             => 'boolean',
        'carga_nminutas'           => 'boolean',
    ];

    // ─── Helpers de permisos ────────────────────────────────────────────────

    /**
     * Verifica si el usuario es administrador (perfil 1 según ser_perfiles)
     */
    public function esAdmin(): bool
    {
        return (int) $this->id_perfil === 1;
    }

    /**
     * Verifica un permiso booleano por nombre de columna
     * Uso: $usuario->can('bloque_sig')
     */
    public function puedeVer(string $permiso): bool
    {
        return (bool) ($this->$permiso ?? false);
    }

    /**
     * Devuelve los bloques de módulos visibles para este usuario
     */
    public function bloquesVisibles(): array
    {
        $bloques = [
            'sig'           => 'bloque_sig',
            'seguridad'     => 'bloque_seguridad',
            'ambiente'      => 'bloque_ambiente',
            'rrhh'          => 'bloque_rrhh',
            'abastecimiento'=> 'bloque_abastecimiento',
            'proyectos'     => 'bloque_proyectos',
        ];

        return array_keys(array_filter(
            $bloques,
            fn($col) => $this->puedeVer($col)
        ));
    }

    // ─── Relaciones ─────────────────────────────────────────────────────────

    public function permisosCarpetas()
    {
        return $this->hasMany(CarpetaPermiso::class, 'id_usuario');
    }
}
