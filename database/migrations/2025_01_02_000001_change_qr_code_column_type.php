<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cambiar el tipo de columna de string a text para soportar imágenes base64
        DB::statement('ALTER TABLE bienes MODIFY qr_code TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a string(255)
        DB::statement('ALTER TABLE bienes MODIFY qr_code VARCHAR(255) NULL');
    }
};
