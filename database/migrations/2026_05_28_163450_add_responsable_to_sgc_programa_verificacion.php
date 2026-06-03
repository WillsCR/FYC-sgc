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
            $table->string('responsable', 200)->nullable()->after('observaciones');
        });
    }

    public function down(): void
    {
        Schema::table('sgc_programa_verificacion', function (Blueprint $table) {
            $table->dropColumn('responsable');
        });
    }
};
