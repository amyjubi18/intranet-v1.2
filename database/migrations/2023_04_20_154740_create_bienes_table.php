<?php

use App\Models\UsuarioDeUso;
use App\Models\EstadoDelUso;
use App\Models\FormaAdquisicion;
use App\Models\UnidadAdministrativa;
use App\Models\Color;
use App\Models\Condicion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UsuarioAdministrativo;

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
            $table->string('n_interno_bien');
            $table->string('descripcion');
            $table->foreignIdFor(FormaAdquisicion::class);
            $table->date('fecha_adquisicion');
            $table->string('n_factura');
            $table->string('valor_factura');
            $table->string('moneda');
            $table->foreignIdFor(EstadoDelUso::class);
            $table->foreignIdFor(Condicion::class);
            $table->string('marca');
            $table->string('modelo');
            $table->foreignIdFor(Color::class);
            $table->foreignIdFor(UnidadAdministrativa::class);
            $table->foreignIdFor(UsuarioAdministrativo::class);
            $table->foreignIdFor(UsuarioDeUso::class);
            $table->string('serial');
            $table->string('comentario');
            $table->date('fecha_comentario');
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
