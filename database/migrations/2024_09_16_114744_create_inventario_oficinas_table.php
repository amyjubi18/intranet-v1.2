<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UnidadMedida;
use App\Models\Articulo;
use App\Models\Proveedore;
use App\Models\Ubicacion;
use App\Models\Area;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventario_oficinas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_ingreso');
            $table->foreignIdFor(UnidadMedida::class);
            $table->foreignIdFor(Articulo::class);
            $table->foreignIdFor(Proveedore::class);
            $table->foreignIdFor(Ubicacion::class);
            $table->foreignIdFor(Area::class);
            $table->string('n_factura');
            $table->integer('unidades');
            $table->string('descripcion');
            $table->integer('unidades_disponibles');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario_oficinas');
    }
};
