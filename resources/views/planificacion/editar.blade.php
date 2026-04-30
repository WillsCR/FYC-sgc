@extends('layouts.app')
@section('title', isset($plan) ? 'Editar planificación' : 'Nueva planificación')

@push('styles')
<style>
.form-plan-body { padding: 20px; max-width: 800px; margin: 0 auto; }
.form-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); overflow: hidden; margin-bottom: 16px;
}
.form-card-header {
    background: var(--navy); color: #fff;
    padding: 12px 18px; font-size: .82rem; font-weight: 700;
}
.form-card-body { padding: 18px; }
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px; }
.btn-guardar {
    padding: 10px 24px; background: var(--navy); color: #fff;
    border: none; border-radius: var(--radius-sm);
    font-size: .85rem; font-weight: 600; cursor: pointer;
}
.btn-guardar:hover { background: var(--navy-light); }
.btn-cancelar {
    padding: 10px 20px; background: transparent; color: var(--text-secondary);
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: .85rem; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center;
}
.breadcrumb-nav {
    display: flex; align-items: center; gap: 6px;
    margin-bottom: 16px; font-size: .78rem; color: var(--text-muted);
}
.breadcrumb-nav a { color: var(--text-muted); text-decoration: none; }
.breadcrumb-nav a:hover { color: var(--navy); }
@media (max-width: 640px) {
    .form-plan-body { padding: 12px; }
    .form-grid-2 { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="form-plan-body">

    <div class="breadcrumb-nav">
        <a href="{{ route('planificacion.index') }}">📋 Planificación</a>
        <span>›</span>
        <span>{{ isset($plan) ? 'Editar planificación #' . $plan->id : 'Nueva planificación' }}</span>
    </div>

    @php
        $accion = isset($plan)
            ? route('planificacion.update', $plan->id)
            : route('planificacion.store');
        $metodo = isset($plan) ? 'PUT' : 'POST';
    @endphp

    <form method="POST" action="{{ $accion }}">
        @csrf
        @if($metodo === 'PUT') @method('PUT') @endif

        <div class="form-card">
            <div class="form-card-header">
                {{ isset($plan) ? '✏️ Editar planificación' : '➕ Nueva planificación' }}
            </div>
            <div class="form-card-body">

                {{-- Actividades --}}
                <div class="form-group" style="margin-bottom:14px">
                    <label class="form-label">Actividad / Descripción</label>
                    <textarea name="actividades" rows="3"
                              class="form-control @error('actividades') is-invalid @enderror"
                              placeholder="Describe la actividad a planificar..." required
                              style="resize:vertical">{{ old('actividades', $plan->actividades ?? '') }}</textarea>
                    @error('actividades') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-grid-2">

                    {{-- Área --}}
                    <div class="form-group">
                        <label class="form-label">Área</label>
                        <select name="area" class="form-control @error('area') is-invalid @enderror" required>
                            <option value="">Selecciona un área</option>
                            @foreach($areas as $id => $nombre)
                            <option value="{{ $id }}"
                                {{ old('area', $plan->area ?? '') == $id ? 'selected' : '' }}>
                                {{ $nombre }}
                            </option>
                            @endforeach
                        </select>
                        @error('area') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- Responsable --}}
                    <div class="form-group">
                        <label class="form-label">Responsable</label>
                        <input type="text" name="responsable"
                               class="form-control @error('responsable') is-invalid @enderror"
                               value="{{ old('responsable', $plan->responsable ?? '') }}"
                               placeholder="Nombre del responsable" required
                               list="lista-responsables">
                        <datalist id="lista-responsables">
                            @foreach($responsables as $r)
                            <option value="{{ $r->nombre }}">
                            @endforeach
                        </datalist>
                        @error('responsable') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- Correo --}}
                    <div class="form-group">
                        <label class="form-label">Correo del responsable</label>
                        <input type="email" name="correo"
                               class="form-control @error('correo') is-invalid @enderror"
                               value="{{ old('correo', $plan->correo ?? '') }}"
                               placeholder="responsable@fycchilespa.cl" required
                               list="lista-correos">
                        <datalist id="lista-correos">
                            @foreach($responsables as $r)
                            <option value="{{ $r->email }}">{{ $r->nombre }}</option>
                            @endforeach
                        </datalist>
                        @error('correo') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- Estado (solo en edición) --}}
                    @if(isset($plan))
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="id_estado" class="form-control" required>
                            @foreach($estados as $id => $nombre)
                            <option value="{{ $id }}"
                                {{ old('id_estado', $plan->id_estado ?? 1) == $id ? 'selected' : '' }}>
                                {{ $nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Fecha inicio --}}
                    <div class="form-group">
                        <label class="form-label">Fecha de inicio</label>
                        <input type="date" name="inicio"
                               class="form-control @error('inicio') is-invalid @enderror"
                               value="{{ old('inicio', $plan->inicio ?? '') }}" required>
                        @error('inicio') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- Fecha término --}}
                    <div class="form-group">
                        <label class="form-label">Fecha de término</label>
                        <input type="date" name="termino"
                               class="form-control @error('termino') is-invalid @enderror"
                               value="{{ old('termino', $plan->termino ?? '') }}" required>
                        @error('termino') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                </div>

                {{-- Observaciones --}}
                <div class="form-group" style="margin-top:14px">
                    <label class="form-label">Observaciones <span style="color:var(--text-muted);font-weight:400">(opcional)</span></label>
                    <textarea name="observaciones" rows="2"
                              class="form-control"
                              placeholder="Notas adicionales, reprogramaciones, etc."
                              style="resize:vertical">{{ old('observaciones', $plan->observaciones ?? '') }}</textarea>
                </div>

            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('planificacion.index') }}" class="btn-cancelar">Cancelar</a>
            <button type="submit" class="btn-guardar">
                {{ isset($plan) ? '💾 Guardar cambios' : '✅ Crear planificación' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Autocompletar correo al seleccionar responsable
var responsables = @json($responsables->map(fn($r) => ['nombre' => $r->nombre, 'email' => $r->email]));

document.querySelector('[name="responsable"]').addEventListener('change', function() {
    var val = this.value;
    var match = responsables.find(r => r.nombre === val);
    if (match) {
        document.querySelector('[name="correo"]').value = match.email;
    }
});
</script>
@endpush
