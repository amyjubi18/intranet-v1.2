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
        Schema::table('distribucion_almacenamiento_impresoras', function (Blueprint $table) {
            $table->integer('unidades_asignadas_originales')->nullable()->after('unidades_asignadas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distribucion_almacenamiento_impresoras', function (Blueprint $table) {
            $table->dropColumn('unidades_asignadas_originales');
        });
    }
};
