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
        Schema::table('sgc_programa_verificacion', function (Blueprint $table) {
            $table->string('correo_aviso', 200)->nullable()->after('responsable');
            $table->boolean('aviso_30d_enviado')->default(false)->after('correo_aviso');
        });
    }

    public function down(): void
    {
        Schema::table('sgc_programa_verificacion', function (Blueprint $table) {
            $table->dropColumn(['correo_aviso', 'aviso_30d_enviado']);
        });
    }
};
