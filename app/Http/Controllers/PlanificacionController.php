<?php

namespace App\Http\Controllers;

use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanificacionController extends Controller
{
    private const AREAS = [
        0  => 'Sin área',
        1  => 'Recursos Humanos',
        2  => 'Seguridad y Salud en el Trabajo',
        3  => 'Abastecimiento y Finanzas',
        4  => 'Contrato Pozos',
        5  => 'Medio Ambiente',
        6  => 'Control SGI',
        7  => 'SGI Gestión',
        8  => 'Patios e Infraestructura',
        9  => 'Gerencia de Operaciones',
        10 => 'Gerencia General',
    ];

    private const ESTADOS = [
        0 => 'Sin estado',
        1 => 'Pendiente',
        2 => 'Cerrado',
    ];

    private const POR_PAGINA = 20;

    public function index(Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        // ── Parámetros de orden y paginación ────────────────────────────
        $orden    = $request->get('orden', 'desc'); // desc = más recientes primero
        $orden    = in_array($orden, ['asc', 'desc']) ? $orden : 'desc';
        $porPagina = (int) $request->get('por_pagina', self::POR_PAGINA);
        $porPagina = in_array($porPagina, [10, 20, 50]) ? $porPagina : self::POR_PAGINA;

        // ── Query base ───────────────────────────────────────────────────
        $query = DB::table('sgc_planificaciones');

        if (! $esAdmin) {
            $query->where('correo', $usuario->email);
        }

        // Filtros
        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }
        if ($request->filled('estado')) {
            $query->where('id_estado', $request->estado);
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('actividades', 'like', '%' . $request->buscar . '%')
                  ->orWhere('responsable',  'like', '%' . $request->buscar . '%');
            });
        }

        // ── Total para stats (antes de paginar) ──────────────────────────
        $queryStats = clone $query;
        $total      = $queryStats->count();
        $pendientes = (clone $query)->where('id_estado', 1)->count();
        $cerradas   = (clone $query)->where('id_estado', 2)->count();

        // ── Ordenar y paginar ────────────────────────────────────────────
        $planificaciones = $query
            ->orderBy('termino', $orden)
            ->paginate($porPagina)
            ->withQueryString(); // mantiene los filtros en los links de paginación

        // ── Enriquecer con semáforo ──────────────────────────────────────
        $hoy = Carbon::today();
        $vencidas = 0;

        $planificaciones->getCollection()->transform(function ($p) use ($hoy, &$vencidas) {
            $p->area_nombre    = self::AREAS[$p->area]      ?? 'Área ' . $p->area;
            $p->estado_nombre  = self::ESTADOS[$p->id_estado] ?? '—';
            $p->semaforo       = $this->calcularSemaforo($p, $hoy);
            $p->dias_restantes = $p->termino
                ? $hoy->diffInDays(Carbon::parse($p->termino), false)
                : null;
            if ($p->semaforo === 'rojo' && (int) $p->id_estado === 1) {
                $vencidas++;
            }
            return $p;
        });

        $stats = [
            'total'      => $total,
            'pendientes' => $pendientes,
            'cerradas'   => $cerradas,
            'vencidas'   => $vencidas,
        ];

        $areas    = self::AREAS;
        $estados  = self::ESTADOS;

        return view('planificacion.index', compact(
            'planificaciones', 'stats', 'areas', 'estados',
            'usuario', 'esAdmin', 'orden', 'porPagina'
        ));
    }

    public function create()
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) abort(403);

        $areas = self::AREAS;
        unset($areas[0]);

        $responsables = DB::table('sgc_usuarios')
            ->whereNotNull('nombre')->where('nombre', '!=', '')
            ->orderBy('nombre')->get(['id', 'nombre', 'email']);

        return view('planificacion.crear', compact('areas', 'responsables', 'usuario'));
    }

    public function store(Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) abort(403);

        $request->validate([
            'area'        => ['required', 'integer'],
            'responsable' => ['required', 'string', 'max:150'],
            'actividades' => ['required', 'string'],
            'inicio'      => ['required', 'date'],
            'termino'     => ['required', 'date', 'after_or_equal:inicio'],
            'correo'      => ['required', 'email'],
        ], [
            'termino.after_or_equal' => 'El término debe ser igual o posterior al inicio.',
        ]);

        DB::table('sgc_planificaciones')->insert([
            'area'          => $request->area,
            'responsable'   => $request->responsable,
            'actividades'   => $request->actividades,
            'inicio'        => $request->inicio,
            'termino'       => $request->termino,
            'id_estado'     => 1,
            'observaciones' => $request->observaciones ?? '',
            'correo'        => $request->correo,
        ]);

        return redirect()->route('planificacion.index')
            ->with('ok', 'Planificación creada correctamente.');
    }

    public function edit(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $plan = DB::table('sgc_planificaciones')->where('id', $id)->first();
        if (! $plan) abort(404);
        if (! $esAdmin && $plan->correo !== $usuario->email) abort(403);

        $areas = self::AREAS;
        unset($areas[0]);
        $estados  = self::ESTADOS;
        unset($estados[0]);

        $responsables = DB::table('sgc_usuarios')
            ->whereNotNull('nombre')->where('nombre', '!=', '')
            ->orderBy('nombre')->get(['id', 'nombre', 'email']);

        return view('planificacion.editar', compact(
            'plan', 'areas', 'estados', 'responsables', 'usuario', 'esAdmin'
        ));
    }

    public function update(Request $request, int $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $plan = DB::table('sgc_planificaciones')->where('id', $id)->first();
        if (! $plan) abort(404);
        if (! $esAdmin && $plan->correo !== $usuario->email) abort(403);

        $request->validate([
            'area'        => ['required', 'integer'],
            'responsable' => ['required', 'string', 'max:150'],
            'actividades' => ['required', 'string'],
            'inicio'      => ['required', 'date'],
            'termino'     => ['required', 'date', 'after_or_equal:inicio'],
            'correo'      => ['required', 'email'],
            'id_estado'   => ['required', 'integer', 'in:1,2'],
        ]);

        DB::table('sgc_planificaciones')->where('id', $id)->update([
            'area'          => $request->area,
            'responsable'   => $request->responsable,
            'actividades'   => $request->actividades,
            'inicio'        => $request->inicio,
            'termino'       => $request->termino,
            'id_estado'     => $request->id_estado,
            'observaciones' => $request->observaciones ?? '',
            'correo'        => $request->correo,
        ]);

        return redirect()->route('planificacion.index')
            ->with('ok', 'Planificación actualizada correctamente.');
    }

    public function cerrar(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) abort(403);

        DB::table('sgc_planificaciones')->where('id', $id)->update(['id_estado' => 2]);

        return back()->with('ok', 'Planificación cerrada correctamente.');
    }

    private function calcularSemaforo(object $p, Carbon $hoy): string
    {
        if ((int) $p->id_estado === 2) return 'verde';
        if (! $p->termino) return 'gris';

        $dias = $hoy->diffInDays(Carbon::parse($p->termino), false);

        if ($dias < 0)   return 'rojo';
        if ($dias <= 14) return 'amarillo';
        return 'verde';
    }
}
