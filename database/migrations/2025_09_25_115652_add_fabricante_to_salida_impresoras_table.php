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
            $table->foreignIdFor(\App\Models\Fabricante::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salida_impresoras', function (Blueprint $table) {
            $table->dropForeign(['fabricante_id']);
            $table->dropColumn('fabricante_id');
        });
    }
};
