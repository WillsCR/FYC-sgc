<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * id_usuario era NOT NULL — debe ser nullable para permitir participantes externos.
     */
    public function up(): void
    {
        Schema::table('sgc_minutas_convocados', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sgc_minutas_convocados', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario')->nullable(false)->change();
        });
    }
};
