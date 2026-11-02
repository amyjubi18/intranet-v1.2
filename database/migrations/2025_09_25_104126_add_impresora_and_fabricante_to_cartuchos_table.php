<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Impresora;
use App\Models\Fabricante;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cartuchos', function (Blueprint $table) {
            $table->foreignIdFor(Impresora::class);
            $table->foreignIdFor(Fabricante::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cartuchos', function (Blueprint $table) {
            $table->dropForeign(['impresora_id']);
            $table->dropColumn('impresora_id');
            $table->dropForeign(['fabricante_id']);
            $table->dropColumn('fabricante_id');
        });
    }
};
