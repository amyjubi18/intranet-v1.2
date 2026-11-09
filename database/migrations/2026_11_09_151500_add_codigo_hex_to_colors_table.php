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
        if (!Schema::hasColumn('colors', 'codigo_hex')) {
            Schema::table('colors', function (Blueprint $table) {
                $table->string('codigo_hex', 25)->nullable()->after('color');
            });
        }

        // Asignar colores respectivos a los registros existentes
        $defaultColors = [
            'NEGRO' => '#000000',
            'BLANCO' => '#FFFFFF',
            'AZUL' => '#1D4ED8',
            'GRIS Y NEGRO' => '#374151',
            'GRIS' => '#6B7280',
            'BEIGE' => '#F5F5DC',
            'BLANCO Y NEGRO' => '#1F2937',
            'VARIOS' => '#9CA3AF',
            'GRIS Y BLANCO' => '#D1D5DB',
            'MARRON OSCURO Y GRIS' => '#4B382A',
            'GRIS CLARO Y GRIS OSCURO' => '#4B5563',
            'MARRON OSCURO' => '#5C4033',
            'PLATA' => '#C0C0C0',
            'MARRON' => '#8B4513',
            'COBRE' => '#B87333',
        ];

        foreach ($defaultColors as $name => $hex) {
            DB::table('colors')
                ->where('color', $name)
                ->update(['codigo_hex' => $hex]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('colors', 'codigo_hex')) {
            Schema::table('colors', function (Blueprint $table) {
                $table->dropColumn('codigo_hex');
            });
        }
    }
};
