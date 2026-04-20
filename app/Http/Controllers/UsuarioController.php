<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\CarpetasPermisos;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * Lista de usuarios — filtrada según perfil del solicitante
     * SuperAdmin (1): ve todos
     * Admin (2):      ve solo Trabajadores (id_perfil=4)
     */
    public function index()
    {
        $actual  = PermisoService::usuarioActual();
        $perfil  = (int) $actual->id_perfil;

        $this->verificarAcceso($perfil);

        $query = Usuario::with('perfil')
            ->orderBy('nombre');

        // Admin solo ve trabajadores
        if ($perfil === 2) {
            $query->where('id_perfil', 4);
        }

        $usuarios = $query->get();
        $perfiles = $this->perfilesDisponibles($perfil);

        return view('usuarios.index', compact('usuarios', 'actual', 'perfiles'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $actual  = PermisoService::usuarioActual();
        $perfil  = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        $perfiles  = $this->perfilesDisponibles($perfil);
        $carpetas  = DB::table('sgc_carpetas')
            ->where('id_padre', 0)
            ->orderBy('descripcion')
            ->get();

        return view('usuarios.crear', compact('actual', 'perfiles', 'carpetas'));
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request)
    {
        $actual = PermisoService::usuarioActual();
        $perfil = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        // Validar que no asigne un perfil superior al suyo
        $perfilNuevo = (int) $request->id_perfil;
        $this->validarPerfilAsignable($perfil, $perfilNuevo);

        $request->validate([
            'nombre'    => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'unique:sgc_usuarios,email'],
            'id_perfil' => ['required', 'integer'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'email.required'     => 'El correo es obligatorio.',
            'email.unique'       => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Crear usuario con hash bcrypt
        $usuario = new Usuario();
        $usuario->nombre       = $request->nombre;
        $usuario->email        = $request->email;
        $usuario->id_perfil    = $perfilNuevo;
        $usuario->quesera      = password_hash($request->password, PASSWORD_BCRYPT, ['cost' => 12]);
        $usuario->fecha_ingreso = now()->toDateString();

        // Bloques por defecto según perfil
        if ($perfilNuevo === 1 || $perfilNuevo === 2) {
            // Admin y SuperAdmin: todos los bloques activos
            foreach ($this->columnasBloque() as $col) {
                $usuario->$col = 1;
            }
        }
        // Trabajador: sin bloques por defecto (se asignan manualmente)

        $usuario->save();

        // Asignar permisos de carpetas si se enviaron
        if ($request->has('carpetas')) {
            $this->guardarPermisosCarpetas($usuario->id, $request->email, $usuario->quesera, $request->carpetas);
        }

        return redirect()->route('usuarios.index')
            ->with('ok', "Usuario \"{$usuario->nombre}\" creado correctamente.");
    }

    /**
     * Formulario de edición / gestión de permisos
     */
    public function edit(int $id)
    {
        $actual  = PermisoService::usuarioActual();
        $perfil  = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        $usuario = Usuario::findOrFail($id);

        // Admin no puede editar SuperAdmins ni otros Admins
        if ($perfil === 2 && in_array((int) $usuario->id_perfil, [1, 2])) {
            abort(403, 'No tienes permiso para editar este usuario.');
        }

        $perfiles = $this->perfilesDisponibles($perfil);
        $carpetas = DB::table('sgc_carpetas')
            ->where('id_padre', 0)
            ->orderBy('descripcion')
            ->get();

        // Permisos actuales de carpetas
        $permisosCarpetas = CarpetasPermisos::where('id_usuario', $id)
            ->get()
            ->keyBy('id_carpeta');

        // Bloques actuales del usuario
        $bloques = [];
        foreach ($this->columnasBloque() as $col) {
            $bloques[$col] = (bool) $usuario->$col;
        }

        return view('usuarios.editar', compact(
            'usuario', 'actual', 'perfiles',
            'carpetas', 'permisosCarpetas', 'bloques'
        ));
    }

    /**
     * Guardar cambios de usuario + permisos
     */
    public function update(Request $request, int $id)
    {
        $actual  = PermisoService::usuarioActual();
        $perfil  = (int) $actual->id_perfil;
        $this->verificarAcceso($perfil);

        $usuario = Usuario::findOrFail($id);

        // Admin no puede editar SuperAdmins
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

        // Cambiar contraseña solo si se envió una nueva
        if ($request->filled('password')) {
            $usuario->quesera = password_hash($request->password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        // Actualizar bloques de módulos
        foreach ($this->columnasBloque() as $col) {
            $usuario->$col = $request->has("bloques.{$col}") ? 1 : 0;
        }

        $usuario->save();

        // Actualizar permisos de carpetas
        CarpetasPermisos::where('id_usuario', $id)->delete();
        if ($request->has('carpetas')) {
            $this->guardarPermisosCarpetas($id, $usuario->email, $usuario->quesera, $request->carpetas);
        }

        return redirect()->route('usuarios.index')
            ->with('ok', "Usuario \"{$usuario->nombre}\" actualizado correctamente.");
    }

    /**
     * Desactivar usuario (no se elimina de la BD)
     */
    public function destroy(int $id)
    {
        $actual  = PermisoService::usuarioActual();
        $perfil  = (int) $actual->id_perfil;

        // Solo SuperAdmin puede desactivar
        if ($perfil !== 1) {
            abort(403, 'Solo el Super Administrador puede desactivar usuarios.');
        }

        // No puede desactivarse a sí mismo
        if ($id === $actual->id) {
            return back()->withErrors(['error' => 'No puedes desactivar tu propio usuario.']);
        }

        $usuario = Usuario::findOrFail($id);

        // No puede desactivar otros SuperAdmins
        if ((int) $usuario->id_perfil === 1 && $id !== $actual->id) {
            return back()->withErrors(['error' => 'No puedes desactivar a otro Super Administrador.']);
        }

        // Desactivar todos los bloques
        foreach ($this->columnasBloque() as $col) {
            $usuario->$col = 0;
        }
        $usuario->save();

        return redirect()->route('usuarios.index')
            ->with('ok', "Usuario \"{$usuario->nombre}\" desactivado.");
    }

    // ─── Helpers privados ────────────────────────────────────────────────────

    /**
     * Verifica que el usuario actual pueda acceder a gestión de usuarios
     */
    private function verificarAcceso(int $perfil): void
    {
        if (! in_array($perfil, [1, 2])) {
            abort(403, 'No tienes permiso para gestionar usuarios.');
        }
    }

    /**
     * Retorna los perfiles que puede asignar según el perfil del solicitante
     */
    private function perfilesDisponibles(int $perfil): \Illuminate\Support\Collection
    {
        if ($perfil === 1) {
            // SuperAdmin puede crear cualquier perfil
            return DB::table('ser_perfiles')
                ->where('estado', 1)
                ->orderBy('id_perfil')
                ->get();
        }

        // Admin solo puede crear Trabajadores (id_perfil=4)
        return DB::table('ser_perfiles')
            ->where('id_perfil', 4)
            ->get();
    }

    /**
     * Valida que no se asigne un perfil igual o superior al del solicitante
     */
    private function validarPerfilAsignable(int $perfilActual, int $perfilNuevo): void
    {
        if ($perfilActual === 2 && $perfilNuevo !== 4) {
            abort(403, 'Los administradores solo pueden crear usuarios con perfil Trabajador.');
        }
    }

    /**
     * Guarda los permisos de carpetas para un usuario
     * $carpetas = ['5' => ['carga'=>'1','descarga'=>'1',...], ...]
     */
    private function guardarPermisosCarpetas(int $usuarioId, string $correo, string $clave, array $carpetas): void
    {
        foreach ($carpetas as $carpetaId => $perms) {
            // Solo guardar si tiene al menos un permiso activo
            $tieneAlguno = collect(['carga','descarga','crear','eliminar','editar'])
                ->some(fn($p) => ! empty($perms[$p]));

            if (! $tieneAlguno) continue;

            CarpetasPermisos::updateOrCreate(
                ['id_carpeta' => $carpetaId, 'id_usuario' => $usuarioId],
                [
                    'correo'     => $correo,
                    'clave'      => $clave,
                    'carga'      => ! empty($perms['carga'])    ? 1 : 0,
                    'descarga'   => ! empty($perms['descarga']) ? 1 : 0,
                    'crear'      => ! empty($perms['crear'])    ? 1 : 0,
                    'ocultar_raiz' => 0,
                    'eliminar'   => ! empty($perms['eliminar']) ? 1 : 0,
                    'editar'     => ! empty($perms['editar'])   ? 1 : 0,
                ]
            );
        }
    }

    /**
     * Columnas de bloque en sgc_usuarios
     */
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
