<?php

namespace App\Http\Controllers;

use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArchivoController extends Controller
{
    // Tipos MIME permitidos — validación real de contenido
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
        'text/plain',
        'application/zip',
        'application/x-zip-compressed',
    ];

    private const MAX_SIZE_MB = 20;

    /**
     * Subir archivo a una carpeta
     */
    public function subir(Request $request)
    {
        $carpetaId = (int) $request->input('carpeta_id');

        // Verificar permiso server-side
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            PermisoService::require('carga', 'carpeta', $carpetaId);
        }

        // Validación básica Laravel
        $request->validate([
            'archivo'    => ['required', 'file', 'max:' . (self::MAX_SIZE_MB * 1024)],
            'carpeta_id' => ['required', 'integer'],
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.max'      => 'El archivo no puede superar ' . self::MAX_SIZE_MB . ' MB.',
        ]);

        $file = $request->file('archivo');

        // Validación MIME real (no solo extensión)
        $mimeReal = $file->getMimeType();
        if (! in_array($mimeReal, self::MIMES_PERMITIDOS)) {
            return back()->withErrors([
                'archivo' => "Tipo de archivo no permitido ({$mimeReal}). Solo se permiten documentos, imágenes y archivos comprimidos."
            ])->withInput();
        }

        // Nombre UUID para almacenamiento — nunca el nombre original
        $extension   = $file->getClientOriginalExtension();
        $nombreDisco = Str::uuid() . '.' . $extension;
        $nombreVisible = $file->getClientOriginalName();

        // Guardar fuera del webroot en storage/app/documentos/
        $ruta = $file->storeAs('documentos', $nombreDisco, 'local');

        // Registrar en sgc_carpetas_contenido
        DB::table('sgc_carpetas_contenido')->insert([
            'id_carpeta'  => $carpetaId,
            'descripcion' => $nombreVisible,
            'archivo'     => $nombreDisco,
            'creada_el'   => now(),
        ]);

        return back()->with('ok', "Archivo \"{$nombreVisible}\" subido correctamente.");
    }

    /**
     * Resuelve la ruta absoluta de un archivo, sea nuevo (storage) o legacy (sgc/inc/).
     * Para archivos legacy usa sgc_carpetas.ruta para construir el path correcto.
     */
    private function resolverRuta(object $archivo): ?string
    {
        // Nuevo sistema: UUID en storage/app/documentos/
        if (Storage::disk('local')->exists('documentos/' . $archivo->archivo)) {
            return Storage::disk('local')->path('documentos/' . $archivo->archivo);
        }

        // Legacy: buscar la ruta de la carpeta en sgc_carpetas para construir el path completo
        $carpeta = DB::table('sgc_carpetas')->where('id', $archivo->id_carpeta)->first();
        if ($carpeta && $carpeta->ruta) {
            $ruta = 'C:/xampp/htdocs/sgc/inc/' . $carpeta->ruta . '/' . $archivo->archivo;
            if (file_exists($ruta)) {
                return $ruta;
            }

            // Algunos archivos están guardados en carpetas padre — subir niveles iterativamente
            $segmentos = explode('/', trim($carpeta->ruta, '/'));
            while (count($segmentos) > 0) {
                array_pop($segmentos);
                $rutaPadre = 'C:/xampp/htdocs/sgc/inc/' . implode('/', $segmentos) . '/' . $archivo->archivo;
                if (file_exists($rutaPadre)) {
                    return $rutaPadre;
                }
            }
        }

        // Fallback: nombre de archivo directo en inc/ (archivos sin subcarpeta)
        $rutaDirecta = 'C:/xampp/htdocs/sgc/inc/' . $archivo->archivo;
        if (file_exists($rutaDirecta)) {
            return $rutaDirecta;
        }

        return null;
    }

    /**
     * Descargar archivo — verifica permiso y sirve el archivo
     */
    public function descargar(int $id)
    {
        $archivo = DB::table('sgc_carpetas_contenido')->where('id', $id)->first();

        if (! $archivo) {
            abort(404, 'Archivo no encontrado.');
        }

        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            PermisoService::require('descarga', 'carpeta', $archivo->id_carpeta);
        }

        $rutaAbsoluta = $this->resolverRuta($archivo);
        if (! $rutaAbsoluta) {
            abort(404, 'El archivo físico no fue encontrado. Puede haber sido movido o eliminado del servidor.');
        }

        return response()->download($rutaAbsoluta, $archivo->descripcion);
    }

    /**
     * Previsualizar archivo inline en el navegador — verifica permiso
     */
    public function ver(int $id)
    {
        $archivo = DB::table('sgc_carpetas_contenido')->where('id', $id)->first();

        if (! $archivo) {
            abort(404, 'Archivo no encontrado.');
        }

        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            PermisoService::require('descarga', 'carpeta', $archivo->id_carpeta);
        }

        $rutaAbsoluta = $this->resolverRuta($archivo);
        if (! $rutaAbsoluta) {
            $nombre = e($archivo->descripcion);
            $descUrl = route('archivos.descargar', $archivo->id);
            return response(
                "<!DOCTYPE html><html><head><meta charset='UTF-8'>
                <style>body{margin:0;display:flex;align-items:center;justify-content:center;
                height:100vh;font-family:sans-serif;background:#525659;color:#fff;text-align:center}
                .box{padding:40px}.icon{font-size:3rem;margin-bottom:14px}
                p{margin:0 0 8px;font-size:.95rem}small{opacity:.65;font-size:.8rem}
                a{display:inline-block;margin-top:20px;padding:9px 20px;background:#fff;
                color:#0D2B5E;border-radius:6px;text-decoration:none;font-weight:600;font-size:.85rem}
                </style></head><body>
                <div class='box'>
                  <div class='icon'>📄</div>
                  <p>El archivo físico no está disponible en el servidor.</p>
                  <small>{$nombre}</small><br>
                  <a href='{$descUrl}'>⬇ Intentar descargar</a>
                </div></body></html>",
                200
            )->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return response()->file($rutaAbsoluta, [
            'Content-Disposition' => 'inline; filename="' . $archivo->descripcion . '"',
        ]);
    }

    /**
     * Eliminar archivo — verifica permiso, borra físico y registro en BD
     */
    public function eliminar(Request $request, int $id)
    {
        $archivo = DB::table('sgc_carpetas_contenido')->where('id', $id)->first();

        if (! $archivo) {
            abort(404, 'Archivo no encontrado.');
        }

        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            PermisoService::require('eliminar', 'carpeta', $archivo->id_carpeta);
        }

        $carpetaId = $archivo->id_carpeta;

        // Eliminar archivo físico del nuevo sistema si existe
        $rutaNuevo = 'documentos/' . $archivo->archivo;
        if (Storage::disk('local')->exists($rutaNuevo)) {
            Storage::disk('local')->delete($rutaNuevo);
        }

        // Eliminar registro en BD
        DB::table('sgc_carpetas_contenido')->where('id', $id)->delete();

        return redirect()
            ->route('carpetas.show', $carpetaId)
            ->with('ok', "Archivo \"{$archivo->descripcion}\" eliminado correctamente.");
    }
}
