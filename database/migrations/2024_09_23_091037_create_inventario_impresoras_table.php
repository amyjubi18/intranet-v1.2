<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Colore;
use App\Models\Area;
use App\Models\Ubicacion;
use App\Models\Cartucho;
use App\Models\Impresora;
use App\Models\Proveedore;
use App\Models\Fabricante;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventario_impresoras', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_ingreso');
            $table->foreignIdFor(Colore::class)->nullable();
            $table->foreignIdFor(Cartucho::class)->nullable();
            $table->foreignIdFor(Impresora::class)->nullable();
            $table->foreignIdFor(Proveedore::class);
            $table->foreignIdFor(Fabricante::class)->nullable();
            $table->foreignIdFor(Ubicacion::class);
            $table->foreignIdFor(Area::class);
            $table->string('n_factura');
            $table->integer('unidades');
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
        Schema::dropIfExists('inventario_impresoras');
    }
};
