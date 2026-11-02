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
        Schema::table('salida_impresoras', function (Blueprint $table) {
            $table->string('tipo_consumible')->nullable()->after('fabricante_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salida_impresoras', function (Blueprint $table) {
            $table->dropColumn('tipo_consumible');
        });
    }
};
