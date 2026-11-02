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
        Schema::table('inventario_oficinas', function (Blueprint $table) {
            if (Schema::hasColumn('inventario_oficinas', 'gerencia_id')) {
                $table->dropColumn('gerencia_id');
            }
            if (Schema::hasColumn('inventario_oficinas', 'area_id')) {
                $table->dropColumn('area_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventario_oficinas', function (Blueprint $table) {
            $table->unsignedBigInteger('gerencia_id')->nullable();
            $table->unsignedBigInteger('area_id')->nullable();
            $table->foreign('gerencia_id')->references('id')->on('gerencias');
            $table->foreign('area_id')->references('id')->on('areas');
        });
    }
};
