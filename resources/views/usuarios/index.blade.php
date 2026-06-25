@extends('layouts.app')
@section('title', 'Gestión de Usuarios')

@push('styles')
<style>
.usr-body { padding: 20px; max-width: 1200px; margin: 0 auto; }
.usr-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
}
.usr-header h2 { font-size: 1.1rem; color: var(--navy); margin-bottom: 2px; }
.usr-header p  { font-size: .78rem; color: var(--text-muted); }
.usr-table-wrap {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-md); overflow: hidden;
}
.usr-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.usr-table th {
    background: var(--navy); color: #fff;
    padding: 10px 14px; text-align: left;
    font-size: .7rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .05em;
}
.usr-table td {
    padding: 10px 14px; border-bottom: 1px solid var(--border);
    color: var(--text-secondary); vertical-align: middle;
}
.usr-table tr:last-child td { border-bottom: none; }
.usr-table tr:hover td { background: var(--surface-2); }
.usr-info { display: flex; align-items: center; gap: 10px; }
.usr-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.usr-name  { font-weight: 600; color: var(--text-primary); font-size: .85rem; }
.usr-email { font-size: .72rem; color: var(--text-muted); }
.perfil-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 12px;
    font-size: .7rem; font-weight: 700; white-space: nowrap;
}
.badge-superadmin { background: #EFF6FF; color: #0D2B5E; }
.badge-admin      { background: #DBEAFE; color: #1D4ED8; }
.badge-trabajador { background: #F1F5F9; color: #64748B; }

/* Tags de área */
.area-tags { display: flex; flex-wrap: wrap; gap: 4px; }
.area-tag {
    display: inline-block; padding: 2px 8px;
    background: #EFF6FF; color: #1D4ED8;
    border-radius: 10px; font-size: .65rem; font-weight: 600;
    white-space: nowrap;
}
.area-tag-vacia { font-size: .72rem; color: var(--text-muted); font-style: italic; }

.btn-edit {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 12px; background: var(--navy); color: #fff;
    border-radius: var(--radius-sm); font-size: .72rem; font-weight: 600;
    text-decoration: none; transition: background .15s;
}
.btn-edit:hover { background: var(--navy-light); }
.btn-deactivate {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 12px; background: transparent; color: var(--danger);
    border: 1px solid var(--danger); border-radius: var(--radius-sm);
    font-size: .72rem; font-weight: 600; cursor: pointer; transition: all .15s;
}
.btn-deactivate:hover { background: var(--danger); color: #fff; }
.btn-nuevo {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px; background: var(--navy); color: #fff;
    border-radius: var(--radius-sm); font-size: .82rem; font-weight: 600;
    text-decoration: none; transition: background .15s;
}
.btn-nuevo:hover { background: var(--navy-light); }
.alert-ok  { background:#DCFCE7; border-left:3px solid #16A34A; color:#166534; padding:10px 14px; border-radius:var(--radius-sm); font-size:.82rem; margin-bottom:14px; }
.alert-err { background:#FCEBEB; border-left:3px solid #DC2626; color:#991B1B; padding:10px 14px; border-radius:var(--radius-sm); font-size:.82rem; margin-bottom:14px; }

.usr-search-wrap {
    position: relative; flex: 1; max-width: 340px;
}
.usr-search-wrap i {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: var(--text-muted); font-size: .85rem; pointer-events: none;
}
.usr-search {
    width: 100%; padding: 8px 12px 8px 32px;
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: .82rem; color: var(--text-primary);
    background: var(--surface); outline: none; transition: border-color .15s;
    box-sizing: border-box;
}
.usr-search:focus { border-color: var(--navy); }
.usr-no-results {
    text-align: center; padding: 32px; color: var(--text-muted);
    font-size: .85rem; display: none;
}
#usr-count-live { font-weight: 600; color: var(--navy); }

@media (max-width: 900px) {
    .usr-table th:nth-child(4), .usr-table td:nth-child(4) { display: none; }
}
@media (max-width: 640px) {
    .usr-body { padding: 12px; }
    .usr-table th:nth-child(3), .usr-table td:nth-child(3) { display: none; }
}
</style>
@endpush

@section('content')
<div class="usr-body">

    <div class="usr-header">
        <div>
            <h2>👥 Gestión de Usuarios</h2>
            <p>
                @if($actual->esSuperAdmin()) Todos los usuarios del sistema
                @else Usuarios con perfil Trabajador
                @endif
                · <span id="usr-count-live">{{ $usuarios->count() }}</span> {{ $usuarios->count() === 1 ? 'usuario' : 'usuarios' }}
            </p>
        </div>
        <div class="usr-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="usr-search" class="usr-search" placeholder="Buscar por nombre, email o área…" autocomplete="off">
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn-nuevo">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo usuario
        </a>
    </div>

    @if(session('ok'))
        <div class="alert-ok">✅ {{ session('ok') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-err">❌ {{ $errors->first() }}</div>
    @endif

    <div class="usr-table-wrap">
        <table class="usr-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Perfil</th>
                    <th>Áreas asignadas</th>
                    <th>Fecha ingreso</th>
                    <th style="width:130px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usr)
                @php
                    $badgeClass = match((int)$usr->id_perfil) {
                        1 => 'badge-superadmin',
                        2 => 'badge-admin',
                        default => 'badge-trabajador',
                    };
                    $iniciales = strtoupper(
                        substr($usr->nombre ?? 'U', 0, 1) .
                        substr(strstr($usr->nombre ?? 'U ', ' '), 1, 1)
                    );
                @endphp
                <tr>
                    <td>
                        <div class="usr-info">
                            <div class="usr-avatar" style="background:{{ $usr->colorPerfil() }}">
                                {{ $iniciales }}
                            </div>
                            <div>
                                <div class="usr-name">{{ $usr->nombre ?? '—' }}</div>
                                <div class="usr-email">{{ $usr->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="perfil-badge {{ $badgeClass }}">
                            {{ $usr->nombrePerfil() }}
                        </span>
                    </td>
                    <td>
                        @if($usr->esSuperAdmin())
                            <span style="font-size:.75rem;color:#16A34A;font-weight:600">Todas</span>
                        @elseif($usr->areas->count() > 0)
                            <div class="area-tags">
                                @foreach($usr->areas as $area)
                                    <span class="area-tag">{{ $area }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="area-tag-vacia">Sin área asignada</span>
                        @endif
                    </td>
                    <td style="font-size:.75rem">
                        {{ $usr->fecha_ingreso ? \Carbon\Carbon::parse($usr->fecha_ingreso)->format('d/m/Y') : '—' }}
                    </td>
                    <td>
                        <div style="display:flex;gap:5px">
                            <a href="{{ route('usuarios.edit', $usr->id) }}" class="btn-edit">
                                ✏️ Editar
                            </a>
                            @if($actual->esSuperAdmin() && $usr->id !== $actual->id && (int)$usr->id_perfil !== 1)
                            <form id="form-del-usr-{{ $usr->id }}" method="POST" action="{{ route('usuarios.destroy', $usr->id) }}">
                                @csrf @method('DELETE')
                                <button type="button" class="btn-deactivate"
                                    onclick="sgcConfirm('¿Desactivar a <strong>{{ addslashes($usr->nombre ?? '') }}</strong>?', () => document.getElementById('form-del-usr-{{ $usr->id }}').submit(), {title:'Desactivar usuario', icon:'<i class=\'fa-solid fa-user-slash\'></i>', btnText:'Desactivar', btnColor:'#d97706', danger:false})">🚫</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:32px;color:var(--text-muted)">
                        No hay usuarios registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="usr-no-results" id="usr-no-results">
            <i class="fa-solid fa-user-slash" style="font-size:1.4rem;margin-bottom:8px;display:block"></i>
            No se encontraron usuarios que coincidan con la búsqueda.
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    const input    = document.getElementById('usr-search');
    const rows     = document.querySelectorAll('.usr-table tbody tr');
    const noResult = document.getElementById('usr-no-results');
    const counter  = document.getElementById('usr-count-live');
    const total    = rows.length;

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let visible = 0;

        rows.forEach(function (tr) {
            const text = tr.textContent.toLowerCase();
            const show = !q || text.includes(q);
            tr.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        counter.textContent = q ? visible : total;
        noResult.style.display = (q && visible === 0) ? 'block' : 'none';
    });
})();
</script>
@endpush
@endsection
