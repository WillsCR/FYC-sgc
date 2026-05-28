<?php

namespace App\Http\Controllers;

use App\Models\ArchivoEquipo;
use App\Services\ArchivoEquipoService;
use App\Services\PermisoService;
use Illuminate\Http\Request;

class ArchivoEquipoController extends Controller
{
    public function __construct(private ArchivoEquipoService $service) {}

    /** ID del usuario actual usando el sistema de auth propio de la app */
    private function userId(): int
    {
        return PermisoService::usuarioActual()?->id ?? (int) session('usuario_id', 1);
    }

    /**
     * Subir certificado (calidad o calibración)
     */
    public function subirCertificado(Request $request)
    {
        $request->validate([
            'archivo'       => 'required|file|max:10240',
            'id_programa'   => 'required|integer',
            'tipo'          => 'required|in:1,2', // 1=calidad, 2=calibracion
            'vigencia_hasta'=> 'nullable|date',
            'descripcion'   => 'nullable|string|max:255',
        ]);

        $tipo_documento = $request->tipo == 1 ? 'cert_calidad' : 'cert_calibracion';

        $resultado = $this->service->subirArchivo(
            $request->file('archivo'),
            $this->userId(),
            $tipo_documento,
            null,
            (int) $request->id_programa,
            null,
            $request->descripcion ?? '',
            $request->vigencia_hasta
        );

        if ($resultado['success']) {
            return response()->json([
                'success' => true,
                'mensaje' => 'Archivo subido correctamente',
                'id'      => $resultado['id'],
                'hash'    => $resultado['hash'],
            ]);
        }

        return response()->json([
            'success' => false,
            'mensaje' => $resultado['mensaje'],
        ], 400);
    }

    /**
     * Subir imagen general del equipo
     */
    public function subirImagen(Request $request)
    {
        $request->validate([
            'archivo'     => 'required|file|max:10240',
            'id_equipo'   => 'required|integer',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $resultado = $this->service->subirArchivo(
            $request->file('archivo'),
            $this->userId(),
            'imagen_general',
            $request->id_equipo,
            null,
            null,
            $request->descripcion ?? ''
        );

        if ($resultado['success']) {
            return response()->json([
                'success' => true,
                'mensaje' => 'Imagen subida correctamente',
                'id'      => $resultado['id'],
            ]);
        }

        return response()->json([
            'success' => false,
            'mensaje' => $resultado['mensaje'],
        ], 400);
    }

    /**
     * Descargar archivo (fuerza descarga)
     */
    public function descargar(ArchivoEquipo $archivo)
    {
        return $this->service->descargarArchivo($archivo->id, $this->userId());
    }

    /**
     * Ver archivo inline en el navegador (sin forzar descarga)
     */
    public function ver(ArchivoEquipo $archivo)
    {
        if ($archivo->estado !== 'activo') abort(404);

        // Usar Storage::disk para resolver la ruta real (Laravel 11 usa /private como raíz)
        if (! \Storage::disk('local')->exists($archivo->ruta_almacenamiento)) abort(404);

        $ruta = \Storage::disk('local')->path($archivo->ruta_almacenamiento);
        $mime = $archivo->archivo_mime ?: mime_content_type($ruta);

        return response()->file($ruta, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $archivo->archivo_nombre . '"',
        ]);
    }

    /**
     * Obtener archivos de un programa
     */
    public function archivosPrograma($id_programa)
    {
        $archivos = $this->service->obtenerArchivosPorPrograma($id_programa);
        return response()->json([
            'success' => true,
            'archivos' => $archivos
        ]);
    }

    /**
     * Obtener archivos de un equipo
     */
    public function archivosEquipo($id_equipo)
    {
        $archivos = $this->service->obtenerArchivosPorEquipo($id_equipo);
        return response()->json([
            'success' => true,
            'archivos' => $archivos
        ]);
    }

    /**
     * Ver historial de un archivo
     */
    public function historial($id_archivo)
    {
        $historial = $this->service->obtenerHistorial($id_archivo);
        return response()->json([
            'success' => true,
            'historial' => $historial
        ]);
    }

    /**
     * Listar todos los archivos con filtros
     */
    public function index(Request $request)
    {
        $query = ArchivoEquipo::where('estado', 'activo');

        if ($request->tipo_documento) {
            $query->where('tipo_documento', $request->tipo_documento);
        }

        if ($request->vigencia) {
            $hoy = now()->toDateString();
            if ($request->vigencia === 'vencido') {
                $query->where('vigencia_hasta', '<', $hoy);
            } elseif ($request->vigencia === 'proximo_a_vencer') {
                $query->whereBetween('vigencia_hasta', [$hoy, now()->addDays(30)->toDateString()]);
            } elseif ($request->vigencia === 'vigente') {
                $query->where('vigencia_hasta', '>=', now()->addDays(30)->toDateString());
            }
        }

        $archivos = $query->paginate(20);
        return response()->json($archivos);
    }
}
