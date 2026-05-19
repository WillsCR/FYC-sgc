<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seguridad — Ampliar hash_md5 en sgc_documentos para SHA-256
 * Origen: sgc_changes.sql — ALTER TABLE sgc_documentos MODIFY hash_md5
 *
 * El campo se reutiliza para SHA-256, que requiere 64 caracteres.
 * MD5 = 32 chars, SHA-256 = 64 chars.
 *
 * Nota: el fix de tipo_mime (varchar 50→255) se encuentra en
 * 2026_04_30_000001_fix_tipo_mime_length_sgc_documentos.php
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `sgc_documentos`
                MODIFY COLUMN `hash_md5` varchar(64) NOT NULL DEFAULT ''
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `sgc_documentos`
                MODIFY COLUMN `hash_md5` varchar(32) DEFAULT NULL
        ");
    }
};
