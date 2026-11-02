<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Colores;
use App\Models\Area;
use App\Models\Ubicacion;
use App\Models\Cartucho;
use App\Models\Impresora;
use App\Models\InventarioImpresora;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salida_impresoras', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_salida');
            $table->foreignIdFor(Cartucho::class);
            $table->foreignIdFor(Colores::class);
            $table->foreignIdFor(Ubicacion::class);
            $table->foreignIdFor(Impresora::class);
            $table->integer('cantidad');
            $table->foreignIdFor(InventarioImpresora::class);
            $table->foreignIdFor(Area::class);
            $table->string('ajuste_inventario');
            $table->integer('historico_unidades_disponibles');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salida_impresoras');
    }
};
