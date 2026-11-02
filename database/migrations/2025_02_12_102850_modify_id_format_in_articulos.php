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
        // Cambiar el tipo de la columna a string
        Schema::table('articulos', function (Blueprint $table) {
            $table->string('id')->change(); // Cambia el tipo de la columna a string
        });

        // Obtener todos los registros y ordenarlos por ID
        $records = DB::table('articulos')->orderBy('id')->get();
        $existingIds = []; // Para almacenar los IDs existentes

        // Primero, obtenemos todos los IDs existentes
        foreach ($records as $record) {
            $existingIds[] = $record->id;
        }

        // Actualizar los IDs existentes
        foreach ($records as $record) {
            $lastNumber = (int) substr($record->id, 4); // Obtiene el número después del guion
            $newId = sprintf('000-%d', $lastNumber + 1); // Genera el nuevo ID

            // Asegúrate de que el nuevo ID no esté en uso
            while (in_array($newId, $existingIds)) {
                $lastNumber++; // Incrementa el número
                $newId = sprintf('000-%d', $lastNumber + 1); // Genera un nuevo ID
            }

            // Actualiza el ID en la base de datos
            DB::table('articulos')->where('id', $record->id)->update(['id' => $newId]);

            // Agrega el nuevo ID a la lista de IDs existentes
            $existingIds[] = $newId;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            //
        });
    }
};
