<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \DB::statement('ALTER TABLE sgc_documentos MODIFY COLUMN tipo_mime VARCHAR(255) NOT NULL DEFAULT \'\'');
    }

    public function down(): void
    {
        // No revertir: reducir el tamaño podría truncar datos existentes
    }
};
