<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AlmacenamientoImpresora;
use App\Models\DistribucionAlmacenamientoImpresora;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salida_almacenamiento_impresoras', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_salida');
            $table->foreignIdFor(AlmacenamientoImpresora::class);
            $table->foreignIdFor(DistribucionAlmacenamientoImpresora::class);
            $table->integer('cantidad');
            $table->integer('historico_unidades_disponibles');
            $table->string('ajuste_inventario');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salida_almacenamiento_impresoras');
    }
};
