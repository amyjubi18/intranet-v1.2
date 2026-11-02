<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Contratado;
use App\Models\Directivo;
use App\Models\Abogado;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cdts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Contratado::class);
            $table->foreignIdFor(Directivo::class);
            $table->foreignIdFor(Abogado::class);
            $table->date('fecha_primer_contrato');
            $table->date('fecha_inicial');
            $table->date('fecha_final');
            $table->date('fecha_punto_cta');
            $table->date('fecha_elaboracion');
            $table->string('n_punto_cuenta');
            $table->string('gaceta',1500);
            $table->string('ciudadano');
            $table->string('contratado');
            $table->string('n_addendum');
            $table->string('clausula',1500);
            $table->string('literal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cdts');
    }
};
