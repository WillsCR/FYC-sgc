<?php

namespace App\Http\Controllers;

use App\Models\ProgramaVerificacion;
use App\Models\ArchivoEquipo;
use App\Models\Area;
use App\Models\Usuario;
use App\Services\ArchivoEquipoService;
use App\Helpers\ControlInstrumentosHelper;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class ControlInstrumentosController extends Controller
{
    private $archivoService;

    public function __construct(ArchivoEquipoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    /**
     * Mostrar listado de programas
     */
    public function index(Request $request)
    {
        $query = ProgramaVerificacion::with(['area', 'responsables.usuario', 'archivos']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhere('serie', 'like', "%{$search}%");
            });
        }

        $programas = $query->orderBy('id', 'desc')->paginate(20);
        $areas = Area::orderBy('descripcion')->get();
        $usuarios = Usuario::orderBy('email')->get();

        return view('control-instrumentos.index-nuevo', compact('programas', 'areas', 'usuarios'));
    }

    /**
     * Importar datos desde Excel
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('archivo_excel');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();

            $borrarExistentes = $request->has('borrar_existentes');
            $importados = 0;

            // Opcional: borrar existentes
            if ($borrarExistentes) {
                ProgramaVerificacion::truncate();
                ArchivoEquipo::truncate();
            }

            // Procesar filas (comenzar desde fila 2 para saltar encabezado)
            foreach ($worksheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $datos = [];
                $col = 0;
                foreach ($cellIterator as $cell) {
                    $datos[$col] = $cell->getValue();
                    $col++;
                }

                // Validar que no esté vacía
                if (!isset($datos[0]) || empty($datos[0])) {
                    continue;
                }

                // Mapear columnas según el Excel
                $programa = $this->procesarFilaExcel($datos);
                if ($programa) {
                    $importados++;
                }
            }

            return response()->json([
                'success' => true,
                'importados' => $importados,
                'mensaje' => "Se importaron $importados registros correctamente"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Procesar una fila del Excel
     */
    private function procesarFilaExcel($datos)
    {
        try {
            // Mapear índices de columnas
            $numero = $datos[0] ?? null;
            $proyecto = $datos[1] ?? null;
            $certificado = $datos[2] ?? null;
            $descripcion = $datos[3] ?? null;
            $marca = $datos[4] ?? null;
            $modelo = $datos[5] ?? null;
            $serie = $datos[6] ?? null;
            $interno = $datos[7] ?? null;
            $calibracion = $datos[8] ?? null;
            $verificacion = $datos[9] ?? null;
            $numCert = $datos[10] ?? null;
            $ultima = $datos[11] ?? null;
            $proxima = $datos[12] ?? null;
            $responsable = $datos[13] ?? null;

            if (!$descripcion || !$proyecto) {
                return null;
            }

            // Obtener ID de área por nombre
            $area = Area::where('descripcion', 'like', "%{$proyecto}%")->first();
            $idArea = $area?->id ?? Area::first()?->id;

            // Convertir frecuencias
            $calibracionId = $this->obtenerFreqId($calibracion);
            $verificacionId = $this->obtenerFreqId($verificacion, true);

            // Determinar tipos de certificado
            $certCalidad = Str::contains($certificado, ['Calidad', 'calidad']);
            $certCalibra = Str::contains($certificado, ['Calibración', 'calibracion', 'Calibracion']);

            // Convertir fechas
            $fechaUltima = $this->convertirFecha($ultima);
            $fechaProxima = $this->convertirFecha($proxima);

            // Crear programa de verificación
            $programa = ProgramaVerificacion::create([
                'id_area' => $idArea,
                'descripcion' => $descripcion,
                'marca' => $marca,
                'modelo' => $modelo,
                'serie' => $serie,
                'interno' => $interno,
                'certificado_calidad' => $certCalidad ? 1 : 0,
                'certificado_calibracion' => $certCalibra ? 1 : 0,
                'calibracion' => $calibracionId,
                'verificacion' => $verificacionId,
                'numero_certificado' => $numCert,
                'fecha_ultima' => $fechaUltima,
                'fecha_proxima' => $fechaProxima,
                'estado' => 'activo',
            ]);

            return $programa;

        } catch (\Exception $e) {
            \Log::error('Error procesando fila Excel: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener ID de frecuencia
     */
    private function obtenerFreqId($valor, $esVerificacion = false)
    {
        if ($esVerificacion) {
            if (Str::contains($valor, ['Mensual', 'mensual'])) return 1;
            return null;
        }

        if (Str::contains($valor, ['Anual', 'anual'])) return 1;
        if (Str::contains($valor, ['Mensual', 'mensual'])) return 2;
        if (Str::contains($valor, ['No aplica', 'no aplica'])) return 3;
        if (Str::contains($valor, ['Semestral', 'semestral'])) return 4;

        return null;
    }

    /**
     * Convertir formato de fecha
     */
    private function convertirFecha($fecha)
    {
        if (!$fecha) return null;

        try {
            // Intenta varios formatos comunes
            $formatos = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'd-m-y'];

            foreach ($formatos as $formato) {
                $fecha_obj = \DateTime::createFromFormat($formato, $fecha);
                if ($fecha_obj) {
                    return $fecha_obj->format('Y-m-d');
                }
            }

            // Si es un número de Excel
            if (is_numeric($fecha)) {
                return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha))->format('Y-m-d');
            }
        } catch (\Exception $e) {
            \Log::error('Error convertiendo fecha: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Obtener formulario de edición
     */
    public function edit(ProgramaVerificacion $programaVerificacion)
    {
        $areas = Area::orderBy('descripcion')->get();
        $programa = $programaVerificacion;

        return view('control-instrumentos.partials.formulario-edicion', compact('programa', 'areas'));
    }

    /**
     * Actualizar programa
     */
    public function update(Request $request, ProgramaVerificacion $programaVerificacion)
    {
        $request->validate([
            'descripcion' => 'required|string',
            'calibracion' => 'required|in:1,2,3,4',
        ]);

        $programaVerificacion->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Programa actualizado correctamente'
        ]);
    }

    /**
     * Obtener archivos asociados
     */
    public function archivos(ProgramaVerificacion $programaVerificacion)
    {
        $programa = $programaVerificacion;
        $areas = Area::all();

        return view('control-instrumentos.partials.gestionar-archivos', compact('programa', 'areas'));
    }

    /**
     * Actualizar permisos de usuarios
     */
    public function actualizarPermisos(Request $request)
    {
        $usuario = Usuario::findOrFail($request->usuario_id);
        $tipo = $request->tipo;
        $estado = $request->estado;

        $campoPermiso = match ($tipo) {
            'ver' => 'ver_control_instrumentos',
            'editar' => 'editar_control_instrumentos',
            'eliminar' => 'eliminar_control_instrumentos',
            default => null
        };

        if ($campoPermiso) {
            $usuario->update([$campoPermiso => $estado]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Eliminar programa
     */
    public function destroy(ProgramaVerificacion $programaVerificacion)
    {
        $programaVerificacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Programa eliminado correctamente'
        ]);
    }
}
