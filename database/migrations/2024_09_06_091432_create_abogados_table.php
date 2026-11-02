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
        Schema::create('abogados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_apellido');
            $table->string('cargo');
            $table->string('n_inprebogado');
            $table->string('cedula');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abogados');
    }
};
