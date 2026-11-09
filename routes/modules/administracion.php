<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\EstanteriaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\BieneController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CondicionController;
use App\Http\Controllers\RelacionBienesController;
use App\Http\Controllers\UnidadAdministrativaController;
use App\Http\Controllers\UsuarioDeUsoController;
use App\Http\Controllers\UsuarioAdministrativoController;
use App\Http\Controllers\FormaAdquisicionController;
use App\Http\Controllers\EstadoUsoController;
use App\Http\Controllers\InventarioOficController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\GerenciasController;
use App\Http\Controllers\SalidaOficController;
use App\Http\Controllers\ImpresoraController;
use App\Http\Controllers\ColoresController;
use App\Http\Controllers\FabricanteController;
use App\Http\Controllers\CartuchoController;
use App\Http\Controllers\InventarioImpresoraController;
use App\Http\Controllers\SalidaImpresoraController;
use App\Http\Controllers\SalidaAlmacenamientoImpresoraController;
use App\Http\Controllers\AlmacenamientoImpresoraController;
use App\Http\Controllers\DistribucionAlmacenamientoImpresoraController;
use App\Http\Controllers\UnifiedInventoryController;
use App\Http\Controllers\ExportarController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\Api\ImpresoraApiController;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo: Administración (OGA)
|--------------------------------------------------------------------------
| Contenedor: ct-administracion (172.17.50.250)
*/

Route::middleware(['auth', 'session.timeout', 'prevent.concurrent'])->group(function () {

    /* =========================================================================
     * 1. DIGITALIZACIÓN DE DOCUMENTOS (OGA)
     * ========================================================================= */
    Route::resource('administracion/documentos', DocsController::class)
        ->names('administracion.documentos')
        ->parameters(['documentos' => 'documento']);
    Route::delete('/administracion/documentos/archivo/{archivo}', [DocsController::class, 'destroyArchivo'])
        ->name('administracion.documentos.archivo.destroy');

    Route::resource('/administracion/estanterias', EstanteriaController::class)
        ->names('administracion.estanterias');

    Route::resource('/administracion/categorias', CategoriaController::class)
        ->names('administracion.categorias');
    Route::post('/administracion/categorias/store-ajax', [CategoriaController::class, 'storeAjax'])
        ->name('administracion.categorias.storeAjax');


    /* =========================================================================
     * 2. BIENES MUEBLES
     * ========================================================================= */
    Route::get('/administracion/bienes_publicos/index', [ExportController::class, 'index'])->name('index');
    Route::get('/export', [ExportController::class, 'export'])->name('export');

    Route::resource('administracion/bienes_publicos', BieneController::class)
        ->names('administracion.bienes_publicos')
        ->parameters(['bienes_publicos' => 'biene']);
    Route::get('/administracion/bienes_publicos/export/excel', [BieneController::class, 'exportExcel'])
        ->name('administracion.bienes_publicos.export.excel');
    Route::get('/administracion/bienes_publicos/export/pdf', [BieneController::class, 'exportPdf'])
        ->name('administracion.bienes_publicos.export.pdf');

    // QR Code
    Route::get('/bienes/qr/{n_interno_bien}', [BieneController::class, 'scanQrCode'])->name('bienes.qr.scan');
    Route::post('/api/bienes/qr/buscar', [BieneController::class, 'findByQrCode'])->name('api.bienes.qr.buscar');

    Route::get('/api/bienes_publicos/usuarios-de-uso/{usuario_admin_id}', [BieneController::class, 'getUsuariosDeUso'])->name('api.bienes_publicos.usuarios_de_uso');
    Route::get('/api/bienes_publicos/usuarios-administrativos/{unidad_id}', [BieneController::class, 'getUsuariosAdministrativos'])->name('api.bienes_publicos.usuarios_administrativos');
    Route::get('/api/bienes_publicos/descripciones', [BieneController::class, 'getDescripciones'])->name('api.bienes_publicos.descripciones');
    Route::post('/api/bienes_publicos/descripciones', [BieneController::class, 'storeDescripcion'])->name('api.bienes_publicos.descripciones.store');

    // Carga Previa Bienes
    Route::resource('administracion/colors', ColorController::class)
        ->names('administracion.colors')
        ->parameters(['colors' => 'color']);
    Route::resource('administracion/condicion_fisica', CondicionController::class)
        ->names('administracion.condicion_fisica')
        ->parameters(['condicion_fisica' => 'condicion']);
    Route::resource('administracion/relacion_bienes', RelacionBienesController::class)
        ->names('administracion.relacion_bienes')
        ->parameters(['relacion_bienes' => 'biene']);
    Route::resource('administracion/unidad_administrativa', UnidadAdministrativaController::class)
        ->names('administracion.unidad_administrativa');
    Route::resource('administracion/usuario_de_uso', UsuarioDeUsoController::class)
        ->names('administracion.usuario_de_uso');
    Route::resource('administracion/usuario_administrativo', UsuarioAdministrativoController::class)
        ->names('administracion.usuario_administrativo');
    Route::resource('administracion/forma_adquisicion', FormaAdquisicionController::class)
        ->names('administracion.forma_adquisicion');
    Route::resource('/administracion/estado_del_uso', EstadoUsoController::class)
        ->names('administracion.estado_del_uso');


    /* =========================================================================
     * 3. INVENTARIO DE OFICINA
     * ========================================================================= */
    Route::resource('/administracion/inventario_oficinas', InventarioOficController::class)
        ->names('administracion.inventario_oficinas')
        ->parameters(['inventario_oficinas' => 'inventario_oficina']);
    Route::get('/administracion/inventario_oficinas/export/excel', [InventarioOficController::class, 'exportExcel'])
        ->name('administracion.inventario_oficinas.export.excel');
    Route::get('/administracion/inventario_oficinas/export/pdf', [InventarioOficController::class, 'exportPdf'])
        ->name('administracion.inventario_oficinas.export.pdf');
    Route::post('/administracion/inventario_oficinas/destroy-multiple', [InventarioOficController::class, 'destroyMultiple'])
        ->name('administracion.inventario_oficinas.destroyMultiple');
    Route::post('/administracion/inventario_oficinas/destroy-all', [InventarioOficController::class, 'destroyAll'])
        ->name('administracion.inventario_oficinas.destroyAll');

    Route::resource('/administracion/ubicacion', UbicacionController::class)->names('administracion.ubicacion');
    Route::resource('/administracion/proveedores', ProveedorController::class)
        ->names('administracion.proveedores')
        ->parameters(['proveedores' => 'proveedore']);
    Route::resource('/administracion/unidad_medidas', UnidadMedidaController::class)
        ->names('administracion.unidad_medidas')
        ->parameters(['unidad_medidas' => 'unidad_medida']);
    Route::resource('/administracion/articulos', ArticuloController::class)
        ->names('administracion.articulos')
        ->parameters(['articulos' => 'articulo']);
    Route::resource('/administracion/areas', AreaController::class)
        ->names('administracion.areas')
        ->parameters(['areas' => 'area']);
    Route::resource('/administracion/gerencias', GerenciasController::class)
        ->names('administracion.gerencias')
        ->parameters(['gerencias' => 'gerencia']);

    Route::resource('/administracion/salida_oficina', SalidaOficController::class)->names('administracion.salida_oficina');
    Route::post('/administracion/salida_oficina/destroy-multiple', [SalidaOficController::class, 'destroyMultiple'])
        ->name('administracion.salida_oficina.destroyMultiple');
    Route::post('/administracion/salida_oficina/destroy-all', [SalidaOficController::class, 'destroyAll'])
        ->name('administracion.salida_oficina.destroyAll');
    Route::get('/administracion/salida_oficina/export/excel', [SalidaOficController::class, 'exportExcel'])
        ->name('administracion.salida_oficina.export.excel');
    Route::get('/administracion/salida_oficina/export/pdf', [SalidaOficController::class, 'exportPdf'])
        ->name('administracion.salida_oficina.export.pdf');


    /* =========================================================================
     * 4. INVENTARIO DE CONSUMIBLES E IMPRESORAS
     * ========================================================================= */
    Route::get('/api/impresoras', [ImpresoraApiController::class, 'index'])->name('api.impresoras');
    Route::resource('/administracion/impresoras', ImpresoraController::class)
        ->names('administracion.impresoras')
        ->parameters(['impresoras' => 'impresora']);
    Route::resource('/administracion/tipo_consumibles', ColoresController::class)
        ->names('administracion.tipo_consumibles')
        ->parameters(['tipo_consumibles' => 'tipo_consumible']);
    Route::resource('/administracion/tipo_impresoras', FabricanteController::class)
        ->names('administracion.tipo_impresoras')
        ->parameters(['tipo_impresoras' => 'tipo_impresora']);
    Route::resource('/administracion/consumibles', CartuchoController::class)
        ->names('administracion.consumibles')
        ->parameters(['consumibles' => 'consumible']);

    Route::resource('/administracion/inventario_impresoras', InventarioImpresoraController::class)
        ->names('administracion.inventario_impresoras')
        ->parameters(['inventario_impresoras' => 'inventario_impresora']);
    Route::get('/administracion/inventario_impresoras/export/excel', [InventarioImpresoraController::class, 'exportExcel'])
        ->name('administracion.inventario_impresoras.export.excel');
    Route::get('/administracion/inventario_impresoras/export/pdf', [InventarioImpresoraController::class, 'exportPdf'])
        ->name('administracion.inventario_impresoras.export.pdf');

    Route::resource('/administracion/salida_impresora', SalidaImpresoraController::class)
        ->names('administracion.salida_impresora');
    Route::get('/administracion/salida_impresora/export/excel', [SalidaImpresoraController::class, 'exportExcel'])
        ->name('administracion.salida_impresora.export.excel');
    Route::get('/administracion/salida_impresora/export/pdf', [SalidaImpresoraController::class, 'exportPdf'])
        ->name('administracion.salida_impresora.export.pdf');

    Route::resource('/administracion/salida_almacen', SalidaAlmacenamientoImpresoraController::class)
        ->names('administracion.salida_almacen');
    Route::get('/administracion/salida_almacen/export/excel', [SalidaAlmacenamientoImpresoraController::class, 'exportExcel'])
        ->name('administracion.salida_almacen.export.excel');
    Route::get('/administracion/salida_almacen/export/pdf', [SalidaAlmacenamientoImpresoraController::class, 'exportPdf'])
        ->name('administracion.salida_almacen.export.pdf');

    Route::resource('/administracion/almacenamiento_impresoras', AlmacenamientoImpresoraController::class)
        ->names('administracion.almacenamiento_impresoras')
        ->parameters(['almacenamiento_impresoras' => 'almacenamiento_impresora']);
    Route::get('/administracion/almacenamiento_impresoras/export/excel', [AlmacenamientoImpresoraController::class, 'exportExcel'])
        ->name('administracion.almacenamiento_impresoras.export.excel');
    Route::get('/administracion/almacenamiento_impresoras/export/pdf', [AlmacenamientoImpresoraController::class, 'exportPdf'])
        ->name('administracion.almacenamiento_impresoras.export.pdf');

    Route::resource('/administracion/distribucion_almacenamiento_impresoras', DistribucionAlmacenamientoImpresoraController::class)
        ->names('administracion.distribucion_almacenamiento_impresoras')
        ->parameters(['distribucion_almacenamiento_impresoras' => 'distribucion']);
    Route::get('/administracion/distribucion_almacenamiento_impresoras/export/excel', [DistribucionAlmacenamientoImpresoraController::class, 'exportExcel'])
        ->name('administracion.distribucion_almacenamiento_impresoras.export.excel');
    Route::get('/administracion/distribucion_almacenamiento_impresoras/export/pdf', [DistribucionAlmacenamientoImpresoraController::class, 'exportPdf'])
        ->name('administracion.distribucion_almacenamiento_impresoras.export.pdf');

    // Reportes Unificados de Inventario
    Route::get('/administracion/reportes', [UnifiedInventoryController::class, 'index'])->name('administracion.reportes.index');
    Route::get('/administracion/reportes/export/excel', [UnifiedInventoryController::class, 'exportExcel'])->name('administracion.reportes.export.excel');
    Route::get('/administracion/reportes/export/pdf', [UnifiedInventoryController::class, 'exportPdf'])->name('administracion.reportes.export.pdf');
    Route::get('/administracion/reportes/consumption/pdf', [UnifiedInventoryController::class, 'exportConsumptionPdf'])->name('administracion.reportes.consumption.pdf');
    Route::get('/administracion/reportes/suppliers/pdf', [UnifiedInventoryController::class, 'exportSuppliersPdf'])->name('administracion.reportes.suppliers.pdf');
    Route::get('/administracion/reportes/lowstock/pdf', [UnifiedInventoryController::class, 'exportLowStockPdf'])->name('administracion.reportes.lowstock.pdf');
    Route::get('/administracion/reportes/audit/pdf', [UnifiedInventoryController::class, 'exportAuditPdf'])->name('administracion.reportes.audit.pdf');
    Route::get('/administracion/reportes/gerencia-area/pdf', [UnifiedInventoryController::class, 'exportGerenciaAreaReportPdf'])->name('administracion.reportes.gerencia_area.pdf');

    // Registro de actividades accesible desde Reportes de Administración
    Route::get('/gtic/activity-log', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('gtic.activity-log.index');
    Route::get('/gtic/activity-log/export/pdf', [\App\Http\Controllers\ActivityLogController::class, 'exportPdf'])->name('gtic.activity-log.export.pdf');
    Route::get('/gtic/activity-log/export/excel', [\App\Http\Controllers\ActivityLogController::class, 'exportExcel'])->name('gtic.activity-log.export.excel');

    /* =========================================================================
     * 5. REPORTES ADICIONALES Y FORMULARIOS
     * ========================================================================= */
    Route::get('reporte/excel/{usuario_de_uso}/{type}/{f1}/{f2}', [ExportarController::class, 'reporteExcel']);
    Route::get('reporte/excel/{usuario_de_uso}/{type}', [ExportarController::class, 'reporteExcel']);
    Route::get('/pdf', [PdfController::class, 'generateForm']);
});

