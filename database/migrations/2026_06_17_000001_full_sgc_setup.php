<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración maestra — FYC-SGC (Sprints 1-7)
 *
 * Crea todas las tablas nuevas del sistema e incorpora los cambios
 * sobre tablas legacy, usando comprobaciones hasTable/hasColumn para
 * que sea segura de ejecutar tanto en una BD vacía como en una con
 * datos existentes.
 *
 * Tablas NUEVAS (creadas por este sistema):
 *   sgc_areas · sgc_carpetas3 · sgc_documentos · sgc_carpetas_contenido3
 *   sgc_usuarios_areas · sgc_usuarios_permisos_area
 *   sgc_minutas · sgc_minutas_convocados · sgc_minutas_compromisos
 *   sgc_publicaciones · sgc_no_conformidades · sgc_nc_acciones_inmediatas
 *   sgc_nc_acciones_correctivas · sgc_nc_documentos
 *   sgc_archivos_equipos · sgc_archivos_equipos_auditoria
 *   sgc_cert_calidad · sgc_matriz_trabajadores · sgc_matriz_cursos
 *
 * Tablas LEGACY modificadas (ADD COLUMN + MODIFY COLUMN):
 *   sgc_usuarios · sgc_carpetas_permisos · sgc_videos
 *   sgc_programa_verificacion
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("SET SESSION sql_mode = ''");

        // ═══════════════════════════════════════════════════════════════════
        // 1. sgc_areas
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_areas')) {
            Schema::create('sgc_areas', function (Blueprint $table) {
                $table->increments('id');
                $table->string('descripcion', 100);
            });

            DB::table('sgc_areas')->insert([
                ['descripcion' => 'Recursos Humanos'],
                ['descripcion' => 'Seguridad y Salud en el Trabajo'],
                ['descripcion' => 'Abastecimiento y Finanzas'],
                ['descripcion' => 'Contrato Pozos'],
                ['descripcion' => 'Medio Ambiente'],
                ['descripcion' => 'Control SGI'],
                ['descripcion' => 'SGI Gestión'],
                ['descripcion' => 'Patios e Infraestructura'],
                ['descripcion' => 'Gerencia de Operaciones'],
                ['descripcion' => 'Gerencia General'],
            ]);
        }

        // ═══════════════════════════════════════════════════════════════════
        // 2. sgc_carpetas3  (árbol de carpetas/módulos del nuevo sistema)
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_carpetas3')) {
            Schema::create('sgc_carpetas3', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('descripcion', 255);
                $table->unsignedBigInteger('id_padre')->default(0);
                $table->integer('nivel')->default(0);
                $table->timestamp('creada_el')->useCurrent();
                $table->string('ruta', 500)->nullable();
                $table->string('color', 50)->nullable();
                $table->string('icono', 100)->nullable();
                $table->index('id_padre');
                $table->index('nivel');
            });
        } else {
            Schema::table('sgc_carpetas3', function (Blueprint $table) {
                if (! Schema::hasColumn('sgc_carpetas3', 'color')) {
                    $table->string('color', 50)->nullable();
                }
                if (! Schema::hasColumn('sgc_carpetas3', 'icono')) {
                    $table->string('icono', 100)->nullable();
                }
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 3. sgc_documentos  (repositorio centralizado de archivos)
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_documentos')) {
            Schema::create('sgc_documentos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('archivo', 100)->unique();
                $table->string('nombre_original', 255);
                $table->string('tipo_mime', 255)->default('application/pdf');
                $table->bigInteger('tamaño')->default(0);
                $table->string('hash_md5', 64)->nullable()->unique();
                $table->string('creado_por', 100)->nullable();
                $table->timestamp('creada_el')->useCurrent();
                $table->timestamp('modificada_el')->useCurrent()->useCurrentOnUpdate();
                $table->index('creada_el');
            });
        } else {
            // Ampliar tipo_mime y hash_md5 si vinieron de versiones anteriores
            DB::statement("ALTER TABLE `sgc_documentos`
                MODIFY COLUMN `tipo_mime` varchar(255) NOT NULL DEFAULT '',
                MODIFY COLUMN `hash_md5`  varchar(64)  DEFAULT NULL
            ");
        }

        // ═══════════════════════════════════════════════════════════════════
        // 4. sgc_carpetas_contenido3  (relación N:M carpeta ↔ documento)
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_carpetas_contenido3')) {
            Schema::create('sgc_carpetas_contenido3', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_carpeta');
                $table->unsignedBigInteger('id_documento');
                $table->string('descripcion', 500);
                $table->json('metadata')->nullable();
                $table->timestamp('creada_el')->useCurrent();
                $table->timestamp('modificada_el')->useCurrent()->useCurrentOnUpdate();

                $table->unique(['id_carpeta', 'id_documento'], 'uq_carpeta_documento');
                $table->index('id_documento');
                $table->index('creada_el');

                $table->foreign('id_carpeta', 'fk_cc3_carpeta')
                      ->references('id')->on('sgc_carpetas3')->onDelete('cascade');
                $table->foreign('id_documento', 'fk_cc3_documento')
                      ->references('id')->on('sgc_documentos')->onDelete('cascade');
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 5. sgc_usuarios_areas
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_usuarios_areas')) {
            Schema::create('sgc_usuarios_areas', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('id_usuario');
                $table->integer('id_area');
                $table->unique(['id_usuario', 'id_area'], 'uq_usuario_area');
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 6. sgc_usuarios_permisos_area
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_usuarios_permisos_area')) {
            Schema::create('sgc_usuarios_permisos_area', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('id_usuario');
                $table->integer('id_area');
                $table->tinyInteger('ver_planificacion')->default(0);
                $table->tinyInteger('editar_planificacion')->default(0);
                $table->tinyInteger('ver_minutas')->default(0);
                $table->tinyInteger('editar_minutas')->default(0);
                $table->unique(['id_usuario', 'id_area'], 'uq_usuario_area');
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 7. sgc_minutas  (puede existir como tabla legacy)
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_minutas')) {
            Schema::create('sgc_minutas', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('id_area');
                $table->string('empresa', 100)->default('');
                $table->string('tipo_reunion', 80)->default('');
                $table->string('lugar', 150)->default('');
                $table->date('fecha');
                $table->time('hora_inicio');
                $table->time('hora_fin');
                $table->date('proxima_reunion')->nullable();
                $table->unsignedInteger('id_usuario_creador')->nullable();
                $table->timestamps();
            });
        } else {
            // Limpiar fechas inválidas legacy y agregar columnas faltantes
            DB::statement("UPDATE sgc_minutas SET proxima_reunion = NULL
                WHERE proxima_reunion = '0000-00-00' OR proxima_reunion = ''");
            Schema::table('sgc_minutas', function (Blueprint $table) {
                if (! Schema::hasColumn('sgc_minutas', 'id_usuario_creador')) {
                    $table->unsignedInteger('id_usuario_creador')->nullable();
                }
                if (! Schema::hasColumn('sgc_minutas', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 8. sgc_minutas_convocados
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_minutas_convocados')) {
            Schema::create('sgc_minutas_convocados', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_minuta');
                $table->string('empresa', 100)->default('');
                $table->unsignedInteger('id_usuario')->nullable();
                $table->string('nom_ape', 150)->default('');
                $table->string('cargo', 150)->default('');
                $table->timestamps();
                $table->foreign('id_minuta')->references('id')->on('sgc_minutas')->onDelete('cascade');
            });
        } else {
            Schema::table('sgc_minutas_convocados', function (Blueprint $table) {
                if (! Schema::hasColumn('sgc_minutas_convocados', 'created_at')) {
                    $table->timestamps();
                }
                if (! Schema::hasColumn('sgc_minutas_convocados', 'id_usuario')) {
                    $table->unsignedInteger('id_usuario')->nullable();
                }
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 9. sgc_minutas_compromisos
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_minutas_compromisos')) {
            Schema::create('sgc_minutas_compromisos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_minuta');
                $table->unsignedSmallInteger('item')->default(0);
                $table->text('descripcion');
                $table->string('responsable', 150)->default('');
                $table->date('inicio_compromiso')->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                $table->text('observaciones')->nullable();
                $table->unsignedInteger('id_usuario')->nullable();
                $table->timestamps();
                $table->foreign('id_minuta')->references('id')->on('sgc_minutas')->onDelete('cascade');
            });
        } else {
            DB::statement("UPDATE sgc_minutas_compromisos SET inicio_compromiso = NULL
                WHERE inicio_compromiso = '0000-00-00' OR inicio_compromiso = ''");
            Schema::table('sgc_minutas_compromisos', function (Blueprint $table) {
                if (! Schema::hasColumn('sgc_minutas_compromisos', 'item')) {
                    $table->unsignedSmallInteger('item')->default(0);
                }
                if (! Schema::hasColumn('sgc_minutas_compromisos', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 10. sgc_publicaciones  (SIG y Medio Ambiente)
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_publicaciones')) {
            Schema::create('sgc_publicaciones', function (Blueprint $table) {
                $table->id();
                $table->string('seccion', 20);
                $table->string('titulo', 300);
                $table->string('archivo', 120)->unique();
                $table->string('nombre_original', 300);
                $table->string('tipo_mime', 100);
                $table->unsignedBigInteger('tamanio');
                $table->unsignedInteger('creado_por')->nullable();
                $table->timestamp('creada_el')->useCurrent();
                $table->index('seccion');
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 11. sgc_videos — tabla legacy; solo agregar columna es_bienvenida
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_videos')) {
            Schema::create('sgc_videos', function (Blueprint $table) {
                $table->id();
                $table->string('titulo', 300);
                $table->string('archivo', 120)->unique();
                $table->string('nombre_original', 300);
                $table->string('tipo_mime', 50);
                $table->unsignedBigInteger('tamanio');
                $table->unsignedInteger('creado_por')->nullable();
                $table->timestamp('creada_el')->useCurrent();
                $table->boolean('es_bienvenida')->default(false);
            });
        } else {
            if (! Schema::hasColumn('sgc_videos', 'es_bienvenida')) {
                Schema::table('sgc_videos', function (Blueprint $table) {
                    $table->boolean('es_bienvenida')->default(false);
                });
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // 12. sgc_no_conformidades + tablas relacionadas
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_no_conformidades')) {
            Schema::create('sgc_no_conformidades', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('num_nc');
                $table->date('fecha_deteccion');
                $table->string('origen', 100)->default('');
                $table->unsignedInteger('id_area')->nullable();
                $table->tinyInteger('tipo_accion')->default(1);
                $table->string('detectada_por', 150)->default('');
                $table->string('cargo', 100)->default('');
                $table->text('descripcion');
                $table->string('resp_inmediata_nombre', 150)->default('');
                $table->string('resp_inmediata_cargo', 100)->default('');
                $table->tinyInteger('status_correccion')->default(0);
                $table->string('resp_correctiva_nombre', 150)->default('');
                $table->string('resp_correctiva_cargo', 100)->default('');
                $table->date('fecha_implem_acc_corr')->nullable();
                $table->date('fecha_seguim_acc_corr')->nullable();
                $table->tinyInteger('status_acc_corr')->default(0);
                $table->tinyInteger('status_seguim_acc_corr')->default(0);
                $table->tinyInteger('accion_eficaz')->default(0);
                $table->date('fecha_cierre')->nullable();
                $table->text('registros')->nullable();
                $table->string('observaciones', 500)->nullable();
                $table->unsignedInteger('id_usuario')->nullable();
                $table->timestamp('creada_el')->useCurrent();
                $table->timestamp('actualizada_el')->useCurrent()->useCurrentOnUpdate();
                $table->index('num_nc');
                $table->index('fecha_deteccion');
            });
        }

        if (! Schema::hasTable('sgc_nc_acciones_inmediatas')) {
            Schema::create('sgc_nc_acciones_inmediatas', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_nc');
                $table->text('descripcion');
                $table->foreign('id_nc')->references('id')->on('sgc_no_conformidades')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('sgc_nc_acciones_correctivas')) {
            Schema::create('sgc_nc_acciones_correctivas', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_nc');
                $table->text('descripcion');
                $table->foreign('id_nc')->references('id')->on('sgc_no_conformidades')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('sgc_nc_documentos')) {
            Schema::create('sgc_nc_documentos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_nc');
                $table->string('archivo', 120);
                $table->string('nombre_original', 300);
                $table->tinyInteger('tipo');
                $table->unsignedInteger('id_usuario')->nullable();
                $table->timestamp('creada_el')->useCurrent();
                $table->foreign('id_nc')->references('id')->on('sgc_no_conformidades')->onDelete('cascade');
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 13. sgc_archivos_equipos + auditoría
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_archivos_equipos')) {
            Schema::create('sgc_archivos_equipos', function (Blueprint $table) {
                $table->id();
                $table->string('archivo_hash', 64)->unique();
                $table->string('archivo_nombre');
                $table->string('archivo_extension', 10);
                $table->string('archivo_mime', 50);
                $table->integer('archivo_tamanio');
                $table->string('ruta_legado')->nullable();
                $table->enum('ruta_tipo', ['legacy', 'hashed'])->default('hashed');
                $table->string('ruta_almacenamiento');
                $table->unsignedBigInteger('id_equipo_generico')->nullable();
                $table->unsignedBigInteger('id_programa_verificacion')->nullable();
                $table->unsignedBigInteger('id_equipo_interno')->nullable();
                $table->enum('tipo_documento', [
                    'imagen_general', 'cert_calidad', 'cert_calibracion',
                    'manual', 'protocolo', 'inspecciones', 'mantenimiento', 'otro',
                ])->default('otro');
                $table->unsignedBigInteger('id_usuario');
                $table->timestamp('fecha_subida')->useCurrent();
                $table->timestamp('fecha_modificacion')->useCurrent()->useCurrentOnUpdate();
                $table->text('descripcion')->nullable();
                $table->date('vigencia_hasta')->nullable();
                $table->enum('estado', ['activo', 'inactivo', 'eliminado'])->default('activo');
                $table->unsignedInteger('version')->default(1);
                $table->unsignedBigInteger('reemplazo_de')->nullable();
                $table->index('tipo_documento');
                $table->index('id_programa_verificacion');
                $table->index('id_equipo_generico');
                $table->index('fecha_subida');
                $table->index('vigencia_hasta');
            });
        }

        if (! Schema::hasTable('sgc_archivos_equipos_auditoria')) {
            Schema::create('sgc_archivos_equipos_auditoria', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_archivo');
                $table->string('accion');
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->timestamp('fecha')->useCurrent();
                $table->json('detalles')->nullable();
                $table->foreign('id_archivo')->references('id')->on('sgc_archivos_equipos')->onDelete('cascade');
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 14. sgc_cert_calidad
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_cert_calidad')) {
            Schema::create('sgc_cert_calidad', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('numero')->nullable();
                $table->string('contrato', 200)->nullable();
                $table->string('descripcion', 400);
                $table->string('tipo_certificado', 200)->nullable();
                $table->boolean('aplica')->default(false);
                $table->boolean('critico')->default(false);
                $table->string('procedimiento_asociado', 300)->nullable();
                $table->string('marca', 200)->nullable();
                $table->string('modelo', 200)->nullable();
                $table->date('vencimiento')->nullable();
                $table->string('correo_aviso', 200)->nullable();
                $table->boolean('aviso_30d_enviado')->default(false);
                $table->text('observaciones')->nullable();
                $table->string('archivo_nombre', 300)->nullable();
                $table->string('archivo_ruta', 400)->nullable();
                $table->string('archivo_mime', 100)->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('sgc_cert_calidad', function (Blueprint $table) {
                if (! Schema::hasColumn('sgc_cert_calidad', 'correo_aviso')) {
                    $table->string('correo_aviso', 200)->nullable()->after('vencimiento');
                }
                if (! Schema::hasColumn('sgc_cert_calidad', 'aviso_30d_enviado')) {
                    $table->boolean('aviso_30d_enviado')->default(false)->after('correo_aviso');
                }
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 15. sgc_matriz_trabajadores + sgc_matriz_cursos
        // ═══════════════════════════════════════════════════════════════════
        if (! Schema::hasTable('sgc_matriz_trabajadores')) {
            Schema::create('sgc_matriz_trabajadores', function (Blueprint $table) {
                $table->id();
                $table->string('nombres', 200);
                $table->string('apellidos', 200);
                $table->string('rut', 30)->nullable();
                $table->string('cargo', 200)->nullable();
                $table->string('contrato', 200)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sgc_matriz_cursos')) {
            Schema::create('sgc_matriz_cursos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_trabajador');
                $table->string('curso', 300);
                $table->string('entidad_acreditadora', 200)->nullable();
                $table->date('fecha_realizacion')->nullable();
                $table->date('fecha_vencimiento')->nullable();
                $table->string('correo_aviso', 200)->nullable();
                $table->string('archivo_nombre', 300)->nullable();
                $table->string('archivo_ruta', 500)->nullable();
                $table->string('archivo_mime', 100)->nullable();
                $table->timestamps();
                $table->foreign('id_trabajador')
                      ->references('id')->on('sgc_matriz_trabajadores')
                      ->onDelete('cascade');
            });
        }

        // ═══════════════════════════════════════════════════════════════════
        // 16. Columnas en sgc_usuarios  (tabla legacy — solo ADD COLUMN)
        // ═══════════════════════════════════════════════════════════════════
        if (Schema::hasTable('sgc_usuarios')) {
            Schema::table('sgc_usuarios', function (Blueprint $table) {
                $newCols = [
                    'bloque_gerencia'                 => ['tinyInteger', 0],
                    'bloque_patio'                    => ['tinyInteger', 0],
                    'bloque_calidad'                  => ['tinyInteger', 0],
                    'bloque_docs_legales'             => ['tinyInteger', 0],
                    'bloque_formatos'                 => ['tinyInteger', 0],
                    'bloque_listado_interes'          => ['tinyInteger', 0],
                    'bloque_finanzas'                 => ['tinyInteger', 0],
                    'eliminar_control_instrumentos'   => ['tinyInteger', 0],
                ];
                foreach ($newCols as $col => [$type, $default]) {
                    if (! Schema::hasColumn('sgc_usuarios', $col)) {
                        $table->$type($col)->default($default)->nullable();
                    }
                }
            });

            // SET DEFAULT 0 en columnas tinyint que en el legacy no tienen DEFAULT
            try {
                DB::statement("
                    ALTER TABLE `sgc_usuarios`
                        MODIFY COLUMN `planificacion`                tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `editar_planificacion`         tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `agregar_planificacion`        tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `agregar_minutas`              tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ver_pozos`                    tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ver_cursos`                   tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ver_btn_matriz`               tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ver_btn_maq`                  tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ver_btn_ctrl_pozos`           tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ver_btn_rrhh`                 tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ocultar_lateral`              tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `carga_pozos`                  tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ver_control_instrumentos`     tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ver_control_no_conformidades` tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ver_sig`                      tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `carga_sig`                    tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `editar_control_instrumentos`  tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `editar_cursos`                tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `bloque_sig`                   tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `bloque_seguridad`             tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `bloque_ambiente`              tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `bloque_rrhh`                  tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `bloque_abastecimiento`        tinyint(1) NOT NULL DEFAULT 0,
                        MODIFY COLUMN `bloque_proyectos`             tinyint(1) NOT NULL DEFAULT 0
                ");
            } catch (\Throwable $e) {
                // Ignorar si alguna columna no existe en esta instancia
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // 17. Columnas en sgc_carpetas_permisos  (tabla legacy)
        // ═══════════════════════════════════════════════════════════════════
        if (Schema::hasTable('sgc_carpetas_permisos')) {
            if (! Schema::hasColumn('sgc_carpetas_permisos', 'ver')) {
                Schema::table('sgc_carpetas_permisos', function (Blueprint $table) {
                    $table->boolean('ver')->default(false)->after('id_usuario');
                });
            }
            try {
                DB::statement("
                    ALTER TABLE `sgc_carpetas_permisos`
                        MODIFY COLUMN `correo`       varchar(150) NOT NULL DEFAULT '',
                        MODIFY COLUMN `clave`        varchar(255) NOT NULL DEFAULT '',
                        MODIFY COLUMN `carga`        tinyint(1)   NOT NULL DEFAULT 0,
                        MODIFY COLUMN `descarga`     tinyint(1)   NOT NULL DEFAULT 0,
                        MODIFY COLUMN `crear`        tinyint(1)   NOT NULL DEFAULT 0,
                        MODIFY COLUMN `ocultar_raiz` tinyint(1)   NOT NULL DEFAULT 0,
                        MODIFY COLUMN `eliminar`     tinyint(1)   NOT NULL DEFAULT 0,
                        MODIFY COLUMN `editar`       tinyint(1)   NOT NULL DEFAULT 0
                ");
            } catch (\Throwable $e) {
                // Ignorar si las columnas no existen en esta instancia
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // 18. Columnas en sgc_programa_verificacion  (tabla legacy)
        // ═══════════════════════════════════════════════════════════════════
        if (Schema::hasTable('sgc_programa_verificacion')) {
            try {
                DB::statement("
                    ALTER TABLE sgc_programa_verificacion
                        MODIFY id_equipo_interno  INT(11)    NULL    DEFAULT NULL,
                        MODIFY id_equipo_generico INT(11)    NULL    DEFAULT NULL,
                        MODIFY interno            INT(11)    NULL    DEFAULT NULL,
                        MODIFY ingresado          DATETIME   NULL    DEFAULT NULL,
                        MODIFY calibracion        TINYINT(1) NOT NULL DEFAULT 0,
                        MODIFY verificacion       TINYINT(1) NULL    DEFAULT NULL,
                        MODIFY cert_calidad       TINYINT(1) NOT NULL DEFAULT 0,
                        MODIFY cert_calibracion   TINYINT(1) NOT NULL DEFAULT 0
                ");
            } catch (\Throwable $e) {
                // Ignorar si el esquema legacy difiere
            }

            if (! Schema::hasColumn('sgc_programa_verificacion', 'responsable')) {
                Schema::table('sgc_programa_verificacion', function (Blueprint $table) {
                    $table->string('responsable', 200)->nullable();
                });
            }
            if (! Schema::hasColumn('sgc_programa_verificacion', 'correo_aviso')) {
                Schema::table('sgc_programa_verificacion', function (Blueprint $table) {
                    $table->string('correo_aviso', 200)->nullable();
                });
            }
            if (! Schema::hasColumn('sgc_programa_verificacion', 'aviso_30d_enviado')) {
                Schema::table('sgc_programa_verificacion', function (Blueprint $table) {
                    $table->boolean('aviso_30d_enviado')->default(false);
                });
            }
        }

        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_matriz_cursos');
        Schema::dropIfExists('sgc_matriz_trabajadores');
        Schema::dropIfExists('sgc_cert_calidad');
        Schema::dropIfExists('sgc_archivos_equipos_auditoria');
        Schema::dropIfExists('sgc_archivos_equipos');
        Schema::dropIfExists('sgc_nc_documentos');
        Schema::dropIfExists('sgc_nc_acciones_correctivas');
        Schema::dropIfExists('sgc_nc_acciones_inmediatas');
        Schema::dropIfExists('sgc_no_conformidades');
        Schema::dropIfExists('sgc_publicaciones');
        Schema::dropIfExists('sgc_carpetas_contenido3');
        Schema::dropIfExists('sgc_documentos');
        Schema::dropIfExists('sgc_carpetas3');
        Schema::dropIfExists('sgc_usuarios_permisos_area');
        Schema::dropIfExists('sgc_usuarios_areas');
    }
};
