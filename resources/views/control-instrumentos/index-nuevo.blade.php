@extends('layouts.app')

@section('title', 'Control de Instrumentos')

@section('content')
<div class="container-fluid mt-4">
    {{-- Header --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <h3><i class="fa-solid fa-gauge"></i> CONTROL DE INSTRUMENTOS</h3>
        </div>
        <div class="col-md-6 text-right">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#importarExcelModal">
                <i class="fa-solid fa-upload"></i> Importar Excel
            </button>
            @can('create', App\Models\ProgramaVerificacion::class)
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#nuevoProgramaModal">
                    <i class="fa-solid fa-plus"></i> Nuevo
                </button>
            @endcan
            @if(auth()->user()->sstipo == 2 || auth()->user()->sstipo == 1)
                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#permisosModal">
                    <i class="fa-solid fa-lock"></i> Permisos
                </button>
            @endif
            <button onclick="togglePantallaCompleta()" class="btn btn-primary">
                <i class="fa-solid fa-expand"></i>
            </button>
        </div>
    </div>

    {{-- Barra de búsqueda --}}
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" id="bus_file" class="form-control" placeholder="Buscar por descripción, marca, modelo...">
                <div class="input-group-append">
                    <button class="btn btn-success" type="button" onclick="buscarProgramas()">
                        <i class="fa-solid fa-search"></i>
                    </button>
                    <button class="btn btn-secondary" type="button" onclick="limpiarBusqueda()">
                        <i class="fa-solid fa-refresh"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla responsiva --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm" id="tablaProgramas">
            <thead style="background-color: #2d6181; color: white; font-size: 11px;">
                <tr>
                    <th colspan="8" class="text-center">CARACTERÍSTICAS DEL EQUIPO</th>
                    <th colspan="2" class="text-center">FRECUENCIA</th>
                    <th colspan="4" class="text-center">CALIBRACIÓN</th>
                    <th class="text-center">ESTADO</th>
                    <th class="text-center">RESPONSABLE</th>
                    <th class="text-center">ACCIONES</th>
                </tr>
                <tr style="background-color: #2d6181; color: white; font-size: 11px;">
                    <th style="width: 40px;">N°</th>
                    <th style="width: 100px;">PROYECTO</th>
                    <th style="width: 120px;">CERTIFICADO</th>
                    <th>DESCRIPCIÓN</th>
                    <th>MARCA</th>
                    <th>MODELO</th>
                    <th>SERIE</th>
                    <th>Nº INTERNO</th>
                    <th style="width: 80px;">CALIB.</th>
                    <th style="width: 80px;">VERIF.</th>
                    <th style="width: 100px;">Nº CERT</th>
                    <th style="width: 90px;">ÚLTIMA</th>
                    <th style="width: 90px;">PRÓXIMA</th>
                    <th style="width: 40px;">⚠️</th>
                    <th>RESPONSABLE</th>
                    <th style="width: 80px;">ACCIONES</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla" style="font-size: 11px;">
                @forelse($programas as $programa)
                    <tr class="programa-row" id="programa-{{ $programa->id }}" data-id="{{ $programa->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span class="badge badge-info">{{ $programa->area->descripcion ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @if($programa->cert_calidad)
                                    <span class="badge badge-success" title="Certificado de Calidad">C</span>
                                @endif
                                @if($programa->cert_calibracion)
                                    <span class="badge badge-warning" title="Certificado de Calibración">B</span>
                                @endif
                                @if(!$programa->cert_calidad && !$programa->cert_calibracion)
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>
                        <td><strong>{{ $programa->descripcion }}</strong></td>
                        <td>{{ $programa->marca ?? '-' }}</td>
                        <td>{{ $programa->modelo ?? '-' }}</td>
                        <td>{{ $programa->serie ?? '-' }}</td>
                        <td>{{ $programa->interno ?? '-' }}</td>
                        <td>{{ obtenerFreqCalib($programa->calibracion) }}</td>
                        <td>{{ obtenerFreqVerif($programa->verificacion) }}</td>
                        <td>{{ $programa->num_cert_calibracion ?? '-' }}</td>
                        <td>
                            @if($programa->fecha_ultima)
                                {{ \Carbon\Carbon::parse($programa->fecha_ultima)->format('d-m-Y') }}
                            @else
                                <span class="text-danger">-</span>
                            @endif
                        </td>
                        <td>
                            @if($programa->fecha_proxima)
                                <span class="{{ obtenerClaseVigencia($programa->fecha_proxima) }}">
                                    {{ \Carbon\Carbon::parse($programa->fecha_proxima)->format('d-m-Y') }}
                                </span>
                            @else
                                <span class="text-danger">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="semaforo" style="
                                border-radius: 50%;
                                width: 24px;
                                height: 24px;
                                margin: 0 auto;
                                border: 2px solid #ccc;
                                background-color: {{ obtenerColorSemaforo($programa->fecha_proxima) }};
                            "></div>
                        </td>
                        <td>
                            @forelse($programa->responsables as $resp)
                                <span class="badge badge-primary">{{ $resp->usuario->nombre ?? 'N/A' }}</span>
                            @empty
                                <span class="text-muted text-danger">Sin asignar</span>
                            @endforelse
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                @can('update', $programa)
                                    <button class="btn btn-warning" onclick="editarPrograma({{ $programa->id }})" title="Editar">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                @endcan
                                <button class="btn btn-info" onclick="verArchivos({{ $programa->id }})" title="Archivos">
                                    <i class="fa-solid fa-file"></i>
                                </button>
                                @can('delete', $programa)
                                    <button class="btn btn-danger" onclick="confirmarEliminar({{ $programa->id }})" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="17" class="text-center text-muted py-4">
                            <i class="fa-solid fa-inbox" style="font-size: 2rem;"></i><br>
                            No hay programas de verificación registrados. 
                            <a href="#" data-toggle="modal" data-target="#importarExcelModal">Importa un archivo Excel</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="row mt-3">
        <div class="col-md-12">
            {{ $programas->links() }}
        </div>
    </div>
</div>

{{-- MODALES --}}

{{-- Modal: Importar Excel --}}
@include('control-instrumentos.modales.importar-excel')

{{-- Modal: Editar Programa --}}
@include('control-instrumentos.modales.editar-inline')

{{-- Modal: Ver/Subir Archivos --}}
@include('control-instrumentos.modales.archivos')

{{-- Modal: Permisos --}}
@include('control-instrumentos.modales.permisos')

<script>
    function togglePantallaCompleta() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }

    function editarPrograma(id) {
    $.ajax({
        url: `/control-instrumentos/${id}/edit`,
        type: 'GET',
        success: function(html) {
            $('#modalEditarProgramaBody').html(html);
            $('#modalEditarPrograma').modal('show');
        },
        error: function() {
            Swal.fire('Error', 'No se pudo cargar el formulario', 'error');
        }
    });
    }

    function verArchivos(id) {
        $.ajax({
            url: `/control-instrumentos/${id}/archivos`,
            type: 'GET',
            success: function(html) {
                $('#modalArchivosBody').html(html);
                $('#modalArchivos').modal('show');
            },
            error: function() {
                Swal.fire('Error', 'No se pudo cargar los archivos', 'error');
            }
        });
    }

    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'DELETE',
                    url: `/control-instrumentos/${id}`,
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        Swal.fire('Eliminado', 'Programa eliminado correctamente', 'success');
                        location.reload();
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo eliminar el programa', 'error');
                    }
                });
            }
        });
    }

    function buscarProgramas() {
        const busqueda = document.getElementById('bus_file').value;
        window.location.href = `/control-instrumentos?search=${busqueda}`;
    }

    function limpiarBusqueda() {
        document.getElementById('bus_file').value = '';
        window.location.href = '/control-instrumentos';
    }

    // Funciones auxiliares
    function obtenerColorSemaforo(fecha) {
        if (!fecha) return 'red';
        const hoy = new Date();
        const proxima = new Date(fecha);
        const diasRestantes = Math.floor((proxima - hoy) / (1000 * 60 * 60 * 24));
        
        if (diasRestantes < 0) return 'red';
        if (diasRestantes < 30) return 'yellow';
        return 'green';
    }
</script>

<style>
    .table-responsive {
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .programa-row {
        vertical-align: middle;
    }
    
    .programa-row:hover {
        background-color: #f0f0fa !important;
    }
    
    .semaforo {
        display: inline-block;
    }
    
    .badge {
        font-size: 10px;
        padding: 4px 6px;
    }
    
    .btn-group-sm .btn {
        padding: 3px 6px;
        font-size: 10px;
    }
    
    @media (max-width: 768px) {
        table {
            font-size: 9px !important;
        }
        
        .btn-group-sm .btn {
            padding: 2px 4px;
            font-size: 8px;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    // Inicializar tooltips y popovers si es necesario
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endpush
