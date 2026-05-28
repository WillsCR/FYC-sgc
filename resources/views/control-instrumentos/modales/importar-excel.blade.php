<div class="modal fade" id="importarExcelModal" tabindex="-1" role="dialog" aria-labelledby="importarExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="importarExcelLabel">
                    <i class="fa-solid fa-upload"></i> Importar Datos desde Excel
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formImportarExcel" action="{{ route('control-instrumentos.importar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6><i class="fa-solid fa-circle-info"></i> Instrucciones de Importación</h6>
                        <p class="mb-0">
                            El archivo Excel debe contener las columnas en este orden:
                        </p>
                        <ul class="mt-2 mb-0" style="font-size: 12px;">
                            <li><strong>N°</strong> - Número secuencial</li>
                            <li><strong>PROYECTO</strong> - Nombre del proyecto/contrato</li>
                            <li><strong>CERTIFICADO</strong> - Tipo (Calidad/Calibración/Ambos)</li>
                            <li><strong>DESCRIPCIÓN</strong> - Descripción del equipo</li>
                            <li><strong>MARCA</strong> - Marca del equipo</li>
                            <li><strong>MODELO</strong> - Modelo del equipo</li>
                            <li><strong>SERIE</strong> - Número de serie</li>
                            <li><strong>Nº INTERNO</strong> - Número interno</li>
                            <li><strong>CALIBRACIÓN</strong> - Frecuencia (Anual/Mensual/Semestral/No aplica)</li>
                            <li><strong>VERIFICACIÓN</strong> - Frecuencia (Mensual)</li>
                            <li><strong>Nº CERT CALIBRACIÓN</strong> - Número de certificado</li>
                            <li><strong>ÚLTIMA</strong> - Fecha última calibración (dd-mm-yyyy)</li>
                            <li><strong>PRÓXIMA</strong> - Fecha próxima calibración (dd-mm-yyyy)</li>
                            <li><strong>RESPONSABLE</strong> - Nombre del responsable (opcional)</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label><strong>Seleccionar archivo Excel:</strong></label>
                        <input type="file" name="archivo_excel" id="archivoExcel" class="form-control-file" accept=".xlsx,.xls" required>
                        <small class="form-text text-muted">Formato: .xlsx o .xls</small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="borrarExistentes" name="borrar_existentes" value="0">
                            <label class="custom-control-label" for="borrarExistentes">
                                ⚠️ Eliminar todos los registros existentes antes de importar
                            </label>
                        </div>
                    </div>

                    <div id="previewImportacion" style="display: none;">
                        <div class="alert alert-warning">
                            <strong>Vista previa:</strong>
                            <p id="previewTexto"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnImportar">
                        <i class="fa-solid fa-upload"></i> Importar Datos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('formImportarExcel').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = document.getElementById('btnImportar');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importando...';
        
        $.ajax({
            type: 'POST',
            url: '{{ route("control-instrumentos.importar") }}',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Importación exitosa',
                    html: `<p>Se importaron <strong>${response.importados}</strong> registros correctamente.</p>`,
                    confirmButtonText: 'Recargar página'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors || {};
                let mensaje = 'Error al importar el archivo';
                
                if (xhr.responseJSON?.message) {
                    mensaje = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la importación',
                    text: mensaje,
                    confirmButtonText: 'Ok'
                });
                
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });
</script>
