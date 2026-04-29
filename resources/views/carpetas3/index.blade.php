@extends('layouts.app')

@section('content')
<div class="container-fluid h-100 d-flex flex-column">
    <!-- Header -->
    <div class="bg-light border-bottom px-4 py-3 mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="mb-0">📁 Gestión Documental</h2>
                <p class="text-muted mb-0 small">{{ $carpetaActual->obtenerRuta() }}</p>
            </div>
            <div class="col-auto">
                @if($esAdmin)
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSubirArchivo">
                        📤 Subir Archivo
                    </button>
                    <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearCarpeta">
                        📂 Nueva Carpeta
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="row flex-grow-1">
        <!-- Panel izquierdo: Árbol de carpetas -->
        <div class="col-md-3 border-end">
            <div class="p-3">
                <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Módulos</h6>
                
                @foreach($raices as $raiz)
                    <div class="mb-2">
                        <a href="{{ route('carpetas3.show', ['modulo' => strtolower(str_replace(' ', '-', $raiz->descripcion)), 'id' => $raiz->id]) }}"
                           class="btn btn-sm {{ $raiz->id == $carpetaActual->id_padre ?: ($raiz->id == $carpetaActual->id ? 'btn-primary' : 'btn-outline-secondary') }} w-100 text-start">
                            📋 {{ $raiz->descripcion }}
                        </a>

                        @if($raiz->id == $carpetaActual->id || $raiz->id == $carpetaActual->id_padre)
                            <div class="ms-3 mt-2" id="submodulos-{{ $raiz->id }}">
                                @forelse($raiz->hijos as $hijo)
                                    <div class="mb-1">
                                        <a href="{{ route('carpetas3.show', ['modulo' => strtolower(str_replace(' ', '-', $raiz->descripcion)), 'id' => $hijo->id]) }}"
                                           class="btn btn-sm btn-outline-secondary w-100 text-start small" 
                                           style="font-size: 0.85rem;">
                                            {{ $hijo->descripcion }}
                                        </a>
                                    </div>
                                @empty
                                    <p class="text-muted small">Sin subcarpetas</p>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Panel derecho: Contenido -->
        <div class="col-md-9">
            <div class="p-4">
                <!-- Breadcrumb -->
                @if(isset($breadcrumb) && count($breadcrumb) > 0)
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            @foreach($breadcrumb as $item)
                                <li class="breadcrumb-item">{{ $item['nombre'] }}</li>
                            @endforeach
                        </ol>
                    </nav>
                @endif

                <!-- Documentos -->
                <h5 class="mb-3">📄 Documentos ({{ count($contenido) }})</h5>
                
                @if(count($contenido) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover small">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tamaño</th>
                                    <th>Subido</th>
                                    <th style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contenido as $doc)
                                    <tr>
                                        <td>
                                            <strong>{{ $doc->nombre }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $doc->nombre_original }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $tamaño = $doc->tamano;
                                                if ($tamaño > 1024*1024) {
                                                    $display = round($tamaño / (1024*1024), 2) . ' MB';
                                                } elseif ($tamaño > 1024) {
                                                    $display = round($tamaño / 1024, 2) . ' KB';
                                                } else {
                                                    $display = $tamaño . ' B';
                                                }
                                            @endphp
                                            {{ $display }}
                                        </td>
                                        <td>{{ $doc->creada_el->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('archivos3.ver', $doc->id) }}" 
                                               class="btn btn-sm btn-info" target="_blank">
                                                👁️
                                            </a>
                                            <a href="{{ route('archivos3.descargar', $doc->id) }}" 
                                               class="btn btn-sm btn-success">
                                                ⬇️
                                            </a>
                                            @if($esAdmin)
                                                <button class="btn btn-sm btn-danger" onclick="eliminarArchivo({{ $doc->id }})">
                                                    🗑️
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info" role="alert">
                        No hay documentos en esta carpeta
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal: Subir Archivo -->
<div class="modal fade" id="modalSubirArchivo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📤 Subir Archivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSubirArchivo">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="archivo" class="form-label">Seleccionar archivo</label>
                        <input type="file" class="form-control" id="archivo" name="archivo" required>
                        <small class="text-muted">Máx. 20 MB • PDF, Office, imágenes, ZIP</small>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Subir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Crear Carpeta -->
<div class="modal fade" id="modalCrearCarpeta" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📂 Nueva Carpeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('carpetas3.store', ['modulo' => $modulo, 'id' => $carpetaActual->id]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombreCarpeta" class="form-label">Nombre de la carpeta</label>
                        <input type="text" class="form-control" id="nombreCarpeta" name="descripcion" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Subir archivo via AJAX
    document.getElementById('formSubirArchivo').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('archivo', document.getElementById('archivo').files[0]);
        formData.append('descripcion', document.getElementById('descripcion').value);
        formData.append('id_carpeta', {{ $carpetaActual->id }});

        fetch('{{ route("archivos3.subir") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert('Archivo subido correctamente');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => alert('Error: ' + err.message));
    });

    // Eliminar archivo
    function eliminarArchivo(id) {
        if (!confirm('¿Eliminar este archivo?')) return;
        
        fetch(`/archivos3/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert('Archivo eliminado');
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => alert('Error: ' + err.message));
    }
</script>
@endsection
