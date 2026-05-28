<div class="modal fade" id="permisosModal" tabindex="-1" role="dialog" aria-labelledby="permisosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="permisosModalLabel">
                    <i class="fa-solid fa-lock"></i> Permisos de Acceso - Control Instrumentos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>USUARIO</th>
                                <th class="text-center">VISUALIZAR</th>
                                <th class="text-center">EDITAR</th>
                                <th class="text-center">ELIMINAR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuario)
                                <tr>
                                    <td>
                                        <strong>{{ $usuario->email }}</strong><br>
                                        <small class="text-muted">{{ $usuario->nombre }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" 
                                                   class="custom-control-input permiso-checkbox" 
                                                   id="ver_{{ $usuario->id }}"
                                                   data-user="{{ $usuario->id }}"
                                                   data-tipo="ver"
                                                   {{ $usuario->ver_control_instrumentos ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="ver_{{ $usuario->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" 
                                                   class="custom-control-input permiso-checkbox" 
                                                   id="editar_{{ $usuario->id }}"
                                                   data-user="{{ $usuario->id }}"
                                                   data-tipo="editar"
                                                   {{ $usuario->editar_control_instrumentos ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="editar_{{ $usuario->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" 
                                                   class="custom-control-input permiso-checkbox" 
                                                   id="eliminar_{{ $usuario->id }}"
                                                   data-user="{{ $usuario->id }}"
                                                   data-tipo="eliminar"
                                                   {{ $usuario->eliminar_control_instrumentos ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="eliminar_{{ $usuario->id }}"></label>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        No hay usuarios disponibles
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.permiso-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const userId = this.dataset.user;
            const tipo = this.dataset.tipo;
            const estado = this.checked ? 1 : 0;

            $.ajax({
                type: 'POST',
                url: '{{ route("control-instrumentos.actualizar-permisos") }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    usuario_id: userId,
                    tipo: tipo,
                    estado: estado
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Actualizado',
                            text: 'Permisos actualizados correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudieron actualizar los permisos', 'error');
                }
            });
        });
    });
</script>
