<div class="modal fade" id="nuevoProgramaModal" tabindex="-1" role="dialog" aria-labelledby="nuevoProgramaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg verybigmodal" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="nuevoProgramaLabel">
                    <i class="fa-solid fa-plus"></i> Nuevo Programa de Verificación y Control
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formNuevoPrograma" action="{{ route('programa-verificacion.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
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
                                        <label>Descripción *</label>
                                        <input type="text" name="descripcion" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Marca</label>
                                        <input type="text" name="marca" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Modelo</label>
                                        <input type="text" name="modelo" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Serie</label>
                                        <input type="text" name="serie" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Nº Interno</label>
                                        <input type="text" name="interno" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Proyecto (Contrato) *</label>
                                        <select name="id_programa_verificacion" class="form-control" required>
                                            <option value="">Seleccione</option>
                                            @foreach($areas as $area)
                                                <option value="{{ $area->id }}">{{ $area->descripcion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Tipo de Certificado</label>
                                        <div>
                                            <div class="custom-control custom-checkbox d-inline-block mr-3">
                                                <input type="checkbox" name="cert_calidad" id="cert_calidad_new" class="custom-control-input" value="1">
                                                <label class="custom-control-label" for="cert_calidad_new">Certificado de Calidad</label>
                                            </div>
                                            <div class="custom-control custom-checkbox d-inline-block">
                                                <input type="checkbox" name="cert_calibracion" id="cert_calibracion_new" class="custom-control-input" value="1">
                                                <label class="custom-control-label" for="cert_calibracion_new">Certificado de Calibración</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Certificados --}}
                    <div class="card mb-3" id="seccionCertificados">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-file-pdf"></i> CERTIFICADOS</h6>
                        </div>
                        <div class="card-body">
                            <div id="capaCalidadNuevo" style="display: none;" class="mb-2">
                                <div class="form-group">
                                    <label>Certificado de Calidad</label>
                                    <input type="file" name="archivo_calidad" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">PDF, JPG o PNG (máx. 10 MB)</small>
                                </div>
                            </div>
                            <div id="capaCalibNuevo" style="display: none;" class="mb-2">
                                <div class="form-group">
                                    <label>Certificado de Calibración</label>
                                    <input type="file" name="archivo_calibracion" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">PDF, JPG o PNG (máx. 10 MB)</small>
                                </div>
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
                                        <label>Calibración *</label>
                                        <select name="calibracion" class="form-control" required>
                                            <option value="">Seleccione</option>
                                            <option value="1">Anual</option>
                                            <option value="2">Mensual</option>
                                            <option value="3">No aplica</option>
                                            <option value="4">Semestral</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Verificación</label>
                                        <select name="verificacion" class="form-control">
                                            <option value="">Seleccione</option>
                                            <option value="1">Mensual</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Calibración del Equipo --}}
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa-solid fa-flask"></i> DATOS DE CALIBRACIÓN</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nº Certificado</label>
                                        <input type="text" name="num_cert_calibracion" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Última Calibración</label>
                                        <input type="date" name="fecha_ultima" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Próxima Calibración</label>
                                        <input type="date" name="fecha_proxima" class="form-control">
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
                            <textarea name="observaciones" class="form-control" rows="3" placeholder="Notas adicionales..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Guardar Programa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Mostrar/ocultar campos de certificados según checkboxes
    document.getElementById('cert_calidad_new').addEventListener('change', function() {
        document.getElementById('capaCalidadNuevo').style.display = this.checked ? 'block' : 'none';
    });
    document.getElementById('cert_calibracion_new').addEventListener('change', function() {
        document.getElementById('capaCalibNuevo').style.display = this.checked ? 'block' : 'none';
    });
</script>
