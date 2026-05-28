<?php

namespace App\Services;

use App\Models\ArchivoEquipo;
use App\Models\ArchivoEquipoAuditoria;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ArchivoEquipoService
{
    const MAX_SIZE = 10485760; // 10 MB
    const DISCO = 'archivos_equipos';
    
    private array $config = [
        'max_size' => 10485760, // 10 MB
        'tipos_permitidos' => [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ]
    ];

    private array $directorios = [
        'imagen_general' => 'verificacion_y_control',
        'cert_calidad' => 'cert_calidad_equipos',
        'cert_calibracion' => 'cert_calibrac_equipos',
        'manual' => 'manuales_equipos',
        'protocolo' => 'protocolos_equipos',
        'inspecciones' => 'inspecciones_equipos',
        'mantenimiento' => 'mantenimiento_equipos',
    ];

    public function subirArchivo(
        $file,
        $id_usuario,
        $tipo_documento,
        $id_equipo_generico = null,
        $id_programa_verificacion = null,
        $id_equipo_interno = null,
        $descripcion = null,
        $vigencia_hasta = null
    ) {
        try {
            // Validación
            $validacion = $this->validarArchivo($file, $tipo_documento);
            if (!$validacion['success']) {
                return $validacion;
            }

            // Hash
            $archivo_hash = hash_file('sha256', $file->getPathname());

            // Verificar duplicado
            $existe = ArchivoEquipo::where('archivo_hash', $archivo_hash)
                ->where('estado', 'activo')
                ->first();
            
            if ($existe) {
                return [
                    'success' => false,
                    'mensaje' => 'El archivo ya existe en el sistema',
                    'id' => $existe->id
                ];
            }

            // Información del archivo
            $extension = strtolower($file->getClientOriginalExtension());
            $mime = $this->config['tipos_permitidos'][$extension] ?? 'application/octet-stream';
            $tamanio = $file->getSize();

            // Guardar en storage
            $nombre_guardado = $archivo_hash . '.' . $extension;
            $directorio = $this->directorios[$tipo_documento] ?? 'otros';
            $path = Storage::disk('local')->putFileAs(
                'nc_docs/' . $directorio,
                $file,
                $nombre_guardado
            );

            // Crear registro en BD
            $archivo = ArchivoEquipo::create([
                'archivo_hash' => $archivo_hash,
                'archivo_nombre' => $file->getClientOriginalName(),
                'archivo_extension' => $extension,
                'archivo_mime' => $mime,
                'archivo_tamanio' => $tamanio,
                'ruta_tipo' => 'hashed',
                'ruta_almacenamiento' => $path,
                'id_equipo_generico' => $id_equipo_generico,
                'id_programa_verificacion' => $id_programa_verificacion,
                'id_equipo_interno' => $id_equipo_interno,
                'tipo_documento' => $tipo_documento,
                'id_usuario' => $id_usuario,
                'descripcion' => $descripcion,
                'vigencia_hasta' => $vigencia_hasta,
                'estado' => 'activo',
            ]);

            // Registrar auditoria
            ArchivoEquipoAuditoria::create([
                'id_archivo' => $archivo->id,
                'accion' => 'SUBIDA',
                'usuario_id' => $id_usuario,
                'detalles' => [
                    'nombre_original' => $file->getClientOriginalName(),
                    'tamanio' => $tamanio,
                    'tipo' => $tipo_documento,
                ]
            ]);

            return [
                'success' => true,
                'id' => $archivo->id,
                'hash' => $archivo_hash,
                'mensaje' => 'Archivo subido correctamente'
            ];
        } catch (\Exception $e) {
            Log::error('Error al subir archivo: ' . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error al procesar el archivo'
            ];
        }
    }

    public function obtenerArchivosPorPrograma($id_programa, $tipo_documento = null)
    {
        $query = ArchivoEquipo::where('id_programa_verificacion', $id_programa)
            ->where('estado', 'activo');

        if ($tipo_documento) {
            $query->where('tipo_documento', $tipo_documento);
        }

        return $query->orderBy('fecha_subida', 'desc')->get();
    }

    public function obtenerArchivosPorEquipo($id_equipo, $tipo_documento = null)
    {
        $query = ArchivoEquipo::where('id_equipo_generico', $id_equipo)
            ->where('estado', 'activo');

        if ($tipo_documento) {
            $query->where('tipo_documento', $tipo_documento);
        }

        return $query->orderBy('fecha_subida', 'desc')->get();
    }

    public function descargarArchivo($id_archivo, $id_usuario = null)
    {
        try {
            $archivo = ArchivoEquipo::findOrFail($id_archivo);

            if ($archivo->estado !== 'activo') {
                abort(404, 'Archivo no disponible');
            }

            // Registrar auditoria
            if ($id_usuario) {
                ArchivoEquipoAuditoria::create([
                    'id_archivo' => $id_archivo,
                    'accion' => 'DESCARGA',
                    'usuario_id' => $id_usuario,
                ]);
            }

            // Storage::disk resuelve la ruta real incluyendo /private en Laravel 11
            if (! Storage::disk('local')->exists($archivo->ruta_almacenamiento)) {
                abort(404, 'Archivo no encontrado en disco');
            }
            return Storage::disk('local')->download($archivo->ruta_almacenamiento, $archivo->archivo_nombre);
        } catch (\Exception $e) {
            Log::error('Error al descargar archivo: ' . $e->getMessage());
            abort(404, 'Archivo no encontrado');
        }
    }

    public function obtenerHistorial($id_archivo)
    {
        return ArchivoEquipoAuditoria::where('id_archivo', $id_archivo)
            ->orderBy('fecha', 'desc')
            ->get();
    }

    private function validarArchivo($file, $tipo_documento)
    {
        if (!$file->isValid()) {
            return ['success' => false, 'mensaje' => 'Error en la subida'];
        }

        if ($file->getSize() > $this->config['max_size']) {
            return ['success' => false, 'mensaje' => 'El archivo excede 10 MB'];
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!isset($this->config['tipos_permitidos'][$extension])) {
            return ['success' => false, 'mensaje' => 'Tipo de archivo no permitido. Solo: PDF, JPG, PNG'];
        }

        if (!isset($this->directorios[$tipo_documento]) && $tipo_documento !== 'otro') {
            return ['success' => false, 'mensaje' => 'Tipo de documento inválido'];
        }

        return ['success' => true];
    }
}
