<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\CarpetasPermisos;
use App\Models\UsuarioArea;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    // Áreas del sistema
    private const AREAS = [
        1  => 'Recursos Humanos',
        2  => 'Seguridad y Salud en el Trabajo',
        3  => 'Abastecimiento y Finanzas',
        4  => 'Contrato Pozos',
        5  => 'Medio Ambiente',
        6  => 'Control SGI',
        7  => 'SGI Gestión',
        8  => 'Patios e Infraestructura',
        9  => 'Gerencia de Operaciones',
        10 => 'Gerencia General',
    ];

    public function index()
    {
        $actual = PermisoService::usuarioActual();
        $perfil = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        $query = Usuario::orderBy('nombre');
        if ($perfil === 2) {
            $query->where('id_perfil', 4);
        }

        $usuarios = $query->get()->map(function ($u) {
            // Áreas asignadas del usuario
            $u->areas = UsuarioArea::where('id_usuario', $u->id)
                ->pluck('id_area')
                ->map(fn($id) => self::AREAS[$id] ?? null)
                ->filter()
                ->values();
            return $u;
        });

        $perfiles = $this->perfilesDisponibles($perfil);

        return view('usuarios.index', compact('usuarios', 'actual', 'perfiles'));
    }

    public function create()
    {
        $actual   = PermisoService::usuarioActual();
        $perfil   = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        $perfiles   = $this->perfilesDisponibles($perfil);
        $carpetas   = DB::table('sgc_carpetas')->where('id_padre', 0)->orderBy('descripcion')->get();
        $areas      = self::AREAS;

        return view('usuarios.crear', compact('actual', 'perfiles', 'carpetas', 'areas'));
    }

    public function store(Request $request)
    {
        $actual = PermisoService::usuarioActual();
        $perfil = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        $perfilNuevo = (int) $request->id_perfil;
        $this->validarPerfilAsignable($perfil, $perfilNuevo);

        $request->validate([
            'nombre'    => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'unique:sgc_usuarios,email'],
            'id_perfil' => ['required', 'integer'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique'       => 'Este correo ya está registrado.',
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

        // Guardar áreas
        $this->guardarAreas($usuario->id, $request->input('areas', []));

        // Guardar permisos de carpetas
        if ($request->has('carpetas')) {
            $this->guardarPermisosCarpetas($usuario->id, $usuario->email, $usuario->quesera, $request->carpetas);
        }

        return redirect()->route('usuarios.index')
            ->with('ok', "Usuario \"{$usuario->nombre}\" creado correctamente.");
    }

    public function edit(int $id)
    {
        $actual = PermisoService::usuarioActual();
        $perfil = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        $usuario = Usuario::findOrFail($id);

        if ($perfil === 2 && in_array((int) $usuario->id_perfil, [1, 2])) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $perfiles         = $this->perfilesDisponibles($perfil);
        $carpetas         = DB::table('sgc_carpetas')->where('id_padre', 0)->orderBy('descripcion')->get();
        $permisosCarpetas = CarpetasPermisos::where('id_usuario', $id)->get()->keyBy('id_carpeta');
        $areas            = self::AREAS;

        // Áreas actualmente asignadas
        $areasAsignadas = UsuarioArea::where('id_usuario', $id)->pluck('id_area')->toArray();

        $bloques = [];
        foreach ($this->columnasBloque() as $col) {
            $bloques[$col] = (bool) $usuario->$col;
        }

        return view('usuarios.editar', compact(
            'usuario', 'actual', 'perfiles', 'carpetas',
            'permisosCarpetas', 'bloques', 'areas', 'areasAsignadas'
        ));
    }

    public function update(Request $request, int $id)
    {
        $actual = PermisoService::usuarioActual();
        $perfil = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        $usuario = Usuario::findOrFail($id);

        if ($perfil === 2 && in_array((int) $usuario->id_perfil, [1, 2])) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $request->validate([
            'nombre'    => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', "unique:sgc_usuarios,email,{$id}"],
            'id_perfil' => ['required', 'integer'],
            'password'  => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique'       => 'Este correo ya está en uso por otro usuario.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario->nombre    = $request->nombre;
        $usuario->email     = $request->email;
        $usuario->id_perfil = (int) $request->id_perfil;

        if ($request->filled('password')) {
            $usuario->quesera = password_hash($request->password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        foreach ($this->columnasBloque() as $col) {
            $usuario->$col = $request->has("bloques.{$col}") ? 1 : 0;
        }

        $usuario->save();

        // Actualizar áreas
        $this->guardarAreas($id, $request->input('areas', []));

        // Actualizar permisos de carpetas
        CarpetasPermisos::where('id_usuario', $id)->delete();
        if ($request->has('carpetas')) {
            $this->guardarPermisosCarpetas($id, $usuario->email, $usuario->quesera, $request->carpetas);
        }

        return redirect()->route('usuarios.index')
            ->with('ok', "Usuario \"{$usuario->nombre}\" actualizado correctamente.");
    }

    public function destroy(int $id)
    {
        $actual = PermisoService::usuarioActual();
        if ((int) $actual->id_perfil !== 1) {
            abort(403, 'Solo el Super Administrador puede desactivar usuarios.');
        }
        if ($id === $actual->id) {
            return back()->withErrors(['error' => 'No puedes desactivar tu propio usuario.']);
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

    private function guardarAreas(int $usuarioId, array $areas): void
    {
        // Eliminar áreas anteriores y reasignar
        UsuarioArea::where('id_usuario', $usuarioId)->delete();

        foreach ($areas as $idArea) {
            $idArea = (int) $idArea;
            if (array_key_exists($idArea, self::AREAS)) {
                UsuarioArea::create([
                    'id_usuario' => $usuarioId,
                    'id_area'    => $idArea,
                ]);
            }
        }
    }

    private function verificarAcceso(int $perfil): void
    {
        if (! in_array($perfil, [1, 2])) {
            abort(403, 'No tienes permiso para gestionar usuarios.');
        }
    }

    private function perfilesDisponibles(int $perfil): \Illuminate\Support\Collection
    {
        if ($perfil === 1) {
            return DB::table('ser_perfiles')->where('estado', 1)->orderBy('id_perfil')->get();
        }
        return DB::table('ser_perfiles')->where('id_perfil', 4)->get();
    }

    private function validarPerfilAsignable(int $perfilActual, int $perfilNuevo): void
    {
        if ($perfilActual === 2 && $perfilNuevo !== 4) {
            abort(403, 'Los administradores solo pueden crear usuarios con perfil Trabajador.');
        }
    }

    private function guardarPermisosCarpetas(int $usuarioId, string $correo, string $clave, array $carpetas): void
    {
        foreach ($carpetas as $carpetaId => $perms) {
            $tieneAlguno = collect(['carga','descarga','crear','eliminar','editar'])
                ->some(fn($p) => ! empty($perms[$p]));

            if (! $tieneAlguno) continue;

            CarpetasPermisos::updateOrCreate(
                ['id_carpeta' => $carpetaId, 'id_usuario' => $usuarioId],
                [
                    'correo'       => $correo,
                    'clave'        => $clave,
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

    private function columnasBloque(): array
    {
        return [
            'bloque_sig', 'bloque_seguridad', 'bloque_ambiente',
            'bloque_rrhh', 'bloque_abastecimiento', 'bloque_proyectos',
            'bloque_gerencia', 'bloque_patio', 'bloque_calidad',
            'bloque_docs_legales', 'bloque_formatos', 'bloque_listado_interes',
        ];
    }
}
