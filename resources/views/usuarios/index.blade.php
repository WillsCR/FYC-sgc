@extends('layouts.app')

@section('title', 'Gestión de Usuarios')



@push('styles')
<style>
.usr-body { padding: 20px; max-width: 1200px; margin: 0 auto; }
.usr-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}
.usr-header h2 { font-size: 1.1rem; color: var(--navy); margin-bottom: 2px; }
.usr-header p  { font-size: .78rem; color: var(--text-muted); }

/* Tabla */
.usr-table-wrap {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
}
.usr-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.usr-table th {
    background: var(--navy);
    color: #fff;
    padding: 10px 14px;
    text-align: left;
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.usr-table td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--border);
    color: var(--text-secondary);
    vertical-align: middle;
}
.usr-table tr:last-child td { border-bottom: none; }
.usr-table tr:hover td { background: var(--surface-2); }

/* Avatar */
.usr-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 700;
    color: #fff; flex-shrink: 0;
}
.usr-info { display: flex; align-items: center; gap: 10px; }
.usr-name  { font-weight: 600; color: var(--text-primary); font-size: .85rem; }
.usr-email { font-size: .72rem; color: var(--text-muted); }

/* Badge perfil */
.perfil-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: .7rem;
    font-weight: 700;
    white-space: nowrap;
}
.badge-superadmin { background: #EFF6FF; color: #0D2B5E; }
.badge-admin      { background: #DBEAFE; color: #1D4ED8; }
.badge-trabajador { background: #F1F5F9; color: #64748B; }

/* Bloques activos */
.bloques-count {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: .75rem;
    color: var(--text-muted);
}
.bloques-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--navy);
}

/* Botones acción */
.btn-edit {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 12px;
    background: var(--navy);
    color: #fff;
    border-radius: var(--radius-sm);
    font-size: .72rem; font-weight: 600;
    text-decoration: none;
    transition: background .15s;
}
.btn-edit:hover { background: var(--navy-light); }

.btn-deactivate {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 12px;
    background: transparent;
    color: var(--danger);
    border: 1px solid var(--danger);
    border-radius: var(--radius-sm);
    font-size: .72rem; font-weight: 600;
    cursor: pointer;
    transition: all .15s;
}
.btn-deactivate:hover { background: var(--danger); color: #fff; }

.btn-nuevo {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px;
    background: var(--navy);
    color: #fff;
    border-radius: var(--radius-sm);
    font-size: .82rem; font-weight: 600;
    text-decoration: none;
    transition: background .15s;
}
.btn-nuevo:hover { background: var(--navy-light); }

/* Alertas */
.alert-ok  { background:#DCFCE7; border-left:3px solid #16A34A; color:#166534; padding:10px 14px; border-radius:var(--radius-sm); font-size:.82rem; margin-bottom:14px; }
.alert-err { background:#FCEBEB; border-left:3px solid #DC2626; color:#991B1B; padding:10px 14px; border-radius:var(--radius-sm); font-size:.82rem; margin-bottom:14px; }

@media (max-width: 768px) {
    .usr-body { padding: 12px; }
    .usr-table th:nth-child(3),
    .usr-table td:nth-child(3),
    .usr-table th:nth-child(4),
    .usr-table td:nth-child(4) { display: none; }
}
</style>
@endpush

@section('content')
<div class="usr-body">

    <div class="usr-header">
        <div>
            <h2>👥 Gestión de Usuarios</h2>
            <p>
                @if($actual->esSuperAdmin())
                    Todos los usuarios del sistema
                @else
                    Usuarios con perfil Trabajador
                @endif
                · {{ $usuarios->count() }} {{ $usuarios->count() === 1 ? 'usuario' : 'usuarios' }}
            </p>
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
                    <th>Módulos activos</th>
                    <th>Fecha ingreso</th>
                    <th style="width:140px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $usr)
                @php
                    $bloques = array_sum(array_map(fn($c) => (int)($usr->$c ?? 0), [
                        'bloque_sig','bloque_seguridad','bloque_ambiente','bloque_rrhh',
                        'bloque_abastecimiento','bloque_proyectos','bloque_gerencia',
                        'bloque_patio','bloque_calidad','bloque_docs_legales',
                        'bloque_formatos','bloque_listado_interes'
                    ]));
                    $badgeClass = match((int)$usr->id_perfil) {
                        1 => 'badge-superadmin',
                        2 => 'badge-admin',
                        default => 'badge-trabajador',
                    };
                    $iniciales = strtoupper(substr($usr->nombre ?? 'U', 0, 1) .
                        substr(strstr($usr->nombre ?? 'U ', ' '), 1, 1));
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
                            <span style="font-size:.75rem;color:#16A34A;font-weight:600">Todos</span>
                        @else
                            <div class="bloques-count">
                                @for($i = 0; $i < min($bloques, 5); $i++)
                                    <div class="bloques-dot"></div>
                                @endfor
                                {{ $bloques }} / 12
                            </div>
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
                            <form method="POST" action="{{ route('usuarios.destroy', $usr->id) }}"
                                  onsubmit="return confirm('¿Desactivar a {{ addslashes($usr->nombre ?? '') }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-deactivate">🚫</button>
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
    </div>

</div>
@endsection
