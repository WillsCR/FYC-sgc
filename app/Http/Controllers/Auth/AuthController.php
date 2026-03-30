<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Muestra el formulario de login
     */
    public function showLogin()
    {
        if (Session::has('usuario_id')) {
            return redirect()->route('panel');
        }
        return view('auth.login');
    }

    /**
     * Procesa el login
     *
     * Lógica de migración de hash:
     *   1. Busca el usuario por email
     *   2. Si la clave en BD es sha1+md5 (legacy), la verifica y migra a bcrypt
     *   3. Si ya es bcrypt, verifica con password_verify
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'clave' => ['required', 'string', 'min:4'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email'    => 'Ingresa un correo válido.',
            'clave.required' => 'La contraseña es obligatoria.',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (! $usuario || ! $this->verificarClave($request->clave, $usuario)) {
            return back()
                ->withInput(['email' => $request->email])
                ->withErrors(['credenciales' => 'Correo o contraseña incorrectos.']);
        }

        // Migrar hash legacy a bcrypt en el primer login exitoso
        $this->migrarHashSiNecesario($request->clave, $usuario);

        // Regenerar ID de sesión para prevenir session fixation
        Session::regenerate();

        // Guardar datos mínimos en sesión — nunca la contraseña
        Session::put([
            'usuario_id'     => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_email'  => $usuario->email,
            'usuario_perfil' => $usuario->id_perfil,
            'es_admin'       => $usuario->esAdmin(),
        ]);

        return redirect()->route('panel');
    }

    /**
     * Cierra la sesión completamente
     */
    public function logout(Request $request)
    {
        Session::flush();
        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('login')
            ->with('mensaje', 'Sesión cerrada correctamente.');
    }

    // ─── Helpers privados ───────────────────────────────────────────────────

    /**
     * Verifica la contraseña soportando tanto hash legacy (sha1+md5)
     * como bcrypt moderno
     */
    private function verificarClave(string $clave, Usuario $usuario): bool
    {
        $hashBD = $usuario->quesera;

        // Si ya es bcrypt
        if (str_starts_with($hashBD, '$2y$') || str_starts_with($hashBD, '$2a$')) {
            return password_verify($clave, $hashBD);
        }

        // Hash legacy: sha1(md5(base64_decode(promocion) . clave))
        // El campo promocion viene de ser_conductas
        $conducta = \DB::table('ser_conductas')->first();
        if ($conducta) {
            $promo = base64_decode($conducta->promocion);
            $hashLegacy = sha1(md5($promo . $clave));
            if (hash_equals($hashBD, $hashLegacy)) {
                return true;
            }
        }

        // Fallback: sha1(md5(clave)) sin promo
        return hash_equals($hashBD, sha1(md5($clave)));
    }

    /**
     * Si el hash almacenado no es bcrypt, lo migra en el mismo login
     */
    private function migrarHashSiNecesario(string $clave, Usuario $usuario): void
    {
        $hashBD = $usuario->quesera;
        if (!str_starts_with($hashBD, '$2y$') && !str_starts_with($hashBD, '$2a$')) {
            $usuario->quesera = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
            $usuario->save();
        }
    }
}
