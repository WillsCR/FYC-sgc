<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Area;
use App\Models\CarpetasPermisos;
use App\Models\UsuarioArea;
use App\Models\UsuarioPermisoArea;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /** Carga el mapa id => descripcion de áreas desde la BD. */
    private function areasDisponibles(): array
    {
        return Area::orderBy('id')->pluck('descripcion', 'id')->toArray();
    }

    public function index()
    {
        $actual = PermisoService::usuarioActual();
        $perfil = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        $query = Usuario::orderBy('nombre');
        if ($perfil === 2) {
            $query->where('id_perfil', 4);
        }

        $areasMap = $this->areasDisponibles();
        $usuarios = $query->get()->map(function ($u) use ($areasMap) {
            $u->areas = UsuarioArea::where('id_usuario', $u->id)
                ->pluck('id_area')
                ->map(fn($id) => $areasMap[$id] ?? null)
                ->filter()->values();
            return $u;
        });

        return view('usuarios.index', compact('usuarios', 'actual'));
    }

    public function create()
    {
        $actual  = PermisoService::usuarioActual();
        $perfil  = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        $perfiles       = $this->perfilesDisponibles($perfil);
        $modulosCarpetas = $this->cargarModulosCarpetas();
        $areas           = $this->areasDisponibles();
        $permisosArea    = collect();

        return view('usuarios.crear', compact('actual', 'perfiles', 'modulosCarpetas', 'areas', 'permisosArea'));
    }

    public function store(Request $request)
    {
        $actual      = PermisoService::usuarioActual();
        $perfil      = (int) $actual->id_perfil;
        $perfilNuevo = (int) $request->id_perfil;

        $this->verificarAcceso($perfil);
        $this->validarPerfilAsignable($perfil, $perfilNuevo);

        $request->validate([
            'nombre'    => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'unique:sgc_usuarios,email'],
            'id_perfil' => ['required', 'integer', 'in:1,2,4'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique'       => 'Este correo ya está registrado.',
            'id_perfil.in'       => 'El perfil seleccionado no es válido.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario = new Usuario();
        $usuario->nombre        = $request->nombre;
        $usuario->email         = $request->email;
        $usuario->id_perfil     = $perfilNuevo;
        $usuario->quesera       = password_hash($request->password, PASSWORD_BCRYPT, ['cost' => 12]);
        $usuario->fecha_ingreso = now()->toDateString();

        if (in_array($perfilNuevo, [1, 2])) {
            foreach ($this->columnasBloque() as $col) {
                $usuario->$col = 1;
            }
        }

        $usuario->save();

        // Guardar permisos por área (áreas + permisos de plan/minutas)
        $this->guardarPermisosArea($usuario->id, $request->input('permisos_area', []));

        // Guardar permisos de carpetas
        if ($request->has('carpetas')) {
            $this->guardarPermisosCarpetas($usuario->id, $usuario->email, $usuario->quesera, $request->carpetas);
        }

        // Visibilidad de módulos dinámicos (bloque_dinamico[id] = 1)
        foreach (array_keys($request->input('bloque_dinamico', [])) as $carpetaId) {
            CarpetasPermisos::firstOrCreate(
                ['id_carpeta' => (int) $carpetaId, 'id_usuario' => $usuario->id],
                ['correo' => '', 'clave' => '', 'ver' => 0, 'carga' => 0, 'descarga' => 0,
                 'crear' => 0, 'ocultar_raiz' => 0, 'eliminar' => 0, 'editar' => 0]
            );
        }

        return redirect()->route('usuarios.index')
            ->with('ok', "Usuario \"{$usuario->nombre}\" creado correctamente.");
    }

    public function edit(int $id)
    {
        $actual     = PermisoService::usuarioActual();
        $perfil     = (int) $actual->id_perfil;
        $esSuCuenta = ($id === $actual->id);
        $this->verificarAcceso($perfil);

        $usuario     = Usuario::findOrFail($id);
        $soloLectura = ($perfil === 2 && in_array((int) $usuario->id_perfil, [1, 2]));

        $perfiles        = $this->perfilesDisponibles($perfil);
        $modulosCarpetas = $this->cargarModulosCarpetas();
        $permisosCarpetas = CarpetasPermisos::where('id_usuario', $id)->get()->keyBy('id_carpeta');
        $areas           = $this->areasDisponibles();

        $permisosArea = UsuarioPermisoArea::where('id_usuario', $id)
            ->get()
            ->keyBy('id_area');

        $bloques = [];
        foreach ($this->columnasBloque() as $col) {
            $bloques[$col] = (bool) $usuario->$col;
        }

        $idsBase = array_keys($this->modulosBase());
        $bloquesDinamicos = CarpetasPermisos::where('id_usuario', $id)
            ->join('sgc_carpetas3', 'sgc_carpetas3.id', '=', 'sgc_carpetas_permisos.id_carpeta')
            ->where('sgc_carpetas3.id_padre', 0)
            ->whereNotIn('sgc_carpetas3.id', $idsBase)
            ->pluck('sgc_carpetas_permisos.id_carpeta')
            ->toArray();

        return view('usuarios.editar', compact(
            'usuario', 'actual', 'perfiles', 'modulosCarpetas',
            'permisosCarpetas', 'bloques', 'areas', 'permisosArea', 'esSuCuenta', 'soloLectura',
            'bloquesDinamicos'
        ));
    }

    public function update(Request $request, int $id)
    {
        $actual        = PermisoService::usuarioActual();
        $perfil        = (int) $actual->id_perfil;
        $esSuCuenta    = ($id === $actual->id);
        $this->verificarAcceso($perfil);

        $usuario = Usuario::findOrFail($id);

        if ($perfil === 2 && in_array((int) $usuario->id_perfil, [1, 2])) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $request->validate([
            'nombre'    => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', "unique:sgc_usuarios,email,{$id}"],
            'id_perfil' => ['nullable', 'integer', 'in:1,2,4'],
            'password'  => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique'       => 'Este correo ya está en uso.',
            'id_perfil.in'       => 'El perfil seleccionado no es válido.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario->nombre = $request->nombre;
        $usuario->email  = $request->email;

        // Nadie puede cambiar su propio perfil
        // Super Admin puede asignar cualquier perfil; Admin solo puede asignar perfiles inferiores (no 1 ni 2)
        if (! $esSuCuenta) {
            $nuevoPerfilId = (int) $request->id_perfil;
            if ($perfil === 1) {
                $usuario->id_perfil = $nuevoPerfilId;
            } elseif ($perfil === 2 && ! in_array($nuevoPerfilId, [1, 2])) {
                $usuario->id_perfil = $nuevoPerfilId;
            }
        }

        if ($request->filled('password')) {
            $usuario->quesera = password_hash($request->password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $usuario->save();

        // Actualizar permisos por área
        $this->guardarPermisosArea($id, $request->input('permisos_area', []));

        // Actualizar permisos de carpetas
        CarpetasPermisos::where('id_usuario', $id)->delete();
        if ($request->has('carpetas')) {
            $this->guardarPermisosCarpetas($id, $usuario->email, $usuario->quesera, $request->carpetas);
        }

        // Visibilidad de módulos dinámicos (bloque_dinamico[id] = 1)
        foreach (array_keys($request->input('bloque_dinamico', [])) as $carpetaId) {
            CarpetasPermisos::firstOrCreate(
                ['id_carpeta' => (int) $carpetaId, 'id_usuario' => $id],
                ['correo' => '', 'clave' => '', 'ver' => 0, 'carga' => 0, 'descarga' => 0,
                 'crear' => 0, 'ocultar_raiz' => 0, 'eliminar' => 0, 'editar' => 0]
            );
        }

        return redirect()->route('usuarios.index')
            ->with('ok', "Usuario \"{$usuario->nombre}\" actualizado correctamente.");
    }

    public function destroy(int $id)
    {
        $actual = PermisoService::usuarioActual();
        if ((int) $actual->id_perfil !== 1) abort(403);
        if ($id === $actual->id) {
            return back()->withErrors(['error' => 'No puedes desactivarte a ti mismo.']);
        }

        $usuario = Usuario::findOrFail($id);
        if ((int) $usuario->id_perfil === 1) {
            return back()->withErrors(['error' => 'No puedes desactivar a otro Super Administrador.']);
        }

        foreach ($this->columnasBloque() as $col) {
            $usuario->$col = 0;
        }
        $usuario->save();

        return redirect()->route('usuarios.index')
            ->with('ok', "Usuario \"{$usuario->nombre}\" desactivado.");
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Guarda los permisos por área desde el formulario.
     * Estructura esperada del request:
     * permisos_area[id_area][ver_planificacion] = 1
     * permisos_area[id_area][editar_planificacion] = 1
     * permisos_area[id_area][ver_minutas] = 1
     * permisos_area[id_area][editar_minutas] = 1
     */
    private function guardarPermisosArea(int $usuarioId, array $permisosArea): void
    {
        // IDs de áreas válidas (leídas de BD)
        $idsValidos = Area::pluck('id')->toArray();

        // Eliminar permisos anteriores
        UsuarioPermisoArea::where('id_usuario', $usuarioId)->delete();
        UsuarioArea::where('id_usuario', $usuarioId)->delete();

        foreach ($permisosArea as $idArea => $perms) {
            $idArea = (int) $idArea;
            if (! in_array($idArea, $idsValidos, true)) continue;

            $verPlan    = ! empty($perms['ver_planificacion']);
            $editarPlan = ! empty($perms['editar_planificacion']);
            $verMin     = ! empty($perms['ver_minutas']);
            $editarMin  = ! empty($perms['editar_minutas']);

            // Solo guardar si tiene al menos un permiso activo
            if (! $verPlan && ! $editarPlan && ! $verMin && ! $editarMin) continue;

            // Registrar área asignada
            UsuarioArea::create(['id_usuario' => $usuarioId, 'id_area' => $idArea]);

            // Registrar permisos
            UsuarioPermisoArea::create([
                'id_usuario'           => $usuarioId,
                'id_area'              => $idArea,
                'ver_planificacion'    => $verPlan   ? 1 : 0,
                'editar_planificacion' => $editarPlan ? 1 : 0,
                'ver_minutas'          => $verMin     ? 1 : 0,
                'editar_minutas'       => $editarMin  ? 1 : 0,
            ]);
        }
    }

    private function verificarAcceso(int $perfil): void
    {
        if (! in_array($perfil, [1, 2])) abort(403);
    }

    private function perfilesDisponibles(int $perfil): \Illuminate\Support\Collection
    {
        // Solo se usan 3 perfiles en esta plataforma: Super Admin (1), Admin (2), Trabajador (4)
        if ($perfil === 1) {
            return DB::table('ser_perfiles')
                ->whereIn('id_perfil', [1, 2, 4])
                ->orderBy('id_perfil')
                ->get();
        }
        // Admin solo puede crear/editar Trabajadores
        return DB::table('ser_perfiles')->where('id_perfil', 4)->get();
    }

    private function validarPerfilAsignable(int $perfilActual, int $perfilNuevo): void
    {
        // Solo se permiten los 3 perfiles activos en la plataforma
        if (! in_array($perfilNuevo, [1, 2, 4])) abort(403);
        // Admin solo puede asignar Trabajador
        if ($perfilActual === 2 && $perfilNuevo !== 4) abort(403);
    }

    private function guardarPermisosCarpetas(int $usuarioId, string $correo, string $clave, array $carpetas): void
    {
        // Módulos especiales: 3 niveles → Sin acceso / Solo visualización / Acceso completo
        $carpetasPermUnico = [9, 11, 98, 99, 105]; // NC, Cert. Calidad, CI, Matriz Cursos, Matriz Competencias

        foreach ($carpetas as $carpetaId => $perms) {
            $carpetaId = (int) $carpetaId;

            // ── Módulos especiales: 2 opciones ───────────────────────────────
            // nivel_acceso = 'ver' | 'completo' | 'ninguno' / ausente
            if (in_array($carpetaId, $carpetasPermUnico, true)) {
                $nivel    = trim($perms['nivel_acceso'] ?? 'ninguno');
                $cargaVal = $nivel === 'completo' ? 1 : 0;

                // Sin acceso: borrar registro si existe
                if (! in_array($nivel, ['ver', 'completo'], true)) {
                    CarpetasPermisos::where('id_carpeta', $carpetaId)
                        ->where('id_usuario', $usuarioId)->delete();
                    continue;
                }

                CarpetasPermisos::updateOrCreate(
                    ['id_carpeta' => $carpetaId, 'id_usuario' => $usuarioId],
                    [
                        'correo'       => '',
                        'clave'        => '',
                        'ver'          => 1,       // siempre 1 para ambas opciones
                        'carga'        => $cargaVal,
                        'descarga'     => $cargaVal,
                        'crear'        => $cargaVal,
                        'ocultar_raiz' => 0,
                        'eliminar'     => $cargaVal,
                        'editar'       => $cargaVal,
                    ]
                );
                continue;
            }

            // ── Carpetas con permisos granulares (comportamiento normal) ──────
            $tieneAlguno = collect(['carga','descarga','crear','eliminar','editar'])
                ->some(fn($p) => ! empty($perms[$p]));
            if (! $tieneAlguno) continue;

            CarpetasPermisos::updateOrCreate(
                ['id_carpeta' => $carpetaId, 'id_usuario' => $usuarioId],
                [
                    'correo'       => '',
                    'clave'        => '',
                    'carga'        => ! empty($perms['carga'])    ? 1 : 0,
                    'descarga'     => ! empty($perms['descarga']) ? 1 : 0,
                    'crear'        => ! empty($perms['crear'])    ? 1 : 0,
                    'ocultar_raiz' => 0,
                    'eliminar'     => ! empty($perms['eliminar']) ? 1 : 0,
                    'editar'       => ! empty($perms['editar'])   ? 1 : 0,
                ]
            );
        }
    }

    /**
     * Carga los módulos raíz de sgc_carpetas3 con sus submodulos anidados.
     * Marca como `es_dinamico` aquellos que no forman parte del sistema base.
     */
    private function cargarModulosCarpetas(): \Illuminate\Support\Collection
    {
        $idsBase = array_keys($this->modulosBase());

        $raices = DB::table('sgc_carpetas3')
            ->where('id_padre', 0)
            ->orderBy('id')
            ->get();

        return $raices->map(function ($modulo) use ($idsBase) {
            $modulo->submodulos  = DB::table('sgc_carpetas3')
                ->where('id_padre', $modulo->id)
                ->orderBy('descripcion')
                ->get();
            $modulo->es_dinamico = ! in_array($modulo->id, $idsBase, true);
            return $modulo;
        });
    }

    /**
     * Las 8 columnas bloque_* que corresponden a los módulos actuales de sgc_carpetas3.
     */
    private function columnasBloque(): array
    {
        return [
            'bloque_sig', 'bloque_seguridad', 'bloque_ambiente', 'bloque_rrhh',
            'bloque_abastecimiento', 'bloque_proyectos', 'bloque_gerencia', 'bloque_finanzas',
        ];
    }

    /**
     * Módulos base del sistema: id => [emoji, nombre corto, columna_bloque]
     */
    private function modulosBase(): array
    {
        return [
            1 => ['📋', 'Control SIG',      'bloque_sig'],
            2 => ['🌿', 'Medio Ambiente',    'bloque_ambiente'],
            3 => ['🛡️', 'Seguridad SST',    'bloque_seguridad'],
            4 => ['🏗️', 'Abastecimiento',   'bloque_abastecimiento'],
            5 => ['👨‍💼','RRHH',              'bloque_rrhh'],
            6 => ['🏢', 'Gerencia',          'bloque_gerencia'],
            7 => ['📈', 'Proyectos',         'bloque_proyectos'],
            8 => ['💰', 'Finanzas',          'bloque_finanzas'],
        ];
    }
}
