<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sgc_videos', function (Blueprint $table) {
            $table->boolean('es_bienvenida')->default(false)->after('creada_el');
        });
    }

    public function down(): void
    {
        Schema::table('sgc_videos', function (Blueprint $table) {
            $table->dropColumn('es_bienvenida');
        });
    }
};
