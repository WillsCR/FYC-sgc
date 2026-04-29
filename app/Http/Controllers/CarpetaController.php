<?php

namespace App\Http\Controllers;

use App\Models\Carpeta;
use App\Models\CarpetaContenido;
use App\Models\Documento;
use App\Services\PermisoService;
use Illuminate\Http\Request;

class CarpetaController extends Controller
{
    /**
     * Muestra la carpeta raíz de un módulo
     */
    public function index($modulo)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        // Obtener la carpeta raíz del módulo
        $carpetaRaiz = Carpeta::where('id_padre', 0)
            ->where('descripcion', 'LIKE', '%' . ucfirst($modulo) . '%')
            ->first();

        if (!$carpetaRaiz) {
            abort(404, 'Módulo no encontrado');
        }

        return $this->mostrarCarpeta($carpetaRaiz, $modulo, $usuario, $esAdmin);
    }

    /**
     * Muestra una carpeta específica
     */
    public function show($modulo, $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $carpetaActual = Carpeta::findOrFail($id);

        return $this->mostrarCarpeta($carpetaActual, $modulo, $usuario, $esAdmin);
    }

    /**
     * Lógica compartida para mostrar una carpeta
     */
    private function mostrarCarpeta($carpeta, $modulo, $usuario, $esAdmin)
    {
        // Obtener todas las carpetas raíz para el menú
        $raices = Carpeta::where('id_padre', 0)
            ->orderBy('descripcion')
            ->get();

        // Obtener subcarpetas
        $subcarpetas = $carpeta->hijos()
            ->orderBy('descripcion')
            ->get();

        // Obtener contenido (documentos)
        $contenido = $this->obtenerContenidoCarpeta($carpeta->id);

        // Definir permisos del usuario
        $permisos = [
            'carga' => $esAdmin,
            'descarga' => $esAdmin,
            'crear' => $esAdmin,
            'eliminar' => $esAdmin,
        ];

        // Obtener breadcrumb si existe el método en el modelo
        $breadcrumb = method_exists($carpeta, 'obtenerBreadcrumb') 
            ? $carpeta->obtenerBreadcrumb() 
            : [];

        $carpetaActual = $carpeta;

        return view('carpetas.index', compact(
            'raices',
            'carpetaActual',
            'contenido',
            'subcarpetas',
            'usuario',
            'esAdmin',
            'modulo',
            'permisos',
            'breadcrumb'
        ));
    }

    /**
     * Obtiene el contenido (documentos) de una carpeta
     */
    private function obtenerContenidoCarpeta($carpetaId)
    {
        return CarpetaContenido::where('id_carpeta', $carpetaId)
            ->with('documento')
            ->orderBy('creada_el', 'desc')
            ->get();
    }

    /**
     * Crea una nueva carpeta
     */
    public function store(Request $request, $modulo, $id)
    {
        // Validar permisos
        $usuario = PermisoService::usuarioActual();
        if (!$usuario->esAdmin()) {
            return response()->json([
                'ok' => false,
                'error' => 'No tienes permisos para crear carpetas'
            ], 403);
        }

        // Validar datos
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255'
        ]);

        try {
            // Crear la nueva carpeta
            $carpeta = Carpeta::create([
                'id_padre' => $id,
                'descripcion' => $validated['descripcion'],
                'activa' => 1
            ]);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Carpeta creada exitosamente',
                'carpeta' => $carpeta
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Error al crear la carpeta: ' . $e->getMessage()
            ], 500);
        }
    }
}