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
        Schema::table('impresoras', function (Blueprint $table) {
            $table->foreignIdFor(Gerencia::class)->nullable()->after('imagen')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('impresoras', function (Blueprint $table) {
            $table->dropForeign(['gerencia_id']);
            $table->dropColumn('gerencia_id');
        });
    }
};

