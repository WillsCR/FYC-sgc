@extends('layouts.app')

@section('title', 'Control de Instrumentos - Verificación y Calibración')

@section('content')
<div class="container-fluid mt-4">
    {{-- Panel Principal --}}
    <div class="panel panel-default" style="background-color:#f5f5f5;">
        <div class="panel-heading" style="padding: 10px;">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 style="display: inline-block; margin: 0;">
                        <i class="fa-solid fa-gauge"></i> CONTROL DE INSTRUMENTOS
                    </h5>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#importarExcelModal">
                        <i class="fa-solid fa-upload"></i> Importar Excel
                    </button>
                    @can('create', App\Models\ProgramaVerificacion::class)
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#nuevoProgramaModal">
                            <i class="fa-solid fa-plus"></i> Nuevo Programa
                        </button>
                    @endcan
                    @if(auth()->user()->sstipo == 2 || auth()->user()->sstipo == 1)
                        <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#permisosModal">
                            <i class="fa-solid fa-lock"></i> Permisos
                        </button>
                    @endif
                    <button onclick="togglePantallaCompleta()" id="btnFullscreen" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-arrows-fullscreen"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Barra de búsqueda --}}
        <div class="panel-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" id="bus_file" class="form-control" placeholder="Buscar por descripción...">
                        <span class="input-group-btn">
                            <button class="btn btn-success" type="button" onclick="buscarProgramas()">
                                <i class="fa-solid fa-search"></i>
                            </button>
                            <button class="btn btn-secondary" type="button" onclick="limpiarBusqueda()">
                                <i class="fa-solid fa-refresh"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <button onclick="togglePantallaCompleta()" id="btnFullscreen" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-arrows-fullscreen"></i> Pantalla completa
                    </button>
                </div>
            </div>

            {{-- Tabla de Programas --}}
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm" id="tablaProgramas">
                    <thead style="background-color: #2d6181; color: white;">
                        <tr>
                            <th colspan="8" class="text-center">CARACTERÍSTICAS DEL EQUIPO</th>
                            <th colspan="2" class="text-center">FRECUENCIA</th>
                            <th colspan="4" class="text-center">CALIBRACIÓN</th>
                            <th class="text-center">ESTADO</th>
                            <th class="text-center">RESPONSABLE</th>
                            <th class="text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <thead style="background-color: #2d6181; color: white;">
                        <tr>
                            <th style="width: 40px;">N°</th>
                            <th>PROYECTO</th>
                            <th>CERTIFICADO</th>
                            <th>DESCRIPCIÓN</th>
                            <th>MARCA</th>
                            <th>MODELO</th>
                            <th>SERIE</th>
                            <th>Nº INTERNO</th>
                            <th>CALIB.</th>
                            <th>VERIF.</th>
                            <th>Nº CERT</th>
                            <th>ÚLTIMA</th>
                            <th>PRÓXIMA</th>
                            <th class="text-center">⚠️</th>
                            <th>RESPONSABLE</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTabla">
                        @forelse($programas as $programa)
                            <tr class="programa-row" id="programa-{{ $programa->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $programa->programaVerificacion?->descripcion ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($programa->cert_calidad)
                                        <span class="badge badge-success">Calidad</span>
                                    @endif
                                    @if($programa->cert_calibracion)
                                        <span class="badge badge-warning">Calibración</span>
                                    @endif
                                </td>
                                <td><strong>{{ $programa->descripcion }}</strong></td>
                                <td>{{ $programa->marca ?? '-' }}</td>
                                <td>{{ $programa->modelo ?? '-' }}</td>
                                <td>{{ $programa->serie ?? '-' }}</td>
                                <td>{{ $programa->interno ?? '-' }}</td>
                                <td>{{ $frecuenciasCalib[$programa->calibracion] ?? '-' }}</td>
                                <td>{{ $frecuenciasVerif[$programa->verificacion] ?? '-' }}</td>
                                <td>{{ $programa->num_cert_calibracion ?? '-' }}</td>
                                <td>
                                    @if($programa->fecha_ultima)
                                        {{ \Carbon\Carbon::parse($programa->fecha_ultima)->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($programa->fecha_proxima)
                                        {{ \Carbon\Carbon::parse($programa->fecha_proxima)->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="semaforo" style="
                                        border-radius: 50%;
                                        width: 30px;
                                        height: 30px;
                                        margin: 0 auto;
                                        background-color: {{ obtenerColorSemaforo($programa->fecha_proxima) }};
                                    "></div>
                                </td>
                                <td>
                                    @forelse($programa->responsables as $resp)
                                        <span class="badge badge-primary">{{ $resp->usuario->nombre ?? 'N/A' }}</span>
                                    @empty
                                        <span class="text-muted">Sin asignar</span>
                                    @endforelse
                                </td>
                                <td class="text-center">
                                    @can('update', $programa)
                                        <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editarModal{{ $programa->id }}" title="Editar">
                                            <i class="fa-solid fa-pencil"></i>
                                        </button>
                                    @endcan
                                    @can('delete', $programa)
                                        <button class="btn btn-danger btn-xs" onclick="eliminarPrograma({{ $programa->id }})" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endcan
                                    <button class="btn btn-info btn-xs" onclick="verDetalles({{ $programa->id }})" title="Ver detalles">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="16" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-inbox" style="font-size: 2rem;"></i><br>
                                    No hay programas de verificación registrados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <x-paginacion :paginator="$programas" label="instrumentos" />
        </div>
    </div>
</div>

{{-- MODALES --}}

{{-- Modal: Nuevo Programa --}}
@include('control-instrumentos.modales.nuevo-programa')

{{-- Modal: Editar Programa --}}
@foreach($programas as $programa)
    @include('control-instrumentos.modales.editar-programa', ['programa' => $programa])
@endforeach

{{-- Modal: Permisos --}}
@include('control-instrumentos.modales.permisos')

{{-- Scripts --}}
<script src="{{ asset('js/control-instrumentos.js') }}"></script>

<style>
    .table-responsive {
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .programa-row {
        font-size: 12px;
        vertical-align: middle;
    }
    
    .programa-row:hover {
        background-color: #f0f0fa !important;
    }
    
    .semaforo {
        display: inline-block;
        border: 2px solid #ccc;
    }
    
    .badge {
        margin-right: 3px;
        font-size: 11px;
    }
    
    .btn-xs {
        padding: 3px 6px;
        font-size: 11px;
    }
    
    .verybigmodal {
        max-width: 95%;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 10px;
        }
        
        .btn-xs {
            padding: 2px 4px;
            font-size: 9px;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    function togglePantallaCompleta() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }

    document.addEventListener("fullscreenchange", () => {
        const btn = document.getElementById("btnFullscreen");
        if (document.fullscreenElement) {
            btn.innerHTML = '<i class="fa-solid fa-arrows-fullscreen-exit"></i> Salir pantalla completa';
        } else {
            btn.innerHTML = '<i class="fa-solid fa-arrows-fullscreen"></i> Pantalla completa';
        }
    });

    function buscarProgramas() {
        const busqueda = document.getElementById('bus_file').value;
        window.location.href = `{{ route('control-instrumentos.index') }}?search=${busqueda}`;
    }

    function limpiarBusqueda() {
        document.getElementById('bus_file').value = '';
        window.location.href = `{{ route('control-instrumentos.index') }}`;
    }

    function verDetalles(id) {
        Swal.fire({
            title: 'Cargando...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        // Implementar carga de detalles
    }

    function eliminarPrograma(id) {
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
</script>
@endpush
