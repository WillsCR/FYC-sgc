<form id="formEditarProgramaInline" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#tabDatos">Datos Principales</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tabCertificados">Certificados</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tabCalibración">Calibración</a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        {{-- Tab: Datos Principales --}}
        <div id="tabDatos" class="tab-pane fade show active">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Descripción *</label>
                        <input type="text" name="descripcion" class="form-control" id="descripcion" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="marca" class="form-control" id="marca">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="modelo" class="form-control" id="modelo">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Serie</label>
                        <input type="text" name="serie" class="form-control" id="serie">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nº Interno</label>
                        <input type="text" name="interno" class="form-control" id="interno">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Proyecto *</label>
                        <select name="id_area" class="form-control" id="id_area" required>
                            <option value="">Seleccione</option>
                            @foreach($areas ?? [] as $area)
                                <option value="{{ $area->id }}">{{ $area->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab: Certificados --}}
        <div id="tabCertificados" class="tab-pane fade">
            <div class="row">
                <div class="col-md-6">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="cert_calidad" id="cert_calidad" class="custom-control-input" value="1">
                        <label class="custom-control-label" for="cert_calidad">
                            <strong>Certificado de Calidad</strong>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="cert_calibracion" id="cert_calibracion" class="custom-control-input" value="1">
                        <label class="custom-control-label" for="cert_calibracion">
                            <strong>Certificado de Calibración</strong>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab: Calibración --}}
        <div id="tabCalibración" class="tab-pane fade">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Frecuencia Calibración *</label>
                        <select name="calibracion" class="form-control" id="calibracion" required>
                            <option value="">Seleccione</option>
                            <option value="1">Anual</option>
                            <option value="2">Mensual</option>
                            <option value="3">No aplica</option>
                            <option value="4">Semestral</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Frecuencia Verificación</label>
                        <select name="verificacion" class="form-control" id="verificacion">
                            <option value="">Seleccione</option>
                            <option value="1">Mensual</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nº Certificado Calibración</label>
                        <input type="text" name="num_cert_calibracion" class="form-control" id="num_cert_calibracion">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Última Calibración</label>
                        <input type="date" name="fecha_ultima" class="form-control" id="fecha_ultima">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Próxima Calibración</label>
                        <input type="date" name="fecha_proxima" class="form-control" id="fecha_proxima">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="form-control" id="observaciones" rows="3"></textarea>
                    </div>
                </div>
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

<script>
    document.getElementById('formEditarProgramaInline').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const programaId = {{ $programa->id ?? 'null' }};
        
        $.ajax({
            type: 'POST',
            url: `/control-instrumentos/${programaId}`,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    text: 'Programa actualizado correctamente',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire('Error', 'No se pudo guardar el programa', 'error');
            }
        });
    });
</script>
