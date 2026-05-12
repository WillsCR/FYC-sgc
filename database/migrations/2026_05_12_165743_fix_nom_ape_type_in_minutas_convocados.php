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
        // nom_ape estaba definida como int(11) por error — debe ser varchar para nombres.
        Schema::table('sgc_minutas_convocados', function (Blueprint $table) {
            $table->string('nom_ape', 200)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sgc_minutas_convocados', function (Blueprint $table) {
            $table->integer('nom_ape')->nullable()->change();
        });
    }
};
