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
        Schema::table('areas', function (Blueprint $table) {
            $table->unsignedBigInteger('gerencia_id')->nullable()->after('area');
            $table->foreign('gerencia_id')->references('id')->on('gerencias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropForeign(['gerencia_id']);
            $table->dropColumn('gerencia_id');
        });
    }
};
