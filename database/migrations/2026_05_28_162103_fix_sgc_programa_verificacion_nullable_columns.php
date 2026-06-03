<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to avoid doctrine/dbal dependency issues with legacy columns
        DB::statement('ALTER TABLE sgc_programa_verificacion
            MODIFY id_equipo_interno  INT(11)    NULL    DEFAULT NULL,
            MODIFY id_equipo_generico INT(11)    NULL    DEFAULT NULL,
            MODIFY interno            INT(11)    NULL    DEFAULT NULL,
            MODIFY ingresado          DATETIME   NULL    DEFAULT NULL,
            MODIFY calibracion        TINYINT(1) NOT NULL DEFAULT 0,
            MODIFY verificacion       TINYINT(1) NULL    DEFAULT NULL,
            MODIFY cert_calidad       TINYINT(1) NOT NULL DEFAULT 0,
            MODIFY cert_calibracion   TINYINT(1) NOT NULL DEFAULT 0
        ');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE sgc_programa_verificacion
            MODIFY id_equipo_interno  INT(11)    NOT NULL,
            MODIFY id_equipo_generico INT(11)    NOT NULL,
            MODIFY interno            INT(11)    NOT NULL,
            MODIFY ingresado          DATETIME   NOT NULL,
            MODIFY calibracion        TINYINT(1) NOT NULL,
            MODIFY verificacion       TINYINT(1) NOT NULL,
            MODIFY cert_calidad       TINYINT(1) NOT NULL,
            MODIFY cert_calibracion   TINYINT(1) NOT NULL
        ');
    }
};
