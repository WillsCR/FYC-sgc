<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Session::has('usuario_id')) {
            return redirect()->route('panel');
        }
        return view('auth.login');
    }

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

        $this->migrarHashSiNecesario($request->clave, $usuario);

        Session::regenerate();

        Session::put([
            'usuario_id'     => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_email'  => $usuario->email,
            'usuario_perfil' => $usuario->id_perfil,
            'id_perfil'      => (int) $usuario->id_perfil,  // usado por PermisoService
            'es_admin'       => $usuario->esAdmin(),
            'es_superadmin'  => $usuario->esSuperAdmin(),
        ]);

        return redirect()->route('panel');
    }

    public function logout(Request $request)
    {
        Session::flush();
        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('login')
            ->with('mensaje', 'Sesión cerrada correctamente.');
    }

    private function verificarClave(string $clave, Usuario $usuario): bool
    {
        $hashBD = $usuario->quesera;

        if (str_starts_with($hashBD, '$2y$') || str_starts_with($hashBD, '$2a$')) {
            return password_verify($clave, $hashBD);
        }

        $conducta = DB::table('ser_conductas')->first();
        if ($conducta) {
            $promo = base64_decode($conducta->promocion);
            $hashLegacy = sha1(md5($promo . $clave));
            if (hash_equals($hashBD, $hashLegacy)) return true;
        }

        return hash_equals($hashBD, sha1(md5($clave)));
    }

    private function migrarHashSiNecesario(string $clave, Usuario $usuario): void
    {
        $hashBD = $usuario->quesera;
        if (! str_starts_with($hashBD, '$2y$') && ! str_starts_with($hashBD, '$2a$')) {
            $usuario->quesera = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
            $usuario->save();
        }
    }
}
