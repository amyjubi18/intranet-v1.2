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
        Schema::create('impresora_gerencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('impresora_id')->constrained()->onDelete('cascade');
            $table->foreignId('gerencia_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['impresora_id', 'gerencia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impresora_gerencia');
    }
};

