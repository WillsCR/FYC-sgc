<?php

namespace App\Http\Controllers;

use App\Models\Publicacion;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicacionController extends Controller
{
    private const MAX_SIZE_MB = 50;

    private const MIMES_PERMITIDOS = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    // ─── Vistas ───────────────────────────────────────────────────────────────

    public function sig()
    {
        $usuario       = PermisoService::usuarioActual();
        $esAdmin       = $usuario->esAdmin();
        $publicaciones = Publicacion::where('seccion', 'sig')
            ->orderBy('creada_el', 'desc')
            ->get();

        return view('sig.index', compact('publicaciones', 'esAdmin'));
    }

    public function ambiente()
    {
        $usuario       = PermisoService::usuarioActual();
        $esAdmin       = $usuario->esAdmin();
        $publicaciones = Publicacion::where('seccion', 'ambiente')
            ->orderBy('creada_el', 'desc')
            ->get();

        return view('ambiente.index', compact('publicaciones', 'esAdmin'));
    }

    // ─── CRUD ─────────────────────────────────────────────────────────────────

    /**
     * Subir nueva publicación (Admin only)
     */
    public function store(Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            return response()->json(['error' => 'Sin permisos para publicar.'], 403);
        }

        $request->validate([
            'titulo'  => ['required', 'string', 'max:300'],
            'seccion' => ['required', 'in:sig,ambiente'],
            'archivo' => ['required', 'file', 'max:' . (self::MAX_SIZE_MB * 1024)],
        ], [
            'titulo.required'  => 'El título es obligatorio.',
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.max'      => 'El archivo no puede superar ' . self::MAX_SIZE_MB . ' MB.',
        ]);

        $archivo = $request->file('archivo');

        // Validar MIME con magic bytes (no confiar en el header HTTP)
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($archivo->getRealPath());

        if (! $mimeType || ! in_array($mimeType, self::MIMES_PERMITIDOS)) {
            return response()->json([
                'error' => 'Tipo de archivo no permitido. Permitidos: PDF, Word, Excel, PowerPoint, imágenes.'
            ], 422);
        }

        // Sanitizar nombre original
        $nombreOriginal = preg_replace('/[\r\n\x00"\'\\\\]/', '', $archivo->getClientOriginalName());
        $nombreOriginal = substr($nombreOriginal, 0, 250);

        // Guardar con UUID
        $nombreArchivo = (string) Str::uuid() . '.' . $archivo->getClientOriginalExtension();
        Storage::disk('local')->put($nombreArchivo, file_get_contents($archivo->getRealPath()));

        $pub = Publicacion::create([
            'seccion'         => $request->input('seccion'),
            'titulo'          => trim($request->input('titulo')),
            'archivo'         => $nombreArchivo,
            'nombre_original' => $nombreOriginal,
            'tipo_mime'       => $mimeType,
            'tamanio'         => $archivo->getSize(),
            'creado_por'      => $usuario->id,
            'creada_el'       => now(),
        ]);

        return response()->json([
            'ok'  => true,
            'pub' => [
                'id'              => $pub->id,
                'titulo'          => $pub->titulo,
                'nombre_original' => $pub->nombre_original,
                'tipo_mime'       => $pub->tipo_mime,
                'tamanio'         => $pub->tamanioFormateado(),
                'es_inline'       => $pub->esVisualizableEnLinea(),
                'url_ver'         => route('publicaciones.ver',       $pub->id),
                'url_descargar'   => route('publicaciones.descargar', $pub->id),
                'creada_el'       => $pub->creada_el->format('d/m/Y'),
            ],
        ]);
    }

    /**
     * Actualizar título (Admin only)
     */
    public function actualizar(Request $request, $id)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            return response()->json(['error' => 'Sin permisos.'], 403);
        }

        $request->validate(['titulo' => ['required', 'string', 'max:300']]);

        $pub         = Publicacion::findOrFail($id);
        $pub->titulo = trim($request->input('titulo'));
        $pub->save();

        return response()->json(['ok' => true, 'titulo' => $pub->titulo]);
    }

    /**
     * Ver en línea (todos los usuarios autenticados)
     */
    public function ver($id)
    {
        $pub  = Publicacion::findOrFail($id);
        $ruta = Storage::disk('local')->path($pub->archivo);

        $vizualizables = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (! in_array($pub->tipo_mime, $vizualizables)) {
            return response()->json(['error' => 'No se puede visualizar este tipo de archivo en línea.'], 403);
        }

        $nombreSeguro = preg_replace('/[\r\n\x00"\'\\\\]/', '', $pub->nombre_original);

        return response()->file($ruta, [
            'Content-Type'        => $pub->tipo_mime,
            'Content-Disposition' => 'inline; filename="' . $nombreSeguro . '"',
        ]);
    }

    /**
     * Descargar (todos los usuarios autenticados)
     */
    public function descargar($id)
    {
        $pub = Publicacion::findOrFail($id);
        return Storage::disk('local')->download($pub->archivo, $pub->nombre_original);
    }

    /**
     * Eliminar (Admin only)
     */
    public function eliminar($id)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            return response()->json(['error' => 'Sin permisos para eliminar.'], 403);
        }

        $pub = Publicacion::findOrFail($id);

        try {
            Storage::disk('local')->delete($pub->archivo);
            $pub->delete();
        } catch (\Exception $e) {
            \Log::error('PublicacionController::eliminar — ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['error' => 'No se pudo eliminar la publicación.'], 500);
        }

        return response()->json(['ok' => true, 'mensaje' => 'Publicación eliminada correctamente.']);
    }
}
