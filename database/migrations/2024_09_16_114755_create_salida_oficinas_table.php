<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UnidadMedida;
use App\Models\Articulo;

use App\Models\Ubicacion;
use App\Models\Area;
use App\Models\InventarioOficina;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salida_oficinas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_salida');
            $table->foreignIdFor(UnidadMedida::class);
            $table->foreignIdFor(Articulo::class);
            $table->integer('cantidad');
            $table->foreignIdFor(InventarioOficina::class);
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
        Schema::dropIfExists('salida_oficinas');
    }
};
