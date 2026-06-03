<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sgc_usuarios', function (Blueprint $table) {
            $table->tinyInteger('eliminar_control_instrumentos')->default(0)->after('editar_control_instrumentos');
        });
    }

    public function down(): void
    {
        Schema::table('sgc_usuarios', function (Blueprint $table) {
            $table->dropColumn('eliminar_control_instrumentos');
        });
    }
};
