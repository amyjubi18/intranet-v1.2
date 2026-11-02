<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Ubicacion;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salida_oficinas', function (Blueprint $table) {
            $table->foreignIdFor(Ubicacion::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salida_oficinas', function (Blueprint $table) {
            $table->dropColumn('ubicacion_id');
            $table->dropForeign('ubicacion_id');
        });
    }
};
