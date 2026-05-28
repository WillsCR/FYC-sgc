<style>
.cert-preview-wrap {
    margin-top: 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    overflow: hidden;
    background: #000;
    position: relative;
}
.cert-preview-wrap iframe,
.cert-preview-wrap img {
    display: block;
    width: 100%;
}
.cert-preview-wrap iframe { height: 420px; border: none; }
.cert-preview-wrap img { max-height: 420px; object-fit: contain; background: #f8f8f8; }
.cert-preview-close {
    position: absolute; top: 6px; right: 6px;
    background: rgba(0,0,0,.55); color: #fff; border: none;
    border-radius: 50%; width: 26px; height: 26px;
    font-size: .85rem; cursor: pointer; display: flex;
    align-items: center; justify-content: center; z-index: 2;
}
.cert-preview-close:hover { background: rgba(0,0,0,.8); }
</style>

<div class="modal-content-pad">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

        {{-- Certificado de Calidad --}}
        <div class="cert-section">
            <div class="cert-section-header calidad">
                <i class="fa-solid fa-certificate"></i> Certificado de Calidad
            </div>
            <div class="cert-section-body">
                @forelse($programa->archivosCalidad as $archivo)
                    <div class="archivo-item">
                        <div>
                            <div class="archivo-nombre">
                                <i class="fa-solid fa-file-pdf" style="color:#DC2626"></i>
                                {{ $archivo->archivo_nombre }}
                            </div>
                            <div class="archivo-meta">
                                {{ round($archivo->archivo_tamanio / 1048576, 2) }} MB
                                · {{ $archivo->fecha_subida->format('d/m/Y') }}
                            </div>
                        </div>
                        <div style="display:flex;gap:4px">
                            <button type="button"
                                    onclick="previsualizarArchivo('calidad-preview', '{{ route('archivos-equipos.ver', $archivo->id) }}', '{{ $archivo->archivo_extension }}')"
                                    class="btn-accion btn-primary-ci" style="padding:3px 8px" title="Ver">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <a href="{{ route('archivos-equipos.descargar', $archivo->id) }}"
                               class="btn-accion btn-files" style="padding:3px 8px" target="_blank" title="Descargar">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </div>
                    </div>
                    {{-- Visor inline --}}
                    <div id="calidad-preview" class="cert-preview-wrap" style="display:none">
                        <button class="cert-preview-close" onclick="cerrarPreview('calidad-preview')" title="Cerrar">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <div id="calidad-preview-content"></div>
                    </div>
                @empty
                    <p style="color:var(--text-muted);font-size:.8rem;margin-bottom:10px">
                        <i class="fa-solid fa-circle-info"></i> Sin certificado cargado
                    </p>
                @endforelse

                <button type="button"
                        onclick="subirCertificado({{ $programa->id }}, 'calidad')"
                        class="btn-accion btn-success-ci"
                        style="width:100%;justify-content:center;margin-top:4px">
                    <i class="fa-solid fa-upload"></i>
                    {{ $programa->archivosCalidad->count() > 0 ? 'Reemplazar' : 'Subir certificado' }}
                </button>
            </div>
        </div>

        {{-- Certificado de Calibración --}}
        <div class="cert-section">
            <div class="cert-section-header calibracion">
                <i class="fa-solid fa-flask"></i> Certificado de Calibración
            </div>
            <div class="cert-section-body">
                @forelse($programa->archivosCalibra as $archivo)
                    <div class="archivo-item">
                        <div>
                            <div class="archivo-nombre">
                                <i class="fa-solid fa-file-pdf" style="color:#DC2626"></i>
                                {{ $archivo->archivo_nombre }}
                            </div>
                            <div class="archivo-meta">
                                {{ round($archivo->archivo_tamanio / 1048576, 2) }} MB
                                · {{ $archivo->fecha_subida->format('d/m/Y') }}
                            </div>
                        </div>
                        <div style="display:flex;gap:4px">
                            <button type="button"
                                    onclick="previsualizarArchivo('calibra-preview', '{{ route('archivos-equipos.ver', $archivo->id) }}', '{{ $archivo->archivo_extension }}')"
                                    class="btn-accion btn-primary-ci" style="padding:3px 8px" title="Ver">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <a href="{{ route('archivos-equipos.descargar', $archivo->id) }}"
                               class="btn-accion btn-files" style="padding:3px 8px" target="_blank" title="Descargar">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </div>
                    </div>
                    {{-- Visor inline --}}
                    <div id="calibra-preview" class="cert-preview-wrap" style="display:none">
                        <button class="cert-preview-close" onclick="cerrarPreview('calibra-preview')" title="Cerrar">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <div id="calibra-preview-content"></div>
                    </div>
                @empty
                    <p style="color:var(--text-muted);font-size:.8rem;margin-bottom:10px">
                        <i class="fa-solid fa-circle-info"></i> Sin certificado cargado
                    </p>
                @endforelse

                <button type="button"
                        onclick="subirCertificado({{ $programa->id }}, 'calibracion')"
                        class="btn-accion btn-success-ci"
                        style="width:100%;justify-content:center;margin-top:4px">
                    <i class="fa-solid fa-upload"></i>
                    {{ $programa->archivosCalibra->count() > 0 ? 'Reemplazar' : 'Subir certificado' }}
                </button>
            </div>
        </div>

    </div>
</div>

<input type="file" id="ci-archivo-tmp" style="display:none" accept=".pdf,.jpg,.jpeg,.png">

<script>
(function() {
    let _ciProgramaId = null;
    let _ciTipo = null;

    // ── Previsualizar ────────────────────────────────────────
    window.previsualizarArchivo = function(containerId, url, ext) {
        const wrap    = document.getElementById(containerId);
        const content = document.getElementById(containerId + '-content');
        const imgs    = ['jpg','jpeg','png','gif','webp','bmp'];

        content.innerHTML = '';

        if (imgs.includes(ext.toLowerCase())) {
            const img = document.createElement('img');
            img.src = url;
            img.alt = 'Previsualización';
            content.appendChild(img);
        } else {
            // PDF u otro: iframe
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.title = 'Previsualización';
            content.appendChild(iframe);
        }

        wrap.style.display = 'block';
        wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    window.cerrarPreview = function(containerId) {
        const wrap = document.getElementById(containerId);
        wrap.style.display = 'none';
        document.getElementById(containerId + '-content').innerHTML = '';
    };

    // ── Subir certificado ────────────────────────────────────
    window.subirCertificado = function(programaId, tipo) {
        _ciProgramaId = programaId;
        _ciTipo = tipo;
        const input = document.getElementById('ci-archivo-tmp');
        input.value = '';
        input.click();
    };

    document.getElementById('ci-archivo-tmp').addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;

        const fd = new FormData();
        fd.append('archivo',     file);
        fd.append('id_programa', _ciProgramaId);
        fd.append('tipo',        _ciTipo === 'calidad' ? '1' : '2');
        fd.append('_token',      window.CSRF_TOKEN || '{{ csrf_token() }}');

        const cuerpo = document.getElementById('modal-archivos-body');
        cuerpo.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;padding:30px;color:var(--text-muted)"><i class="fa-solid fa-spinner fa-spin"></i>&nbsp;Subiendo archivo...</div>';

        try {
            const res  = await fetch('/archivos-equipos/subir-certificado', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                if (typeof notif === 'function') notif('success', 'Archivo subido correctamente');
                if (typeof verArchivos === 'function') verArchivos(_ciProgramaId);
            } else {
                if (typeof notif === 'function') notif('error', data.mensaje || 'Error al subir');
                if (typeof verArchivos === 'function') verArchivos(_ciProgramaId);
            }
        } catch (err) {
            if (typeof notif === 'function') notif('error', 'Error de conexión');
            console.error(err);
        }
    });
})();
</script>
