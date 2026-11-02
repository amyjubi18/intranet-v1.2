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
        Schema::table('salida_almacenamiento_impresoras', function (Blueprint $table) {
            $table->integer('historico_unidades_asignadas_restantes')->nullable()->after('historico_unidades_asignadas_originales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salida_almacenamiento_impresoras', function (Blueprint $table) {
            $table->dropColumn('historico_unidades_asignadas_restantes');
        });
    }
};
