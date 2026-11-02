<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AreaAdscrita;
use App\Models\TipoDocumento;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('digitalizaciones', function (Blueprint $table) {
            $table->id('cedula');
            $table->string('nombre_apellido');
            $table->string('expediente');
            $table->enum('salud', ['Titular', 'Beneficiario']);
            $table->foreignIdFor(AreaAdscrita::class);
            $table->foreignIdFor(TipoDocumento::class);
            $table->foreignIdFor(User::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digitalizaciones');
    }
};
