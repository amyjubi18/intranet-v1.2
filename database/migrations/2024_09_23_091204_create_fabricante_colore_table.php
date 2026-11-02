
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
        Schema::create('fabricante_colore', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fabricante_id')->constrained()->onDelete('cascade');
            $table->foreignId('colore_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Evitar duplicados
            $table->unique(['fabricante_id', 'colore_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fabricante_colore');
    }
};

