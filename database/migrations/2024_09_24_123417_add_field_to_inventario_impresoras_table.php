<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Gerencia;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventario_impresoras', function (Blueprint $table) {
            $table->foreignIdFor(Gerencia::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventario_impresoras', function (Blueprint $table) {
            $table->dropColumn('gerencia_id');
            $table->dropForeign('gerencia_id');
        });
    }
};
