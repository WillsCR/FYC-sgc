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
            PermisoService::require('carga', 'carpeta', (int) $request->input('id_carpeta'));
        }

        $archivo = $request->file('archivo');

        // Validar MIME con magic bytes (finfo) — no confiar en el MIME declarado por el cliente
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($archivo->getRealPath());
        if (! $mimeType) {
            // Fallback conservador: rechazar si no se puede leer el tipo real
            return response()->json(['error' => 'No se pudo verificar el tipo de archivo.'], 422);
        }

        // Validar MIME
        if (!in_array($mimeType, self::MIMES_PERMITIDOS)) {
            return response()->json([
                'error' => 'Tipo de archivo no permitido. Permitidos: PDF, Word, Excel, PowerPoint, imágenes, ZIP, RAR, 7Z'
            ], 422);
        }

        try {
            // Leer contenido y generar hash SHA-256 (MD5 tiene colisiones conocidas)
            $contenido = file_get_contents($archivo->getRealPath());
            $hashSha256 = hash('sha256', $contenido);

            // Sanitizar nombre original: eliminar caracteres peligrosos para headers HTTP
            $nombreOriginal = preg_replace('/[\r\n\x00"\'\\\\]/', '', $archivo->getClientOriginalName());
            $nombreOriginal = substr($nombreOriginal, 0, 250); // límite seguro

            // Verificar si el documento ya existe (por hash)
            $documentoExistente = Documento::where('hash_md5', $hashSha256)->first();

            if ($documentoExistente) {
                $documento = $documentoExistente;
            } else {
                // UUID como nombre de archivo — sin relación con el nombre original
                $nombreArchivo = (string) Str::uuid() . '.' . $archivo->getClientOriginalExtension();

                Storage::disk('local')->put($nombreArchivo, $contenido);

                $documento = Documento::create([
                    'archivo'         => $nombreArchivo,
                    'nombre_original' => $nombreOriginal,
                    'tipo_mime'       => $mimeType,
                    'tamaño'          => $archivo->getSize(),
                    'hash_md5'        => $hashSha256,  // columna reutilizada para SHA-256
                    'creado_por'      => $usuario->id,
                    'creada_el'       => now(),
                ]);
            }

            // Crear relación en carpeta_contenido
            $existe = CarpetaContenido::where('id_carpeta', $request->input('id_carpeta'))
                ->where('id_documento', $documento->id)
                ->exists();

            if (! $existe) {
                CarpetaContenido::create([
                    'id_carpeta'  => $request->input('id_carpeta'),
                    'id_documento'=> $documento->id,
                    'descripcion' => $request->input('descripcion', $documento->nombre_original),
                    'metadata'    => json_encode(['cargado_por' => $usuario->id]),
                    'creada_el'   => now(),
                ]);
            }

            return response()->json([
                'ok'       => true,
                'mensaje'  => 'Archivo guardado correctamente',
                'documento'=> [
                    'id'     => $documento->id,
                    'nombre' => $documento->nombre_original,
                    'tamaño' => $documento->tamaño,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('ArchivoController::subir — ' . $e->getMessage(), [
                'usuario' => $usuario->id,
                'carpeta' => $request->input('id_carpeta'),
            ]);
            return response()->json(['error' => 'No se pudo procesar el archivo. Inténtalo nuevamente.'], 500);
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
            PermisoService::require('descarga', 'carpeta', (int) $contenido->id_carpeta);
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
            PermisoService::require('descarga', 'carpeta', (int) $contenido->id_carpeta);
        }

        $documento = $contenido->documento;
        $ruta = Storage::disk('local')->path($documento->archivo);

        // Solo PDF e imágenes se pueden visualizar
        if (!in_array($documento->tipo_mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            return response()->json(['error' => 'No se puede visualizar este tipo de archivo'], 403);
        }

        // Sanitizar nombre para evitar header injection (CRLF, comillas)
        $nombreSeguro = preg_replace('/[\r\n\x00"\'\\\\]/', '', $documento->nombre_original);

        return response()->file($ruta, [
            'Content-Type'        => $documento->tipo_mime,
            'Content-Disposition' => 'inline; filename="' . $nombreSeguro . '"',
        ]);
    }

    /**
     * Eliminar múltiples archivos en lote
     */
    public function eliminarLote(Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $ids = $request->input('ids', []);
        if (empty($ids) || ! is_array($ids)) {
            return response()->json(['error' => 'No se recibieron archivos para eliminar.'], 422);
        }

        $eliminados = 0;
        $errores    = [];

        foreach ($ids as $id) {
            $contenido = CarpetaContenido::find((int) $id);
            if (! $contenido) continue;

            if (! $esAdmin) {
                if (! PermisoService::can('eliminar', 'carpeta', (int) $contenido->id_carpeta)) {
                    $errores[] = $id;
                    continue;
                }
            }

            $documentoId = $contenido->id_documento;
            $contenido->delete();

            $tieneOtrasRefs = CarpetaContenido::where('id_documento', $documentoId)->exists();
            if (! $tieneOtrasRefs) {
                try {
                    $doc = \App\Models\Documento::find($documentoId);
                    if ($doc) {
                        Storage::disk('local')->delete($doc->archivo);
                        $doc->delete();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error eliminando archivo en lote: ' . $e->getMessage());
                }
            }

            $eliminados++;
        }

        if ($eliminados === 0) {
            return response()->json(['error' => 'No se pudo eliminar ningún archivo.'], 422);
        }

        $msg = $eliminados === 1
            ? '1 archivo eliminado correctamente.'
            : "{$eliminados} archivos eliminados correctamente.";

        return response()->json(['ok' => true, 'mensaje' => $msg, 'eliminados' => $eliminados]);
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
            PermisoService::require('eliminar', 'carpeta', (int) $contenido->id_carpeta);
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