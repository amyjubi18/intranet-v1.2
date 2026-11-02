<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Area;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gerencias', function (Blueprint $table) {
            $table->foreignIdFor(Area::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gerencias', function (Blueprint $table) {
            $table->dropColumn('area_id');
            $table->dropForeign('area_id');
        });
    }
};
