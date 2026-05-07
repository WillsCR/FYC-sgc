<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sgc_carpetas3', function (Blueprint $table) {
            $table->string('color', 50)->nullable()->comment('Color hexadecimal o nombre - solo para niveles 0 y 1');
            $table->string('icono', 100)->nullable()->comment('Nombre del icono (emoji o clase) - solo para niveles 0 y 1');
        });
    }

    public function down(): void
    {
        Schema::table('sgc_carpetas3', function (Blueprint $table) {
            $table->dropColumn(['color', 'icono']);
        });
    }
};
