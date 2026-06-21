<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sgc_matriz_cursos', function (Blueprint $table) {
            if (! Schema::hasColumn('sgc_matriz_cursos', 'aviso_30d_enviado')) {
                $table->boolean('aviso_30d_enviado')->default(false)->after('correo_aviso');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sgc_matriz_cursos', function (Blueprint $table) {
            if (Schema::hasColumn('sgc_matriz_cursos', 'aviso_30d_enviado')) {
                $table->dropColumn('aviso_30d_enviado');
            }
        });
    }
};
