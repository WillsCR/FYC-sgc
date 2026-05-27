<div class="row">
    {{-- Certificado de Calidad --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">Certificado de Calidad</h6>
            </div>
            <div class="card-body">
                @if($programa->archivosCalidad->count() > 0)
                    <div class="list-group mb-3">
                        @foreach($programa->archivosCalidad as $archivo)
                            <a href="{{ route('archivos-equipos.descargar', $archivo->id) }}" class="list-group-item list-group-item-action" target="_blank">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="fa-solid fa-file-pdf text-danger"></i> {{ $archivo->archivo_nombre }}
                                    </h6>
                                    <small>{{ $archivo->mb }} MB</small>
                                </div>
                                <small class="text-muted">Subido: {{ $archivo->fecha_subida->format('d/m/Y H:i') }}</small>
                            </a>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-warning btn-block btn-sm" onclick="reemplazarArchivo({{ $programa->id }}, 'calidad')">
                        <i class="fa-solid fa-refresh"></i> Reemplazar
                    </button>
                @else
                    <p class="text-muted"><i class="fa-solid fa-circle-info"></i> No hay certificado de calidad cargado</p>
                    <button type="button" class="btn btn-success btn-block btn-sm" onclick="subirArchivo({{ $programa->id }}, 'calidad')">
                        <i class="fa-solid fa-upload"></i> Subir Certificado
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Certificado de Calibración --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">Certificado de Calibración</h6>
            </div>
            <div class="card-body">
                @if($programa->archivosCalibra->count() > 0)
                    <div class="list-group mb-3">
                        @foreach($programa->archivosCalibra as $archivo)
                            <a href="{{ route('archivos-equipos.descargar', $archivo->id) }}" class="list-group-item list-group-item-action" target="_blank">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="fa-solid fa-file-pdf text-danger"></i> {{ $archivo->archivo_nombre }}
                                    </h6>
                                    <small>{{ $archivo->mb }} MB</small>
                                </div>
                                <small class="text-muted">Subido: {{ $archivo->fecha_subida->format('d/m/Y H:i') }}</small>
                            </a>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-warning btn-block btn-sm" onclick="reemplazarArchivo({{ $programa->id }}, 'calibracion')">
                        <i class="fa-solid fa-refresh"></i> Reemplazar
                    </button>
                @else
                    <p class="text-muted"><i class="fa-solid fa-circle-info"></i> No hay certificado de calibración cargado</p>
                    <button type="button" class="btn btn-success btn-block btn-sm" onclick="subirArchivo({{ $programa->id }}, 'calibracion')">
                        <i class="fa-solid fa-upload"></i> Subir Certificado
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Input hidden para subir archivos --}}
<input type="file" id="archivoTemp" style="display: none;" accept=".pdf,.jpg,.jpeg,.png">

<script>
    function subirArchivo(programaId, tipo) {
        const input = document.getElementById('archivoTemp');
        input.dataset.programaId = programaId;
        input.dataset.tipo = tipo;
        input.click();
    }

    function reemplazarArchivo(programaId, tipo) {
        const input = document.getElementById('archivoTemp');
        input.dataset.programaId = programaId;
        input.dataset.tipo = tipo;
        input.click();
    }

    document.getElementById('archivoTemp').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const programaId = this.dataset.programaId;
        const tipo = this.dataset.tipo;
        const tipoDocumento = tipo === 'calidad' ? 'cert_calidad' : 'cert_calibracion';

        const formData = new FormData();
        formData.append('archivo', file);
        formData.append('id_programa', programaId);
        formData.append('tipo_documento', tipoDocumento);

        $.ajax({
            type: 'POST',
            url: '{{ route("archivos-equipos.subir-certificado") }}',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                Swal.fire({
                    title: 'Cargando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function(response) {
                Swal.fire('Éxito', 'Archivo cargado correctamente', 'success').then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Error al cargar el archivo';
                Swal.fire('Error', error, 'error');
            }
        });

        // Limpiar input
        this.value = '';
    });
</script>
