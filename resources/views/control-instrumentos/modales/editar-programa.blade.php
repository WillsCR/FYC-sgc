<div class="modal fade" id="editarModal{{ $programa->id }}" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg verybigmodal" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fa-solid fa-pencil"></i> Editar Programa - ID: {{ $programa->id }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditar{{ $programa->id }}" action="{{ route('programa-verificacion.update', $programa->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    {{-- Características del Equipo --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-cog"></i> CARACTERÍSTICAS DEL EQUIPO</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <input type="text" name="descripcion" class="form-control" value="{{ $programa->descripcion }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Marca</label>
                                        <input type="text" name="marca" class="form-control" value="{{ $programa->marca }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Modelo</label>
                                        <input type="text" name="modelo" class="form-control" value="{{ $programa->modelo }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Serie</label>
                                        <input type="text" name="serie" class="form-control" value="{{ $programa->serie }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Nº Interno</label>
                                        <input type="text" name="interno" class="form-control" value="{{ $programa->interno }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Certificados --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-file-pdf"></i> CERTIFICADOS</h6>
                        </div>
                        <div class="card-body">
                            @if($programa->archivosCalidad->count() > 0)
                                <div class="alert alert-info mb-2">
                                    <strong>Certificado de Calidad Actual:</strong>
                                    @foreach($programa->archivosCalidad as $archivo)
                                        <br>
                                        <a href="{{ route('archivos-equipos.descargar', $archivo->id) }}" class="badge badge-success">
                                            <i class="fa-solid fa-download"></i> {{ $archivo->archivo_nombre }}
                                        </a>
                                        <button type="button" class="badge badge-danger" onclick="eliminarArchivo({{ $archivo->id }})">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            <div class="form-group">
                                <label>Reemplazar Certificado de Calidad</label>
                                <input type="file" name="archivo_calidad" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <hr>
                            @if($programa->archivosCalibra->count() > 0)
                                <div class="alert alert-info mb-2">
                                    <strong>Certificado de Calibración Actual:</strong>
                                    @foreach($programa->archivosCalibra as $archivo)
                                        <br>
                                        <a href="{{ route('archivos-equipos.descargar', $archivo->id) }}" class="badge badge-success">
                                            <i class="fa-solid fa-download"></i> {{ $archivo->archivo_nombre }}
                                        </a>
                                        <button type="button" class="badge badge-danger" onclick="eliminarArchivo({{ $archivo->id }})">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            <div class="form-group">
                                <label>Reemplazar Certificado de Calibración</label>
                                <input type="file" name="archivo_calibracion" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>

                    {{-- Frecuencia --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-calendar"></i> FRECUENCIA</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Calibración</label>
                                        <select name="calibracion" class="form-control">
                                            <option value="">Seleccione</option>
                                            <option value="1" {{ $programa->calibracion == 1 ? 'selected' : '' }}>Anual</option>
                                            <option value="2" {{ $programa->calibracion == 2 ? 'selected' : '' }}>Mensual</option>
                                            <option value="3" {{ $programa->calibracion == 3 ? 'selected' : '' }}>No aplica</option>
                                            <option value="4" {{ $programa->calibracion == 4 ? 'selected' : '' }}>Semestral</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Verificación</label>
                                        <select name="verificacion" class="form-control">
                                            <option value="">Seleccione</option>
                                            <option value="1" {{ $programa->verificacion == 1 ? 'selected' : '' }}>Mensual</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Datos de Calibración --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-flask"></i> DATOS DE CALIBRACIÓN</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nº Certificado</label>
                                        <input type="text" name="num_cert_calibracion" class="form-control" value="{{ $programa->num_cert_calibracion }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Última Calibración</label>
                                        <input type="date" name="fecha_ultima" class="form-control" value="{{ $programa->fecha_ultima }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Próxima Calibración</label>
                                        <input type="date" name="fecha_proxima" class="form-control" value="{{ $programa->fecha_proxima }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Observaciones --}}
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-note"></i> OBSERVACIONES</h6>
                        </div>
                        <div class="card-body">
                            <textarea name="observaciones" class="form-control" rows="3">{{ $programa->observaciones }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
