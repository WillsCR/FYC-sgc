<form id="form-editar-prog" data-id="{{ $programa->id }}" novalidate>
    @csrf
    @method('PUT')

    <div class="modal-content-pad">

        <div class="form-section">
            <div class="form-section-title"><i class="fa-solid fa-cog"></i> Características del equipo</div>
            <div class="form-row" style="grid-template-columns:repeat(auto-fill,minmax(130px,1fr))">
                <div class="form-group">
                    <label>Descripción *</label>
                    <input type="text" name="descripcion" required value="{{ $programa->descripcion }}">
                </div>
                <div class="form-group">
                    <label>Marca</label>
                    <input type="text" name="marca" value="{{ $programa->marca }}">
                </div>
                <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" name="modelo" value="{{ $programa->modelo }}">
                </div>
                <div class="form-group">
                    <label>Serie</label>
                    <input type="text" name="serie" value="{{ $programa->serie }}">
                </div>
                <div class="form-group">
                    <label>N° Interno</label>
                    <input type="text" name="interno" value="{{ $programa->interno }}">
                </div>
            </div>
            <div class="form-row-2" style="margin-top:12px">
                <div class="form-group">
                    <label>Proyecto / Contrato *</label>
                    <select name="id_contrato" required>
                        <option value="">Seleccione...</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ $programa->id_contrato == $area->id ? 'selected' : '' }}>
                                {{ $area->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipo de certificado</label>
                    <div style="display:flex;gap:20px;padding-top:4px">
                        <label class="form-check">
                            <input type="checkbox" name="cert_calidad" value="1"
                                   {{ $programa->cert_calidad ? 'checked' : '' }}>
                            <label>Certificado de Calidad</label>
                        </label>
                        <label class="form-check">
                            <input type="checkbox" name="cert_calibracion" value="1"
                                   {{ $programa->cert_calibracion ? 'checked' : '' }}>
                            <label>Certificado de Calibración</label>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="fa-solid fa-calendar"></i> Frecuencia</div>
            <div class="form-row-2">
                <div class="form-group">
                    <label>Calibración *</label>
                    <select name="calibracion" required>
                        <option value="">Seleccione...</option>
                        <option value="1" {{ $programa->calibracion == 1 ? 'selected' : '' }}>Anual</option>
                        <option value="2" {{ $programa->calibracion == 2 ? 'selected' : '' }}>Mensual</option>
                        <option value="3" {{ $programa->calibracion == 3 ? 'selected' : '' }}>No aplica</option>
                        <option value="4" {{ $programa->calibracion == 4 ? 'selected' : '' }}>Semestral</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Verificación</label>
                    <select name="verificacion">
                        <option value="">Seleccione...</option>
                        <option value="1" {{ $programa->verificacion == 1 ? 'selected' : '' }}>Mensual</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="fa-solid fa-flask"></i> Datos de calibración</div>
            <div class="form-row-3">
                <div class="form-group">
                    <label>N° Certificado</label>
                    <input type="text" name="certificado_calibracion"
                           value="{{ $programa->certificado_calibracion }}">
                </div>
                <div class="form-group">
                    <label>Última calibración</label>
                    <input type="date" name="ultima"
                           value="{{ $programa->ultima ? $programa->ultima->format('Y-m-d') : '' }}">
                </div>
                <div class="form-group">
                    <label>Próxima calibración</label>
                    <input type="date" name="proxima"
                           value="{{ $programa->proxima ? $programa->proxima->format('Y-m-d') : '' }}">
                </div>
            </div>
        </div>

        <div class="form-row-2" style="margin-bottom:12px">
            <div class="form-group">
                <label>Responsable</label>
                <input type="text" name="responsable" value="{{ $programa->responsable }}"
                       placeholder="Ej: Juan Pérez">
            </div>
            <div class="form-group">
                <label>Correo de aviso</label>
                <div class="ci-correos-container" id="ci-correos-edit"></div>
                <input type="hidden" name="correo_aviso" id="ci-correo-edit" value="{{ $programa->correo_aviso ?? '' }}">
            </div>
        </div>
        <div class="form-group">
            <label>Observaciones</label>
            <textarea name="observaciones" rows="2">{{ $programa->observaciones }}</textarea>
        </div>

    </div>

    <div class="modal-footer">
        <button type="button" class="btn-accion btn-secondary-ci" onclick="cerrarModal('modal-editar')">Cancelar</button>
        <button type="submit" class="btn-accion btn-primary-ci">
            <i class="fa-solid fa-save"></i> Guardar Cambios
        </button>
    </div>
</form>
<script>
    if (typeof ciCorreosEditInit === 'function') {
        ciCorreosEditInit('{{ addslashes($programa->correo_aviso ?? '') }}');
    }
</script>
