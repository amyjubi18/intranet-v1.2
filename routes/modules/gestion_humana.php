<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DigitalizacioneController;
use App\Http\Controllers\AreaAdscritaController;
use App\Http\Controllers\TipoDocumentoController;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo: Gestión Humana (OGH)
|--------------------------------------------------------------------------
| Contenedor: ct-gestion-humana (172.17.50.251)
*/

Route::middleware(['auth', 'session.timeout', 'prevent.concurrent'])->group(function () {

    // Área Adscrita
    Route::resource('/gestion-humana/area_adscrita', AreaAdscritaController::class)
        ->names('gestion-humana.area_adscrita')
        ->parameters(['area_adscrita' => 'area_adscrita']);

    // Digitalización de Documentos OGH
    Route::resource('/gestion-humana/digitalizacion_documentos', DigitalizacioneController::class)
        ->names('gestion-humana.digitalizacion_documentos')
        ->parameters(['digitalizacion_documentos' => 'digitalizacione']);

    Route::delete('/gestion-humana/digitalizacion_documentos/documento/{documento}', [DigitalizacioneController::class, 'destroyDocumento'])
        ->name('gestion-humana.digitalizacion_documentos.documento.destroy');

    // Tipo de Documentos
    Route::resource('/gestion-humana/tipo_documentos', TipoDocumentoController::class)
        ->names('gestion-humana.tipo_documentos')
        ->parameters(['tipo_documentos' => 'tipo_documento']);
});
