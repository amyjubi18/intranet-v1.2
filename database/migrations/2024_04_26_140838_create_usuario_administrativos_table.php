<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UnidadAdministrativa;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuario_administrativos', function (Blueprint $table) {
            $table->id();
            $table->string('usuario_administrativo');
            $table->foreignIdFor(UnidadAdministrativa::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_administrativos');
    }
};
