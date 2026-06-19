<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sgc_matriz_trabajadores')) return;

        Schema::table('sgc_matriz_trabajadores', function (Blueprint $table) {
            if (! Schema::hasColumn('sgc_matriz_trabajadores', 'nivel_estudios')) {
                $table->string('nivel_estudios', 200)->nullable();
            }
            if (! Schema::hasColumn('sgc_matriz_trabajadores', 'especialidad')) {
                $table->string('especialidad', 200)->nullable();
            }
            if (! Schema::hasColumn('sgc_matriz_trabajadores', 'cert_titulo_nombre')) {
                $table->string('cert_titulo_nombre', 300)->nullable();
            }
            if (! Schema::hasColumn('sgc_matriz_trabajadores', 'cert_titulo_ruta')) {
                $table->string('cert_titulo_ruta', 500)->nullable();
            }
            if (! Schema::hasColumn('sgc_matriz_trabajadores', 'cert_titulo_mime')) {
                $table->string('cert_titulo_mime', 100)->nullable();
            }
            if (! Schema::hasColumn('sgc_matriz_trabajadores', 'cv_nombre')) {
                $table->string('cv_nombre', 300)->nullable();
            }
            if (! Schema::hasColumn('sgc_matriz_trabajadores', 'cv_ruta')) {
                $table->string('cv_ruta', 500)->nullable();
            }
            if (! Schema::hasColumn('sgc_matriz_trabajadores', 'cv_mime')) {
                $table->string('cv_mime', 100)->nullable();
            }
            if (! Schema::hasColumn('sgc_matriz_trabajadores', 'cumple')) {
                $table->boolean('cumple')->default(false);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sgc_matriz_trabajadores')) return;

        Schema::table('sgc_matriz_trabajadores', function (Blueprint $table) {
            $cols = ['nivel_estudios','especialidad','cert_titulo_nombre','cert_titulo_ruta',
                     'cert_titulo_mime','cv_nombre','cv_ruta','cv_mime','cumple'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('sgc_matriz_trabajadores', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
