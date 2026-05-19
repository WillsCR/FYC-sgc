<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 3 — Estructura inicial de módulos y submódulos en sgc_carpetas3
 * Origen: files_table_content.sql
 *
 * Inserta los 8 módulos raíz y sus submódulos de producción.
 * Solo se ejecuta si la tabla está vacía (idempotente).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sgc_carpetas3')) {
            return; // La migración anterior debe ejecutarse primero
        }

        // Solo sembrar si no hay módulos raíz ya cargados
        if (DB::table('sgc_carpetas3')->where('nivel', 0)->exists()) {
            return;
        }

        // ── Módulos principales (nivel 0) ───────────────────────────────
        $modulos = [
            'Sistema Integrado Gestión',
            'Control Medio Ambiente',
            'Control Seguridad y Salud en el Trabajo',
            'Control Abastecimiento e Infraestructura',
            'Control Recursos Humanos',
            'Control Gerencia',
            'Control Proyectos',
            'Control Finanzas',
        ];

        $ids = [];
        foreach ($modulos as $descripcion) {
            $ids[$descripcion] = DB::table('sgc_carpetas3')->insertGetId([
                'descripcion' => $descripcion,
                'id_padre'    => 0,
                'nivel'       => 0,
                'creada_el'   => now(),
            ]);
        }

        // ── Submódulos por módulo ───────────────────────────────────────
        $submodulos = [
            'Sistema Integrado Gestión' => [
                'No Conformidades',
                'Instrumentos de Medición Certificación de Calidad',
                'Certificados de Calidad',
                'Certificados de EPP',
                'Formatos SIG',
                'Documentos del SIG',
                'Capacitaciones',
                'Informes',
                'Auditorías',
            ],
            'Control Medio Ambiente' => [
                'Sustancias y Residuos Peligrosos',
                'Control de Recursos',
                'Huellas de Carbono',
                'Capacitaciones',
                'Informes',
                'Formatos',
                'Documentos',
                'Control Operativo',
            ],
            'Control Seguridad y Salud en el Trabajo' => [
                'Protocolo Minsal',
                'DS 44',
                'CPHS',
                'Informes',
                'Capacitaciones',
                'Formatos',
                'Documentos',
                'Control Operativo',
            ],
            'Control Abastecimiento e Infraestructura' => [
                'Control Plan e Infraestructura',
                'Informes',
                'Formatos',
                'Documentos',
                'Capacitaciones',
            ],
            'Control Recursos Humanos' => [
                'Formatos',
                'Documentos',
                'Informes',
                'Capacitaciones',
                'Cursos',
            ],
            'Control Gerencia' => [
                'Informes',
            ],
            'Control Proyectos' => [
                'Contrato pozos',
            ],
            'Control Finanzas' => [
                'Informes',
            ],
        ];

        foreach ($submodulos as $moduloNombre => $hijos) {
            $idPadre = $ids[$moduloNombre] ?? null;
            if (! $idPadre) {
                continue;
            }
            foreach ($hijos as $descripcion) {
                DB::table('sgc_carpetas3')->insert([
                    'descripcion' => $descripcion,
                    'id_padre'    => $idPadre,
                    'nivel'       => 1,
                    'creada_el'   => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Eliminar submódulos primero (nivel 1), luego módulos raíz (nivel 0)
        DB::table('sgc_carpetas3')->where('nivel', 1)->delete();
        DB::table('sgc_carpetas3')->where('nivel', 0)->delete();
    }
};
