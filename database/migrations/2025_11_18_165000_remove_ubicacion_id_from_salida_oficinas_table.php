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
        // Verificar si la columna existe antes de eliminarla
        if (Schema::hasColumn('salida_oficinas', 'ubicacion_id')) {
            Schema::table('salida_oficinas', function (Blueprint $table) {
                $table->dropColumn('ubicacion_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salida_oficinas', function (Blueprint $table) {
            $table->unsignedBigInteger('ubicacion_id')->nullable();
            $table->foreign('ubicacion_id')->references('id')->on('ubicacions')->onDelete('cascade');
        });
    }
};

