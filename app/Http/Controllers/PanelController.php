<?php

namespace App\Http\Controllers;

use App\Services\PermisoService;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    /**
     * Panel principal — carga el usuario y sus bloques visibles
     * La lógica de bloques se expande en el Sprint 2
     */
    public function index()
    {
        $usuario = PermisoService::usuarioActual();

        if (! $usuario) {
            return redirect()->route('login');
        }

        $bloques = $usuario->bloquesVisibles();

        return view('panel.index', compact('usuario', 'bloques'));
    }
}
