<?php

namespace App\Http\Controllers;

use App\Models\Carpeta;
use App\Models\CarpetaContenido;
use App\Models\Documento;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArchivoController extends Controller
{
    private const MAX_SIZE_MB = 20;
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
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
    ];

    /**
     * Subir archivo a una carpeta
     */
    public function subir(Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $request->validate([
            'archivo' => [
                'required',
                'file',
                'max:' . (self::MAX_SIZE_MB * 1024),
            ],
            'id_carpeta' => ['required', 'integer', 'exists:sgc_carpetas3,id'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        // Verificar permisos
        if (!$esAdmin) {
            PermisoService::require('carga', 'archivo', $request->input('id_carpeta'));
        }

        $archivo = $request->file('archivo');
        $mimeType = $archivo->getMimeType();

        // Validar MIME
        if (!in_array($mimeType, self::MIMES_PERMITIDOS)) {
            return response()->json([
                'error' => 'Tipo de archivo no permitido. Permitidos: PDF, Word, Excel, PowerPoint, imágenes, ZIP, RAR, 7Z'
            ], 422);
        }

        try {
            // Leer contenido y generar hash MD5
            $contenido = file_get_contents($archivo->getRealPath());
            $hashMd5 = md5($contenido);

            // Verificar si el documento ya existe (por hash)
            $documentoExistente = Documento::where('hash_md5', $hashMd5)->first();

            if ($documentoExistente) {
                // Reutilizar documento existente
                $documento = $documentoExistente;
            } else {
                // Crear nuevo documento con UUID como nombre
                $nombreArchivo = (string) Str::uuid() . '.' . $archivo->getClientOriginalExtension();

                // Guardar en C:\xampp\documentos\
                Storage::disk('local')->put($nombreArchivo, $contenido);

                // Crear registro en BD
                $documento = Documento::create([
                    'archivo' => $nombreArchivo,
                    'nombre_original' => $archivo->getClientOriginalName(),
                    'tipo_mime' => $mimeType,
                    'tamaño' => $archivo->getSize(),
                    'hash_md5' => $hashMd5,
                    'creado_por' => $usuario->id,
                    'creada_el' => now(),
                ]);
            }

            // Crear relación en carpeta_contenido
            $existe = CarpetaContenido::where('id_carpeta', $request->input('id_carpeta'))
                ->where('id_documento', $documento->id)
                ->exists();

            if (!$existe) {
                CarpetaContenido::create([
                    'id_carpeta' => $request->input('id_carpeta'),
                    'id_documento' => $documento->id,
                    'descripcion' => $request->input('descripcion', $documento->nombre_original),
                    'metadata' => json_encode(['cargado_por' => $usuario->id]),
                    'creada_el' => now(),
                ]);
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Archivo guardado correctamente',
                'documento' => [
                    'id' => $documento->id,
                    'nombre' => $documento->nombre_original,
                    'tamaño' => $documento->tamaño,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar archivo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Descargar archivo
     */
    public function descargar($id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $contenido = CarpetaContenido::findOrFail($id);

        // Verificar permisos
        if (!$esAdmin) {
            PermisoService::require('descarga', 'archivo', $contenido->id_carpeta);
        }

        $documento = $contenido->documento;

        return Storage::disk('local')->download(
            $documento->archivo,
            $documento->nombre_original
        );
    }

    /**
     * Ver archivo en línea (preview)
     */
    public function ver($id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $contenido = CarpetaContenido::findOrFail($id);

        // Verificar permisos
        if (!$esAdmin) {
            PermisoService::require('descarga', 'archivo', $contenido->id_carpeta);
        }

        $documento = $contenido->documento;
        $ruta = Storage::disk('local')->path($documento->archivo);

        // Solo PDF e imágenes se pueden visualizar
        if (!in_array($documento->tipo_mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            return response()->json(['error' => 'No se puede visualizar este tipo de archivo'], 403);
        }

        return response()->file($ruta, [
            'Content-Type' => $documento->tipo_mime,
            'Content-Disposition' => 'inline; filename="' . $documento->nombre_original . '"'
        ]);
    }

    /**
     * Eliminar archivo (de la carpeta)
     */
    public function eliminar($id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $contenido = CarpetaContenido::findOrFail($id);

        // Verificar permisos
        if (!$esAdmin) {
            PermisoService::require('eliminar', 'archivo', $contenido->id_carpeta);
        }

        $documento = $contenido->documento;
        $documentoId = $documento->id;

        // Eliminar relación
        $contenido->delete();

        // Si no hay más referencias a este documento, eliminar el archivo
        $tieneOtrasReferencias = CarpetaContenido::where('id_documento', $documentoId)->exists();

        if (!$tieneOtrasReferencias) {
            try {
                Storage::disk('local')->delete($documento->archivo);
                $documento->delete();
            } catch (\Exception $e) {
                // Log error pero no falla
                \Log::error('Error eliminando archivo: ' . $e->getMessage());
            }
        }

        return response()->json(['ok' => true, 'mensaje' => 'Archivo eliminado']);
    }
}