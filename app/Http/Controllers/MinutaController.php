<?php

namespace App\Http\Controllers;

use App\Services\PermisoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MinutaController extends Controller
{
    private const AREAS = [
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

    private const STATUS = [
        1 => 'En Proceso',
        2 => 'Cerrado',
        3 => 'Descartado',
    ];

    private const POR_PAGINA = 20;

    // ─────────────────────────────────────────────────────────────────────────
    // Helper: áreas donde el usuario tiene un permiso dado
    // ─────────────────────────────────────────────────────────────────────────
    private function areasConPermiso(int $idUsuario, string $columna): array
    {
        return DB::table('sgc_usuarios_permisos_area')
            ->where('id_usuario', $idUsuario)
            ->where($columna, 1)
            ->pluck('id_area')
            ->map(fn($v) => (int) $v)
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $orden     = $request->get('orden', 'desc');
        $orden     = in_array($orden, ['asc', 'desc']) ? $orden : 'desc';
        $porPagina = (int) $request->get('por_pagina', self::POR_PAGINA);
        $porPagina = in_array($porPagina, [10, 20, 50]) ? $porPagina : self::POR_PAGINA;

        $query = DB::table('sgc_minutas');

        $areasPermitidas = [];

        if (! $esAdmin) {
            $areasPermitidas = $this->areasConPermiso($usuario->id, 'ver_minutas');

            if (empty($areasPermitidas)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('id_area', $areasPermitidas);
            }
        }

        // Filtros
        if ($request->filled('area')) {
            $query->where('id_area', $request->area);
        }
        if ($request->filled('tipo_reunion')) {
            $query->where('tipo_reunion', 'like', '%' . $request->tipo_reunion . '%');
        }
        if ($request->filled('fecha_desde')) {
            $query->where('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha', '<=', $request->fecha_hasta);
        }

        $total = (clone $query)->count();

        $minutas = $query
            ->orderBy('fecha', $orden)
            ->paginate($porPagina)
            ->withQueryString();

        // Enriquecer con nombre de área y conteo de compromisos
        $minutas->getCollection()->transform(function ($m) {
            $m->area_nombre     = self::AREAS[$m->id_area] ?? 'Área ' . $m->id_area;
            $m->total_compromisos  = DB::table('sgc_minutas_compromisos')
                ->where('id_minuta', $m->id)->count();
            $m->compromisos_abiertos = DB::table('sgc_minutas_compromisos')
                ->where('id_minuta', $m->id)->where('status', 1)->count();
            $m->proxima_reunion = $m->proxima_reunion && $m->proxima_reunion !== '0000-00-00'
                ? $m->proxima_reunion : null;
            return $m;
        });

        // Áreas para el filtro
        if ($esAdmin) {
            $areasParaFiltro = self::AREAS;
        } else {
            $areasParaFiltro = array_filter(
                self::AREAS,
                fn($id) => in_array($id, $areasPermitidas, true),
                ARRAY_FILTER_USE_KEY
            );
        }

        // Áreas donde puede editar (para mostrar botones)
        $areasConEdicion = $esAdmin
            ? array_keys(self::AREAS)
            : $this->areasConPermiso($usuario->id, 'editar_minutas');

        return view('minutas.index', compact(
            'minutas', 'total', 'areasParaFiltro', 'areasConEdicion',
            'usuario', 'esAdmin', 'orden', 'porPagina'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        // Trabajador necesita editar_minutas en al menos un área
        if (! $esAdmin) {
            $areasConEdicion = $this->areasConPermiso($usuario->id, 'editar_minutas');
            if (empty($areasConEdicion)) abort(403);
        }

        $areas = $esAdmin
            ? self::AREAS
            : array_filter(
                self::AREAS,
                fn($id) => in_array($id, $this->areasConPermiso($usuario->id, 'editar_minutas'), true),
                ARRAY_FILTER_USE_KEY
            );

        $usuariosSelect = DB::table('sgc_usuarios')
            ->whereNotNull('nombre')->where('nombre', '!=', '')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'email']);

        return view('minutas.crear', compact('areas', 'usuariosSelect', 'usuario', 'esAdmin'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        if (! $esAdmin) {
            $areasConEdicion = $this->areasConPermiso($usuario->id, 'editar_minutas');
            if (empty($areasConEdicion)) abort(403);
        }

        $request->validate([
            'id_area'      => ['required', 'integer'],
            'empresa'      => ['required', 'string', 'max:100'],
            'tipo_reunion' => ['required', 'string', 'max:80'],
            'lugar'        => ['required', 'string', 'max:150'],
            'fecha'        => ['required', 'date'],
            'hora_inicio'  => ['required'],
            'hora_fin'     => ['required'],
        ]);

        // Validar que el trabajador tenga permiso en el área seleccionada
        if (! $esAdmin) {
            $areasConEdicion = $this->areasConPermiso($usuario->id, 'editar_minutas');
            if (! in_array((int) $request->id_area, $areasConEdicion, true)) abort(403);
        }

        $idMinuta = DB::table('sgc_minutas')->insertGetId([
            'id_area'           => $request->id_area,
            'empresa'           => $request->empresa,
            'tipo_reunion'      => $request->tipo_reunion,
            'lugar'             => $request->lugar,
            'fecha'             => $request->fecha,
            'hora_inicio'       => $request->hora_inicio,
            'hora_fin'          => $request->hora_fin,
            'proxima_reunion'   => $request->filled('proxima_reunion') ? $request->proxima_reunion : null,
            'id_usuario_creador'=> $usuario->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Guardar convocados
        $this->guardarConvocados($idMinuta, $request, $usuario->id);

        // Guardar compromisos
        $this->guardarCompromisos($idMinuta, $request, $usuario->id);

        return redirect()->route('minutas.index')
            ->with('ok', 'Minuta creada correctamente.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────────────
    public function show(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $minuta = DB::table('sgc_minutas')->where('id', $id)->first();
        if (! $minuta) abort(404);

        if (! $esAdmin) {
            $areasPermitidas = $this->areasConPermiso($usuario->id, 'ver_minutas');
            if (! in_array((int) $minuta->id_area, $areasPermitidas, true)) abort(403);
        }

        $minuta->area_nombre = self::AREAS[$minuta->id_area] ?? 'Área ' . $minuta->id_area;
        $minuta->proxima_reunion = $minuta->proxima_reunion && $minuta->proxima_reunion !== '0000-00-00'
            ? $minuta->proxima_reunion : null;

        $convocados = DB::table('sgc_minutas_convocados')
            ->leftJoin('sgc_usuarios', 'sgc_minutas_convocados.id_usuario', '=', 'sgc_usuarios.id')
            ->where('sgc_minutas_convocados.id_minuta', $id)
            ->select(
                'sgc_minutas_convocados.*',
                DB::raw("COALESCE(sgc_usuarios.nombre, sgc_minutas_convocados.nom_ape) as nombre_display")
            )
            ->get();

        $compromisos = DB::table('sgc_minutas_compromisos')
            ->where('id_minuta', $id)
            ->orderBy('item')
            ->get()
            ->map(function ($c) {
                $c->status_nombre = self::STATUS[$c->status] ?? '—';
                return $c;
            });

        $areasConEdicion = $esAdmin
            ? array_keys(self::AREAS)
            : $this->areasConPermiso($usuario->id, 'editar_minutas');

        $puedeEditar = in_array((int) $minuta->id_area, $areasConEdicion, true);

        return view('minutas.show', compact(
            'minuta', 'convocados', 'compromisos',
            'usuario', 'esAdmin', 'puedeEditar'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────────────
    public function edit(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $minuta = DB::table('sgc_minutas')->where('id', $id)->first();
        if (! $minuta) abort(404);

        if (! $esAdmin) {
            $areasConEdicion = $this->areasConPermiso($usuario->id, 'editar_minutas');
            if (! in_array((int) $minuta->id_area, $areasConEdicion, true)) abort(403);
        }

        $minuta->proxima_reunion = $minuta->proxima_reunion && $minuta->proxima_reunion !== '0000-00-00'
            ? $minuta->proxima_reunion : null;

        $areas = $esAdmin
            ? self::AREAS
            : array_filter(
                self::AREAS,
                fn($id) => in_array($id, $this->areasConPermiso($usuario->id, 'editar_minutas'), true),
                ARRAY_FILTER_USE_KEY
            );

        $convocados = DB::table('sgc_minutas_convocados')
            ->where('id_minuta', $id)
            ->get();

        $compromisos = DB::table('sgc_minutas_compromisos')
            ->where('id_minuta', $id)
            ->orderBy('item')
            ->get();

        $usuariosSelect = DB::table('sgc_usuarios')
            ->whereNotNull('nombre')->where('nombre', '!=', '')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'email']);

        return view('minutas.editar', compact(
            'minuta', 'areas', 'convocados', 'compromisos',
            'usuariosSelect', 'usuario', 'esAdmin'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────────────
    public function update(Request $request, int $id)
    {
        $usuario = PermisoService::usuarioActual();
        $esAdmin = $usuario->esAdmin();

        $minuta = DB::table('sgc_minutas')->where('id', $id)->first();
        if (! $minuta) abort(404);

        if (! $esAdmin) {
            $areasConEdicion = $this->areasConPermiso($usuario->id, 'editar_minutas');
            if (! in_array((int) $minuta->id_area, $areasConEdicion, true)) abort(403);
        }

        $request->validate([
            'id_area'      => ['required', 'integer'],
            'empresa'      => ['required', 'string', 'max:100'],
            'tipo_reunion' => ['required', 'string', 'max:80'],
            'lugar'        => ['required', 'string', 'max:150'],
            'fecha'        => ['required', 'date'],
            'hora_inicio'  => ['required'],
            'hora_fin'     => ['required'],
        ]);

        DB::table('sgc_minutas')->where('id', $id)->update([
            'id_area'         => $request->id_area,
            'empresa'         => $request->empresa,
            'tipo_reunion'    => $request->tipo_reunion,
            'lugar'           => $request->lugar,
            'fecha'           => $request->fecha,
            'hora_inicio'     => $request->hora_inicio,
            'hora_fin'        => $request->hora_fin,
            'proxima_reunion' => $request->filled('proxima_reunion') ? $request->proxima_reunion : null,
            'updated_at'      => now(),
        ]);

        // Reemplazar convocados y compromisos
        DB::table('sgc_minutas_convocados')->where('id_minuta', $id)->delete();
        DB::table('sgc_minutas_compromisos')->where('id_minuta', $id)->delete();

        $this->guardarConvocados($id, $request, $usuario->id);
        $this->guardarCompromisos($id, $request, $usuario->id);

        return redirect()->route('minutas.show', $id)
            ->with('ok', 'Minuta actualizada correctamente.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy(int $id)
    {
        $usuario = PermisoService::usuarioActual();
        if (! $usuario->esAdmin()) abort(403);

        DB::table('sgc_minutas_compromisos')->where('id_minuta', $id)->delete();
        DB::table('sgc_minutas_convocados')->where('id_minuta', $id)->delete();
        DB::table('sgc_minutas')->where('id', $id)->delete();

        return redirect()->route('minutas.index')
            ->with('ok', 'Minuta eliminada correctamente.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ─────────────────────────────────────────────────────────────────────────

    private function guardarConvocados(int $idMinuta, Request $request, int $idUsuarioCreador): void
    {
        $empresas  = $request->input('conv_empresa', []);
        $usuarios  = $request->input('conv_id_usuario', []);
        $nomApes   = $request->input('conv_nom_ape', []);
        $cargos    = $request->input('conv_cargo', []);

        foreach ($empresas as $i => $empresa) {
            $nomApe   = $nomApes[$i]  ?? '';
            $idUsuario = isset($usuarios[$i]) && $usuarios[$i] !== '' ? (int) $usuarios[$i] : null;
            $cargo    = $cargos[$i]   ?? '';

            // Si hay id_usuario, obtener nombre desde sgc_usuarios
            if ($idUsuario) {
                $u = DB::table('sgc_usuarios')->where('id', $idUsuario)->value('nombre');
                $nomApe = $u ?? $nomApe;
            }

            if ($nomApe === '' && $idUsuario === null) continue; // fila vacía

            DB::table('sgc_minutas_convocados')->insert([
                'id_minuta'  => $idMinuta,
                'empresa'    => $empresa ?? '',
                'id_usuario' => $idUsuario,
                'nom_ape'    => $nomApe,
                'cargo'      => $cargo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function guardarCompromisos(int $idMinuta, Request $request, int $idUsuarioCreador): void
    {
        $descripciones = $request->input('comp_descripcion', []);
        $responsables  = $request->input('comp_responsable', []);
        $fechas        = $request->input('comp_inicio_compromiso', []);
        $statuses      = $request->input('comp_status', []);
        $observaciones = $request->input('comp_observaciones', []);

        $item = 1;
        foreach ($descripciones as $i => $descripcion) {
            if (trim($descripcion) === '') continue;

            DB::table('sgc_minutas_compromisos')->insert([
                'id_minuta'          => $idMinuta,
                'item'               => $item++,
                'descripcion'        => $descripcion,
                'responsable'        => $responsables[$i] ?? '',
                'inicio_compromiso'  => isset($fechas[$i]) && $fechas[$i] !== '' ? $fechas[$i] : null,
                'status'             => isset($statuses[$i]) && $statuses[$i] !== '' ? (int) $statuses[$i] : 1,
                'observaciones'      => $observaciones[$i] ?? '',
                'id_usuario'         => $idUsuarioCreador,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }
}
