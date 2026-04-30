<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Desactivar modo estricto temporalmente para poder limpiar
        // los valores inválidos del legacy ('0000-00-00', '')
        DB::statement("SET SESSION sql_mode = ''");

        // ─────────────────────────────────────────────────────────────
        // sgc_minutas
        // ─────────────────────────────────────────────────────────────
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
            // Limpiar fechas inválidas del legacy
            DB::statement("UPDATE sgc_minutas SET proxima_reunion = NULL WHERE proxima_reunion = '0000-00-00' OR proxima_reunion = '' OR proxima_reunion IS NULL");

            Schema::table('sgc_minutas', function (Blueprint $table) {
                if (! Schema::hasColumn('sgc_minutas', 'id_usuario_creador')) {
                    $table->unsignedInteger('id_usuario_creador')->nullable()->after('proxima_reunion');
                }
                if (! Schema::hasColumn('sgc_minutas', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        // ─────────────────────────────────────────────────────────────
        // sgc_minutas_convocados
        // ─────────────────────────────────────────────────────────────
        if (! Schema::hasTable('sgc_minutas_convocados')) {
            Schema::create('sgc_minutas_convocados', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_minuta');
                $table->string('empresa', 100)->default('');
                $table->unsignedInteger('id_usuario')->nullable();
                $table->string('nom_ape', 150)->default('');
                $table->string('cargo', 150)->default('');
                $table->timestamps();

                $table->foreign('id_minuta')
                      ->references('id')->on('sgc_minutas')
                      ->onDelete('cascade');
            });
        } else {
            Schema::table('sgc_minutas_convocados', function (Blueprint $table) {
                if (! Schema::hasColumn('sgc_minutas_convocados', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        // ─────────────────────────────────────────────────────────────
        // sgc_minutas_compromisos
        // ─────────────────────────────────────────────────────────────
        if (! Schema::hasTable('sgc_minutas_compromisos')) {
            Schema::create('sgc_minutas_compromisos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_minuta');
                $table->unsignedSmallInteger('item')->default(0);
                $table->text('descripcion');
                $table->string('responsable', 150)->default('');
                $table->date('inicio_compromiso')->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                // 1 = En Proceso | 2 = Cerrado | 3 = Descartado
                $table->text('observaciones')->nullable();
                $table->unsignedInteger('id_usuario')->nullable();
                $table->timestamps();

                $table->foreign('id_minuta')
                      ->references('id')->on('sgc_minutas')
                      ->onDelete('cascade');
            });
        } else {
            // Limpiar fechas inválidas en compromisos también
            DB::statement("UPDATE sgc_minutas_compromisos SET inicio_compromiso = NULL WHERE inicio_compromiso = '0000-00-00' OR inicio_compromiso = ''");

            Schema::table('sgc_minutas_compromisos', function (Blueprint $table) {
                if (! Schema::hasColumn('sgc_minutas_compromisos', 'item')) {
                    $table->unsignedSmallInteger('item')->default(0)->after('id_minuta');
                }
                if (! Schema::hasColumn('sgc_minutas_compromisos', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        // Restaurar modo estricto por defecto de Laravel
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
    }

    public function down(): void
    {
        Schema::dropIfExists('sgc_minutas_compromisos');
        Schema::dropIfExists('sgc_minutas_convocados');
        Schema::dropIfExists('sgc_minutas');
    }
};
