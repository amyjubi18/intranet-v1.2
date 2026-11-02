<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Cambiar el tipo de la columna de nuevo a integer
        Schema::table('articulos', function (Blueprint $table) {
            $table->integer('id')->change(); // Cambia el tipo de la columna a integer
        });

        // Obtener todos los registros y ordenarlos por ID
        $records = DB::table('articulos')->orderBy('id')->get();

        // Crear un array para almacenar los nuevos IDs
        $newIds = [];

        // Actualizar los IDs existentes
        foreach ($records as $record) {
            // Extraer el número del ID actual
            $lastNumber = (int) substr($record->id, 4); // Obtiene el número después del guion
            $newId = $lastNumber; // Asigna el nuevo ID como un número entero

            // Asegúrate de que el nuevo ID no esté en uso
            while (in_array($newId, $newIds) || DB::table('articulos')->where('id', $newId)->exists()) {
                $newId++; // Incrementa el número hasta encontrar un ID único
            }

            // Actualiza el ID en la base de datos
            DB::table('articulos')->where('id', $record->id)->update(['id' => $newId]);

            // Agrega el nuevo ID a la lista de IDs utilizados
            $newIds[] = $newId;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Aquí puedes agregar la lógica para revertir esta migración si es necesario
        Schema::table('articulos', function (Blueprint $table) {
            $table->string('id')->change(); // Cambia el tipo de la columna de nuevo a string
        });
    }
};
