<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    private const MAX_SIZE_MB  = 500;
    private const MIMES_PERMITIDOS = [
        'video/mp4',
        'video/webm',
        'video/ogg',
        'video/quicktime',
        'video/x-msvideo',  // .avi
        'video/x-matroska', // .mkv
    ];

    public function index()
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();
        $videos  = Video::orderBy('creada_el', 'desc')->get();

        return view('videos.index', compact('videos', 'esAdmin'));
    }

    public function store(Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden subir videos.'], 403);
        }

        // Detectar cuando PHP descartó el archivo por exceder upload_max_filesize o post_max_size.
        // En ese caso $_FILES llega vacío pero Content-Length indica el tamaño real enviado.
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $postMaxBytes  = $this->parseIniBytes(ini_get('post_max_size'));
        $uploadMaxBytes= $this->parseIniBytes(ini_get('upload_max_filesize'));
        $limiteMinimo  = min($postMaxBytes, $uploadMaxBytes);

        if ($contentLength > 0 && ! $request->hasFile('video') && $contentLength > $limiteMinimo) {
            $limiteMB = round($limiteMinimo / 1024 / 1024);
            return response()->json([
                'error' => "El servidor rechazó el archivo porque supera el límite de PHP ({$limiteMB} MB). "
                         . 'Pide al administrador del servidor que ejecute: php artisan sgc:setup',
            ], 413);
        }

        $request->validate([
            'titulo' => ['required', 'string', 'max:300'],
            'video'  => [
                'required',
                'file',
                'max:' . (self::MAX_SIZE_MB * 1024),
            ],
        ], [
            'titulo.required' => 'El título es obligatorio.',
            'video.required'  => 'Debes seleccionar un archivo de video.',
            'video.max'       => 'El video no puede superar ' . self::MAX_SIZE_MB . ' MB.',
        ]);

        $archivo = $request->file('video');

        // Validar MIME real con magic bytes
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($archivo->getRealPath());
        if (! in_array($mimeReal, self::MIMES_PERMITIDOS, true)) {
            return response()->json(['error' => 'Tipo de archivo no permitido. Solo se aceptan videos (MP4, WebM, OGG, MOV, AVI, MKV).'], 422);
        }

        set_time_limit(0);

        $extension  = strtolower($archivo->getClientOriginalExtension()) ?: 'mp4';
        $nombreUuid = Str::uuid() . '.' . $extension;

        Storage::disk('local')->putFileAs('videos', $archivo, $nombreUuid);

        $video = Video::create([
            'titulo'         => $request->input('titulo'),
            'archivo'        => $nombreUuid,
            'nombre_original'=> $archivo->getClientOriginalName(),
            'tipo_mime'      => $mimeReal,
            'tamanio'        => $archivo->getSize(),
            'creado_por'     => $usuario->id,
            'creada_el'      => now(),
        ]);

        return response()->json([
            'ok'    => true,
            'video' => [
                'id'              => $video->id,
                'titulo'          => $video->titulo,
                'nombre_original' => $video->nombre_original,
                'tamanio'         => $video->tamanioFormateado(),
                'creada_el'       => $video->creada_el->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Transmite el video al navegador con soporte de Range (seeking).
     */
    public function stream(int $id)
    {
        $video = Video::findOrFail($id);
        $path  = Storage::disk('local')->path('videos/' . $video->archivo);

        if (! file_exists($path)) {
            abort(404);
        }

        $size   = filesize($path);
        $start  = 0;
        $end    = $size - 1;
        $length = $size;
        $status = 200;

        $headers = [
            'Content-Type'              => $video->tipo_mime,
            'Accept-Ranges'             => 'bytes',
            'Content-Length'            => $size,
            'Cache-Control'             => 'no-cache',
        ];

        if (request()->hasHeader('Range')) {
            [$unit, $range] = explode('=', request()->header('Range'), 2);
            [$start, $reqEnd] = explode('-', $range, 2);

            $start  = (int) $start;
            $end    = $reqEnd !== '' ? (int) $reqEnd : $size - 1;
            $end    = min($end, $size - 1);
            $length = $end - $start + 1;
            $status = 206;

            $headers['Content-Range']  = "bytes {$start}-{$end}/{$size}";
            $headers['Content-Length'] = $length;
        }

        return response()->stream(function () use ($path, $start, $length) {
            $handle = fopen($path, 'rb');
            fseek($handle, $start);

            $remaining = $length;
            $chunk     = 1024 * 1024; // 1 MB chunks

            while (! feof($handle) && $remaining > 0) {
                $toRead = min($chunk, $remaining);
                echo fread($handle, $toRead);
                $remaining -= $toRead;
                flush();
            }

            fclose($handle);
        }, $status, $headers);
    }

    public function descargar(int $id)
    {
        $video = Video::findOrFail($id);
        $path  = Storage::disk('local')->path('videos/' . $video->archivo);

        if (! file_exists($path)) {
            abort(404);
        }

        $nombreDescarga = preg_replace('/[^\w\.\-]/', '_', $video->nombre_original);

        return response()->download($path, $nombreDescarga);
    }

    private function parseIniBytes(string $val): int
    {
        $val  = trim($val);
        $last = strtoupper(substr($val, -1));
        $num  = (int) $val;
        return match ($last) {
            'G'     => $num * 1024 * 1024 * 1024,
            'M'     => $num * 1024 * 1024,
            'K'     => $num * 1024,
            default => $num,
        };
    }

    public function eliminar(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) {
            return response()->json(['error' => 'Solo los administradores pueden eliminar videos.'], 403);
        }

        $video = Video::findOrFail($id);

        try {
            Storage::disk('local')->delete('videos/' . $video->archivo);
        } catch (\Exception $e) {
            \Log::warning('No se pudo borrar archivo de video: ' . $e->getMessage());
        }

        $video->delete();

        return response()->json(['ok' => true, 'mensaje' => 'Video eliminado correctamente.']);
    }
}
