<?php

use Illuminate\Support\Facades\Route;
use App\Models\Gerencia;
use App\Models\Area;

Route::get('/test-gerencias-areas', function () {
    // Obtener todas las gerencias con sus áreas relacionadas
    $gerencias = Gerencia::with('areas')->get();

    $result = [];

    foreach ($gerencias as $gerencia) {
        $areas = $gerencia->areas->map(function ($area) {
            return ['id' => $area->id, 'area' => $area->area];
        });

        $result[] = [
            'gerencia' => $gerencia->gerencia,
            'areas' => $areas
        ];
    }

    // Retornar resultado en JSON para facilidad de lectura
    return response()->json($result);
});
