@extends('layouts.app')
@section('title', 'Panel principal')

@section('content')
<div class="panel-body">

    @if(session('sin_permiso_carpeta'))
    <div style="background:#FEF2F2;border-left:4px solid #DC2626;color:#991B1B;
                padding:11px 16px;border-radius:4px;margin-bottom:16px;font-size:.84rem;display:flex;align-items:center;gap:8px">
        🔒 {{ session('sin_permiso_carpeta') }}
    </div>
    @endif

    <div class="panel-welcome">
        <h2>Bienvenido, {{ session('usuario_nombre') }}</h2>
        <p>{{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}
            @if(session('es_superadmin'))
                · <span style="color:var(--blue-accent);font-weight:500">Super Administrador</span>
            @elseif(session('es_admin'))
                · <span style="color:var(--blue-accent);font-weight:500">Administrador</span>
            @endif
        </p>
    </div>

    {{-- Bloques --}}
    @if(count($bloques) > 0)
        <div class="section-label">
            Módulos del sistema
            @if(session('es_superadmin'))
                <button onclick="abrirModalCrearModulo()" style="float:right;padding:6px 12px;background:var(--navy);color:#fff;border:none;border-radius:4px;font-size:.75rem;cursor:pointer">➕ Nuevo módulo</button>
            @endif
        </div>
        <div class="bloques-grid">
            @foreach($bloques as $bloque)
            <a href="{{ route('carpetas.show', $bloque['carpeta_id']) }}"
               class="bloque"
               style="border-top-color: {{ $bloque['color'] }}; text-decoration:none">
                <div class="bloque-icon-wrap" style="background: {{ $bloque['color'] }}18">
                    <span style="font-size:1.5rem;line-height:1">{{ $bloque['emoji'] }}</span>
                </div>
                <div class="bloque-title">{{ $bloque['titulo'] }}</div>
                <div class="bloque-badge">{{ $bloque['badge'] }}</div>
            </a>
            @endforeach
            @if(session('es_admin') || session('es_superadmin'))
            <button onclick="abrirModalCrearSubmodulo({{ $bloque['id'] }})" class="sub-bloque" style="background-color:#D1D5DB;color:#1F2937;border:none;cursor:pointer;font-weight:500">
                ➕ Nuevo submódulo
            </button>
            @endif
        </div>
    @else
        <div style="padding:40px 0;text-align:center">
            <div style="font-size:2.5rem;margin-bottom:12px">🔒</div>
            <p style="color:var(--text-muted);font-size:.9rem">No tienes módulos asignados. Contacta al administrador.</p>
        </div>
    @endif

    {{-- Resumen --}}
    <div class="section-label">Resumen del sistema</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value {{ $stats['cumplimiento'] >= 80 ? 'success' : ($stats['cumplimiento'] >= 50 ? 'warning' : 'danger') }}">
                {{ $stats['cumplimiento'] }}%
            </div>
            <div class="stat-label">Cumplimiento global</div>
        </div>
        <div class="stat-card">
            <div class="stat-value {{ $stats['pendientes'] > 5 ? 'danger' : ($stats['pendientes'] > 0 ? 'warning' : 'success') }}">
                {{ $stats['pendientes'] }}
            </div>
            <div class="stat-label">Actividades pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value success">{{ $stats['cerradas'] }}</div>
            <div class="stat-label">Actividades cerradas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['minutas_mes'] }}</div>
            <div class="stat-label">Minutas este mes</div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<style>
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200; align-items: center; justify-content: center; }
.modal-overlay.visible { display: flex; }
.modal { background: var(--surface); border-radius: 8px; padding: 28px; width: 100%; max-width: 500px; box-shadow: 0 24px 64px rgba(0,0,0,.2); }
.modal-title { font-size: 1rem; font-weight: 700; color: var(--navy); margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
.modal-field { margin-bottom: 16px; }
.modal-label { display: block; font-size: .8rem; font-weight: 600; color: var(--navy); margin-bottom: 8px; }
.modal-input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 4px; font-size: .85rem; outline: none; box-sizing: border-box; }
.modal-input:focus { border-color: var(--blue-accent); }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; }
.btn-cancel { padding: 9px 18px; border-radius: 4px; border: 1px solid var(--border); background: transparent; font-size: .82rem; cursor: pointer; color: var(--text-secondary); font-weight: 500; }
.btn-cancel:hover { background: var(--surface-2); }
.btn-submit { padding: 9px 18px; border-radius: 4px; border: none; background: var(--navy); color: #fff; font-size: .82rem; cursor: pointer; font-weight: 600; }
.btn-submit:hover { background: #0a2147; }
.icon-btn {
    border: 2px solid var(--border);
    background: var(--surface-2);
    border-radius: 6px;
    padding: 10px;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.icon-btn:hover {
    border-color: var(--blue-accent);
    transform: scale(1.1);
}
.icon-btn.selected {
    border-color: var(--navy);
    background: #EFF6FF;
    transform: scale(1.15);
}
</style>

{{-- Modal: Crear módulo --}}
<div class="modal-overlay" id="modal-crear-modulo">
    <div class="modal" style="max-width:600px">
        <div class="modal-title">➕ Nuevo módulo</div>
        <form id="form-crear-modulo">
            <div class="modal-field">
                <label class="modal-label">Nombre del módulo</label>
                <input type="text" name="descripcion" id="modulo-nombre" class="modal-input" placeholder="Ej: Auditoría Interna" required>
            </div>
            <div class="modal-field">
                <label class="modal-label">Color (hexadecimal o nombre)</label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="color" id="modulo-color" class="modal-input" placeholder="#0D2B5E" style="flex:1">
                    <input type="color" id="modulo-color-picker" style="width:45px;height:38px;border:1px solid var(--border);border-radius:4px;cursor:pointer">
                </div>
            </div>
            <div class="modal-field">
                <label class="modal-label">Icono</label>
                <div style="display:grid;grid-template-columns:repeat(8,1fr);gap:8px;margin-bottom:12px;max-height:150px;overflow-y:auto;padding:8px">
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📋')">📋</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🌿')">🌿</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🛡️')">🛡️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🏗️')">🏗️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('👨‍💼')">👨‍💼</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🏢')">🏢</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📈')">📈</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('💰')">💰</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('⚠️')">⚠️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📏')">📏</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('✅')">✅</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🦺')">🦺</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📝')">📝</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📁')">📁</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🎓')">🎓</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📊')">📊</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🔍')">🔍</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('♻️')">♻️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🌱')">🌱</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🌍')">🌍</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('⚙️')">⚙️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🏥')">🏥</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('⚖️')">⚖️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('👥')">👥</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📚')">📚</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('⛏️')">⛏️</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🔐')">🔐</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('📱')">📱</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🎯')">🎯</button>
                    <button type="button" class="icon-btn" onclick="seleccionarIcono('🚀')">🚀</button>
                </div>
                <input type="hidden" name="icono" id="modulo-icono" value="📋">
                <div style="font-size:.8rem;color:var(--text-muted);text-align:center">Icono seleccionado: <span id="icono-preview" style="font-size:1.2rem">📋</span></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalCrearModulo()">Cancelar</button>
                <button type="submit" class="btn-submit">Crear módulo</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Crear submódulo --}}
<div class="modal-overlay" id="modal-crear-submodulo">
    <div class="modal">
        <div class="modal-title">➕ Nuevo submódulo</div>
        <form id="form-crear-submodulo">
            <input type="hidden" name="modulo_id" id="submodulo-modulo-id">
            <div class="modal-field">
                <label class="modal-label">Nombre del submódulo</label>
                <input type="text" name="descripcion" id="submodulo-nombre" class="modal-input" placeholder="Ej: Auditorías 2025" required>
            </div>
            <div class="modal-field">
                <label class="modal-label">Color (hexadecimal o nombre)</label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="color" id="submodulo-color" class="modal-input" placeholder="#15803D" style="flex:1">
                    <input type="color" id="submodulo-color-picker" style="width:45px;height:38px;border:1px solid var(--border);border-radius:4px;cursor:pointer">
                </div>
            </div>
            <div class="modal-field">
                <label class="modal-label">Icono (emoji o clase)</label>
                <input type="text" name="icono" id="submodulo-icono" class="modal-input" placeholder="🔍 o fa-search">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModalCrearSubmodulo()">Cancelar</button>
                <button type="submit" class="btn-submit">Crear submódulo</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalCrearModulo() {
    document.getElementById('form-crear-modulo').reset();
    document.getElementById('modulo-color-picker').value = '#0D2B5E';
    document.getElementById('modulo-icono').value = '📋';
    document.getElementById('icono-preview').textContent = '📋';
    
    // Marcar el primer icono como seleccionado
    document.querySelectorAll('#modal-crear-modulo .icon-btn').forEach((btn, index) => {
        if (index === 0) {
            btn.classList.add('selected');
        } else {
            btn.classList.remove('selected');
        }
    });
    
    document.getElementById('modal-crear-modulo').classList.add('visible');
    setTimeout(() => document.getElementById('modulo-nombre').focus(), 100);
}

function cerrarModalCrearModulo() {
    document.getElementById('modal-crear-modulo').classList.remove('visible');
}

function seleccionarIcono(icono) {
    // Actualizar el campo oculto
    document.getElementById('modulo-icono').value = icono;
    
    // Actualizar preview
    document.getElementById('icono-preview').textContent = icono;
    
    // Actualizar estilo de botones
    document.querySelectorAll('#modal-crear-modulo .icon-btn').forEach(btn => {
        if (btn.textContent.trim() === icono) {
            btn.classList.add('selected');
        } else {
            btn.classList.remove('selected');
        }
    });
    
    return false;
}

function abrirModalCrearSubmodulo(moduloId) {
    document.getElementById('form-crear-submodulo').reset();
    document.getElementById('submodulo-modulo-id').value = moduloId;
    document.getElementById('submodulo-color-picker').value = '#15803D';
    document.getElementById('modal-crear-submodulo').classList.add('visible');
    setTimeout(() => document.getElementById('submodulo-nombre').focus(), 100);
}

function cerrarModalCrearSubmodulo() {
    document.getElementById('modal-crear-submodulo').classList.remove('visible');
}

// Sincronizar color pickers
document.getElementById('modulo-color').addEventListener('change', function() {
    if (this.match(/^#[0-9A-F]{6}$/i)) {
        document.getElementById('modulo-color-picker').value = this.value;
    }
});
</script>

@endpush
