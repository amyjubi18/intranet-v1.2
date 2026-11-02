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
        Schema::table('eventos', function (Blueprint $table) {
            if (!Schema::hasColumn('eventos', 'all_day')) {
                $table->boolean('all_day')->default(false)->after('end');
            }
            if (!Schema::hasColumn('eventos', 'color')) {
                $table->string('color', 7)->default('#3b82f6')->after('all_day');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            if (Schema::hasColumn('eventos', 'color')) {
                $table->dropColumn('color');
            }
            if (Schema::hasColumn('eventos', 'all_day')) {
                $table->dropColumn('all_day');
            }
        });
    }
};

