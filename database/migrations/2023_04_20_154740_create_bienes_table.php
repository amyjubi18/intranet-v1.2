<?php

use App\Models\CategoriaEspecifica;
use App\Models\CategoriaGeneral;
use App\Models\EstadoDelUso;
use App\Models\FormaAdquisicion;
use App\Models\SubCategoria;
use App\Models\UnidadAdministrativa;
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
        Schema::create('bienes', function (Blueprint $table) {
            $table->id();
            $table->string('sede');
            $table->foreignIdFor(UnidadAdministrativa::class);
            $table->string('n_interno_bien');
            $table->string('descripcion');
            $table->foreignIdFor(FormaAdquisicion::class);
            $table->date('fecha_adquisicion');
            $table->string('n_factura');
            $table->string('valor_factura');
            $table->string('moneda');
            $table->foreignIdFor(EstadoDelUso::class);
            $table->string('condicion_fisica');
            $table->string('marca');
            $table->string('modelo');
            $table->string('serial');
            $table->foreignIdFor(CategoriaGeneral::class);
            $table->foreignIdFor(SubCategoria::class);
            $table->foreignIdFor(CategoriaEspecifica::class);
            $table->string('codigo_contable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bienes');
    }
};
