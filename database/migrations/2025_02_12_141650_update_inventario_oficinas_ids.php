<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Obtener todos los registros de inventario_oficinas
        $inventarios = DB::table('inventario_oficinas')->get();

        foreach ($inventarios as $inventario) {
            // Obtener el ID del artículo original
            $articuloId = $inventario->articulo_id; // Asegúrate de que esta columna es la correcta

            // Formatear el ID a 4 dígitos
            $nuevoId = str_pad($articuloId, 4, '0', STR_PAD_LEFT);

            // Actualizar el registro en inventario_oficinas
            DB::table('inventario_oficinas')
                ->where('id', $inventario->id) // Asegúrate de que estás usando la columna correcta para identificar el registro
                ->update(['articulo_id' => $nuevoId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
