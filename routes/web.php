<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\UsuarioDeUsoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListadoController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BieneController;
use App\Http\Controllers\EstadoUsoController;
use App\Http\Controllers\FormaAdquisicionController;
use App\Http\Controllers\UnidadAdministrativaController;
use App\Http\Controllers\RelacionBienesController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CondicionController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\UsuarioAdministrativoController;
use App\Http\Controllers\MedicamentosIngresosController;
use App\Http\Controllers\MedicamentosEgresoController;
use App\Http\Controllers\PresentacionController;
use App\Http\Livewire\ReportesController;
use App\Http\Controllers\ExportarController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\DirectivoController;
use App\Http\Controllers\CdtController;
use App\Http\Controllers\ContratadoController;
use App\Http\Controllers\AbogadoController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\InventarioOficController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\SalidaOficController;
use App\Http\Controllers\ImpresoraController;
use App\Http\Controllers\FabricanteController;
use App\Http\Controllers\ColoresController;
use App\Http\Controllers\GerenciasController;
use App\Http\Controllers\CartuchoController;
use App\Http\Controllers\InventarioImpresoraController;
use App\Http\Controllers\SalidaImpresoraController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AreaAdscritaController;
use App\Http\Controllers\DigitalizacioneController;
use App\Http\Controllers\TipoDocumentoController;
use App\Http\Controllers\Api\ImpresoraApiController;
use App\Http\Controllers\AlmacenamientoImpresoraController;
use App\Http\Controllers\DistribucionAlmacenamientoImpresoraController;
use App\Http\Controllers\SalidaAlmacenamientoImpresoraController;
use App\Http\Controllers\UnifiedInventoryController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\EstanteriaController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ConstanciaTrabajoController;
use App\Http\Controllers\ReciboPagoController;
use App\Http\Controllers\NoticiaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('login');
})->name('Inicio');

Route::get('/inicio', function () {
    return view('inicio');
})->middleware(['auth', 'verified'])->name('inicio');

Route::middleware(['auth', 'session.timeout', 'prevent.concurrent'])->group(function () {

// API routes that need auth
Route::get('/api/impresoras', [ImpresoraApiController::class, 'index'])->name('api.impresoras');


    Route::get('reportes', ReportesController::class);

    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.delete');



    Route::get('/gtic/register', [RegisteredUserController::class, 'create'])->name('gtic.register');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



 Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->middleware('guest')->name('password.request');


Route::get('/pdf', 'PdfController@generateForm');


    //permisos
    Route::resource('gtic/permisos', PermissionController::class)->names('gtic.permisos')->parameters(['permisos' => 'permission']);
    // Ruta para verificar nombre de permiso único
    Route::get('/gtic/permisos/check-name', [PermissionController::class, 'checkName'])->name('gtic.permisos.check-name');


    //roles
    Route::resource('gtic/roles', RoleController::class)->names('gtic.roles');
    // Ruta para verificar nombre de rol único
    Route::get('/gtic/roles/check-name', [RoleController::class, 'checkName'])->name('gtic.roles.check-name');

    //Administracion

    Route::resource('administracion/documentos', DocsController::class)->names('administracion.documentos')->parameters(['documentos' => 'documento']);

    // Ruta para eliminar un archivo específico
    Route::delete('/administracion/documentos/archivo/{archivo}', [DocsController::class, 'destroyArchivo'])->name('administracion.documentos.archivo.destroy');

    //Estanterías
    Route::resource('/administracion/estanterias', EstanteriaController::class)->names('administracion.estanterias');

    ///Categoria--------//
    Route::resource('/administracion/categorias', CategoriaController::class)->names('administracion.categorias');
    // Ruta para crear categoría vía AJAX desde modal
    Route::post('/administracion/categorias/store-ajax', [CategoriaController::class, 'storeAjax'])->name('administracion.categorias.storeAjax');

    //Exportar excel
    Route::get('/administracion/bienes_publicos/index', [ExportController::class, 'index'])->name('index');
    Route::get('/export', [ExportController::class, 'export'])->name('export');



    /*Bienes*/
    Route::resource('administracion/bienes_publicos', BieneController::class)->names('administracion.bienes_publicos')->parameters(['bienes_publicos' => 'biene']);
    Route::get('/administracion/bienes_publicos/export/excel', [BieneController::class, 'exportExcel'])->name('administracion.bienes_publicos.export.excel');
    Route::get('/administracion/bienes_publicos/export/pdf', [BieneController::class, 'exportPdf'])->name('administracion.bienes_publicos.export.pdf');

    // QR Code routes
    Route::get('/bienes/qr/{n_interno_bien}', [BieneController::class, 'scanQrCode'])->name('bienes.qr.scan');
    Route::post('/api/bienes/qr/buscar', [BieneController::class, 'findByQrCode'])->name('api.bienes.qr.buscar');


    Route::get('/api/bienes_publicos/usuarios-de-uso/{usuario_admin_id}', [BieneController::class, 'getUsuariosDeUso'])->name('api.bienes_publicos.usuarios_de_uso');
    Route::get('/api/bienes_publicos/usuarios-administrativos/{unidad_id}', [BieneController::class, 'getUsuariosAdministrativos'])->name('api.bienes_publicos.usuarios_administrativos');
    Route::get('/api/bienes_publicos/descripciones', [BieneController::class, 'getDescripciones'])->name('api.bienes_publicos.descripciones');
    Route::post('/api/bienes_publicos/descripciones', [BieneController::class, 'storeDescripcion'])->name('api.bienes_publicos.descripciones.store');

     /*Colores*/
     Route::resource('administracion/colors', ColorController::class)->names('administracion.colors')->parameters(['colors' => 'color']);

     /*Condicion*/
     Route::resource('administracion/condicion_fisica', CondicionController::class)->names('administracion.condicion_fisica')->parameters(['condicion_fisica' => 'condicion']);

    /*Relacion de Bienes*/
    Route::resource('administracion/relacion_bienes', RelacionBienesController::class)->names('administracion.relacion_bienes')->parameters(['relacion_bienes' => 'biene']);

    /*Unidad Administrativa*/
    Route::resource('administracion/unidad_administrativa', UnidadAdministrativaController::class)->names('administracion.unidad_administrativa');

    /*Usuario de Uso*/
    Route::resource('administracion/usuario_de_uso', UsuarioDeUsoController::class)->names('administracion.usuario_de_uso');


    /*Usuario Administrativo*/
    Route::resource('administracion/usuario_administrativo', UsuarioAdministrativoController::class)->names('administracion.usuario_administrativo');


    /*Forma de adquisición*/
    Route::resource('administracion/forma_adquisicion', FormaAdquisicionController::class)->names('administracion.forma_adquisicion');


/*Estado del uso*/
Route::resource('/administracion/estado_del_uso', EstadoUsoController::class)->names('administracion.estado_del_uso');

/* INVENTARIO OFICINA */
//inventario Oficina
Route::resource('/administracion/inventario_oficinas', InventarioOficController::class)->names('administracion.inventario_oficinas')->parameters(['inventario_oficinas' => 'inventario_oficina']);
Route::get('/administracion/inventario_oficinas/export/excel', [InventarioOficController::class, 'exportExcel'])->name('administracion.inventario_oficinas.export.excel');
Route::get('/administracion/inventario_oficinas/export/pdf', [InventarioOficController::class, 'exportPdf'])->name('administracion.inventario_oficinas.export.pdf');
Route::post('/administracion/inventario_oficinas/destroy-multiple', [InventarioOficController::class, 'destroyMultiple'])->name('administracion.inventario_oficinas.destroyMultiple');
Route::post('/administracion/inventario_oficinas/destroy-all', [InventarioOficController::class, 'destroyAll'])->name('administracion.inventario_oficinas.destroyAll');

 //Ubicacion
 Route::resource('/administracion/ubicacion', UbicacionController::class)->names('administracion.ubicacion') ;

 //Proveedor
 Route::resource('/administracion/proveedores', ProveedorController::class)->names('administracion.proveedores')->parameters(['proveedores' => 'proveedore']) ;
 //Unidad
 Route::resource('/administracion/unidad_medidas', UnidadMedidaController::class)->names('administracion.unidad_medidas')->parameters(['unidad_medidas' => 'unidad_medida']) ;

 //Articulos
 Route::resource('/administracion/articulos', ArticuloController::class)->names('administracion.articulos')->parameters(['articulos' => 'articulo']) ;

 //Areas
 Route::resource('/administracion/areas', AreaController::class)->names('administracion.areas')->parameters(['areas' => 'area']) ;
 //Gerencias
 Route::resource('/administracion/gerencias', GerenciasController::class)->names('administracion.gerencias')->parameters(['gerencias' => 'gerencia']) ;

//Salida oficina
 Route::resource('/administracion/salida_oficina', SalidaOficController::class)->names('administracion.salida_oficina');
 Route::post('/administracion/salida_oficina/destroy-multiple', [SalidaOficController::class, 'destroyMultiple'])->name('administracion.salida_oficina.destroyMultiple');
 Route::post('/administracion/salida_oficina/destroy-all', [SalidaOficController::class, 'destroyAll'])->name('administracion.salida_oficina.destroyAll');


/* DIGITALIZACION DE DOCUMENTOS OGH */

    Route::resource('/gestion-humana/area_adscrita', AreaAdscritaController::class)->names('gestion-humana.area_adscrita')->parameters(['area_adscrita' => 'area_adscrita']);

Route::resource('/gestion-humana/digitalizacion_documentos', DigitalizacioneController::class)->names('gestion-humana.digitalizacion_documentos')->parameters(['digitalizacion_documentos' => 'digitalizacione']);

    // Ruta para eliminar un documento específico
    Route::delete('/gestion-humana/digitalizacion_documentos/documento/{documento}', [DigitalizacioneController::class, 'destroyDocumento'])->name('gestion-humana.digitalizacion_documentos.documento.destroy');

    Route::resource('/gestion-humana/tipo_documentos', TipoDocumentoController::class)->names('gestion-humana.tipo_documentos')->parameters(['tipo_documentos' => 'tipo_documento']);







 /* ------------INVENTARIO IMPRESORAS---------- */


    //Impresoras
    Route::resource('/administracion/impresoras', ImpresoraController::class)->names('administracion.impresoras')->parameters(['impresoras' => 'impresora']) ;
    //tipo_consumibles (was colores)
    Route::resource('/administracion/tipo_consumibles', ColoresController::class)->names('administracion.tipo_consumibles')->parameters(['tipo_consumibles' => 'tipo_consumible']) ;
    //tipo_impresoras (was tipo_consumibles)
    Route::resource('/administracion/tipo_impresoras', FabricanteController::class)->names('administracion.tipo_impresoras')->parameters(['tipo_impresoras' => 'tipo_impresora']) ;
    //consumibles
    Route::resource('/administracion/consumibles', CartuchoController::class)->names('administracion.consumibles')->parameters(['consumibles' => 'consumible']) ;
    //inventario impresora
    Route::resource('/administracion/inventario_impresoras', InventarioImpresoraController::class)->names('administracion.inventario_impresoras')->parameters(['inventario_impresoras' => 'inventario_impresora']);
    Route::get('/administracion/inventario_impresoras/export/excel', [InventarioImpresoraController::class, 'exportExcel'])->name('administracion.inventario_impresoras.export.excel');
    Route::get('/administracion/inventario_impresoras/export/pdf', [InventarioImpresoraController::class, 'exportPdf'])->name('administracion.inventario_impresoras.export.pdf');
    //Salida impresora
    Route::resource('/administracion/salida_impresora', SalidaImpresoraController::class)->names('administracion.salida_impresora');
    Route::get('/administracion/salida_impresora/export/excel', [SalidaImpresoraController::class, 'exportExcel'])->name('administracion.salida_impresora.export.excel');
    Route::get('/administracion/salida_impresora/export/pdf', [SalidaImpresoraController::class, 'exportPdf'])->name('administracion.salida_impresora.export.pdf');

    //Salida almacenamiento impresora
    Route::resource('/administracion/salida_almacen', SalidaAlmacenamientoImpresoraController::class)->names('administracion.salida_almacen');
    Route::get('/administracion/salida_almacen/export/excel', [SalidaAlmacenamientoImpresoraController::class, 'exportExcel'])->name('administracion.salida_almacen.export.excel');
    Route::get('/administracion/salida_almacen/export/pdf', [SalidaAlmacenamientoImpresoraController::class, 'exportPdf'])->name('administracion.salida_almacen.export.pdf');

    //Salida almacenamiento impresora
    Route::resource('/administracion/salida_almacen', SalidaAlmacenamientoImpresoraController::class)->names('administracion.salida_almacen');
    Route::get('/administracion/salida_almacen/export/excel', [SalidaAlmacenamientoImpresoraController::class, 'exportExcel'])->name('administracion.salida_almacen.export.excel');
    Route::get('/administracion/salida_almacen/export/pdf', [SalidaAlmacenamientoImpresoraController::class, 'exportPdf'])->name('administracion.salida_almacen.export.pdf');

    //Salida oficina
    Route::resource('/administracion/salida_oficina', \App\Http\Controllers\SalidaOficController::class)->names('administracion.salida_oficina');
    Route::get('/administracion/salida_oficina/export/excel', [\App\Http\Controllers\SalidaOficController::class, 'exportExcel'])->name('administracion.salida_oficina.export.excel');
    Route::get('/administracion/salida_oficina/export/pdf', [\App\Http\Controllers\SalidaOficController::class, 'exportPdf'])->name('administracion.salida_oficina.export.pdf');

    //Almacenamiento impresora
    Route::resource('/administracion/almacenamiento_impresoras', AlmacenamientoImpresoraController::class)->names('administracion.almacenamiento_impresoras')->parameters(['almacenamiento_impresoras' => 'almacenamiento_impresora']);
    Route::get('/administracion/almacenamiento_impresoras/export/excel', [AlmacenamientoImpresoraController::class, 'exportExcel'])->name('administracion.almacenamiento_impresoras.export.excel');
    Route::get('/administracion/almacenamiento_impresoras/export/pdf', [AlmacenamientoImpresoraController::class, 'exportPdf'])->name('administracion.almacenamiento_impresoras.export.pdf');

    //Distribución Almacenamiento Impresora
    Route::resource('/administracion/distribucion_almacenamiento_impresoras', DistribucionAlmacenamientoImpresoraController::class)->names('administracion.distribucion_almacenamiento_impresoras')->parameters(['distribucion_almacenamiento_impresoras' => 'distribucion']);
    Route::get('/administracion/distribucion_almacenamiento_impresoras/export/excel', [DistribucionAlmacenamientoImpresoraController::class, 'exportExcel'])->name('administracion.distribucion_almacenamiento_impresoras.export.excel');
    Route::get('/administracion/distribucion_almacenamiento_impresoras/export/pdf', [DistribucionAlmacenamientoImpresoraController::class, 'exportPdf'])->name('administracion.distribucion_almacenamiento_impresoras.export.pdf');

    //Inventario Unificado
    Route::get('/administracion/reportes', [UnifiedInventoryController::class, 'index'])->name('administracion.reportes.index');

    Route::get('/administracion/reportes/export/excel', [UnifiedInventoryController::class, 'exportExcel'])->name('administracion.reportes.export.excel');
    Route::get('/administracion/reportes/export/pdf', [UnifiedInventoryController::class, 'exportPdf'])->name('administracion.reportes.export.pdf');
    Route::get('/administracion/reportes/consumption/pdf', [UnifiedInventoryController::class, 'exportConsumptionPdf'])->name('administracion.reportes.consumption.pdf');
    Route::get('/administracion/reportes/suppliers/pdf', [UnifiedInventoryController::class, 'exportSuppliersPdf'])->name('administracion.reportes.suppliers.pdf');
    Route::get('/administracion/reportes/lowstock/pdf', [UnifiedInventoryController::class, 'exportLowStockPdf'])->name('administracion.reportes.lowstock.pdf');
Route::get('/administracion/reportes/audit/pdf', [UnifiedInventoryController::class, 'exportAuditPdf'])->name('administracion.reportes.audit.pdf');

Route::get('/administracion/reportes/gerencia-area/pdf', [UnifiedInventoryController::class, 'exportGerenciaAreaReportPdf'])->name('administracion.reportes.gerencia_area.pdf');

    // Route for report by gerencia and area PDF
//    Route::get('/administracion/reportes/gerencia-area/pdf', [\App\Http\Controllers\UnifiedInventoryController::class, 'exportGerenciaAreaReportPdf'])->name('administracion.reportes.gerencia_area.pdf');

    // Activity Log
    Route::get('/gtic/activity-log', [ActivityLogController::class, 'index'])->name('gtic.activity-log.index');
    Route::get('/gtic/activity-log/{id}', [ActivityLogController::class, 'show'])->name('gtic.activity-log.show');
    Route::get('/gtic/activity-log/export/excel', [ActivityLogController::class, 'exportExcel'])->name('gtic.activity-log.export.excel');
    Route::get('/gtic/activity-log/export/pdf', [ActivityLogController::class, 'exportPdf'])->name('gtic.activity-log.export.pdf');

    // Activity Log (Security Activity)
    Route::get('/gtic/security-activity', [ActivityLogController::class, 'index'])->name('gtic.security-activity.index');
    Route::get('/gtic/security-activity/export', [ActivityLogController::class, 'export'])->name('gtic.security-activity.export');
    Route::get('/gtic/security-activity/failed', [ActivityLogController::class, 'failedAttempts'])->name('gtic.security-activity.failed');

/*-----------------REGISTRO DE INSUMOS-----------------------------*/




/*presentacion*/


Route::get('reporte/excel/{usuario_de_uso}/{type}/{f1}/{f2}', [ExportarController::class, 'reporteExcel']);
//dia
Route::get('reporte/excel/{usuario_de_uso}/{type}', [ExportarController::class, 'reporteExcel']);


Route::view('/examples', 'examples.index')->name('examples');
Route::get('/examples/react-integration', function() {
    return view('ejemplos.react-integration');
})->name('examples.react-integration');

Route::get('/{page?}', [DocsController::class, 'show'])->name('doc');

Route::view('/prueba', 'prueba');
});
require __DIR__.'/auth.php';
Route::get('/register', [RegisterController::class, 'create'])->name('register.create');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::get('/register/{id}/pdf', [RegisterController::class, 'generatePDF'])->name('register.pdf');

// Rutas de búsqueda
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/buscar', [BusquedaController::class, 'search'])->name('api.buscar');
    Route::get('/buscar/ruta/{tipo}/{id}', [BusquedaController::class, 'getRoute'])->name('api.buscar.ruta');
});

// Rutas de notificaciones
Route::middleware(['auth'])->prefix('api/notificaciones')->group(function () {
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.notificaciones.markAllRead');
    Route::post('/mark-read/{type}', [NotificationController::class, 'markAsRead'])->name('api.notificaciones.markRead');
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('api.notificaciones.unreadCount');
});

// Rutas de Constancia de Trabajo
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::post('/constancia-trabajo/solicitar', [ConstanciaTrabajoController::class, 'sendRequest'])->name('constancia-trabajo.solicitar');
});

// Rutas de Recibo de Pago
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::post('/recibo-pago/solicitar', [ReciboPagoController::class, 'sendRequest'])->name('recibo-pago.solicitar');
});

// Rutas de noticias locales
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/noticias', [NoticiaController::class, 'apiIndex'])->name('api.noticias.index');
    Route::get('/noticias/{id}', [NoticiaController::class, 'apiShow'])->name('api.noticias.show');
    Route::post('/noticias', [NoticiaController::class, 'apiStore'])->name('api.noticias.store');
});

// Rutas de gestión de noticias (solo para admin, gtic, gerente, coordinacion)
Route::middleware(['auth'])->group(function () {
    Route::get('/noticias', [NoticiaController::class, 'index'])->name('noticias.index');
    Route::get('/noticias/create', [NoticiaController::class, 'create'])->name('noticias.create');
    Route::post('/noticias', [NoticiaController::class, 'store'])->name('noticias.store');
    Route::get('/noticias/{id}/edit', [NoticiaController::class, 'edit'])->name('noticias.edit');
    Route::put('/noticias/{id}', [NoticiaController::class, 'update'])->name('noticias.update');
    Route::delete('/noticias/{id}', [NoticiaController::class, 'destroy'])->name('noticias.destroy');
    Route::post('/noticias/publish-all', [NoticiaController::class, 'publishAll'])->name('noticias.publishAll');
});

// Rutas de Single Sign-On (SSO) inter-módulos
Route::get('/sso/go/{module}', [App\Http\Controllers\Auth\SSOController::class, 'go'])->name('sso.go')->middleware('auth');
Route::get('/sso/verify', [App\Http\Controllers\Auth\SSOController::class, 'verify'])->name('sso.verify');


