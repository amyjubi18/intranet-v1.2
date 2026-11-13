<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DirectoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\BieneController;
use App\Http\Controllers\InventarioOficController;
use App\Http\Controllers\CartuchoController;
use App\Http\Controllers\SalidaOficController;
use App\Http\Controllers\DigitalizacioneController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\EstanteriaController;
use App\Http\Controllers\CategoriaController;

// API routes that need authentication (works with session auth for web)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get("/user", function (Request $request) {
        return $request->user();
    });
    Route::get("/dashboard/metrics", [DashboardController::class, "metrics"])->name("api.dashboard.metrics");
    Route::get("/dashboard/birthdays", [DashboardController::class, "birthdays"])->name("api.dashboard.birthdays");
    Route::post("/dashboard/birthdays/send-wish", [DashboardController::class, "sendBirthdayWish"])->name("api.dashboard.birthdays.send-wish");
    Route::get("/dashboard/user-stats", [DashboardController::class, "userStats"])->name("api.dashboard.user-stats");

    // Directory routes
    Route::get("/directory", [DirectoryController::class, "index"])->name("api.directory.index");
    Route::post("/directory", [DirectoryController::class, "store"])->name("api.directory.store");
    Route::put("/directory/{id}", [DirectoryController::class, "update"])->name("api.directory.update");
    Route::delete("/directory/{id}", [DirectoryController::class, "destroy"])->name("api.directory.destroy");

    // Events routes
    Route::get("/events", [EventController::class, "index"])->name("api.events.index");
    Route::post("/events", [EventController::class, "store"])->name("api.events.store");
    Route::put("/events/{id}", [EventController::class, "update"])->name("api.events.update");
    Route::delete("/events/{id}", [EventController::class, "destroy"])->name("api.events.destroy");
    Route::get("/events/by-date", [EventController::class, "byDate"])->name("api.events.by-date");
    Route::get("/events/upcoming", [EventController::class, "upcoming"])->name("api.events.upcoming");

    // GTIC Module API (ct-gtic: 172.17.50.252)
    Route::prefix('gtic')->group(function () {
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('api.gtic.activity-log.index');
        Route::get('/activity-log/{id}', [ActivityLogController::class, 'show'])->name('api.gtic.activity-log.show');
        Route::get('/users', [UserController::class, 'index'])->name('api.gtic.users.index');
        Route::post('/users', [UserController::class, 'store'])->name('api.gtic.users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('api.gtic.users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('api.gtic.users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('api.gtic.users.destroy');

        // Roles & Permisos
        Route::get('/roles', [RoleController::class, 'index'])->name('api.gtic.roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('api.gtic.roles.store');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('api.gtic.roles.show');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('api.gtic.roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('api.gtic.roles.destroy');

        Route::get('/permisos', [PermissionController::class, 'index'])->name('api.gtic.permisos.index');
        Route::post('/permisos', [PermissionController::class, 'store'])->name('api.gtic.permisos.store');
        Route::get('/permisos/{permission}', [PermissionController::class, 'show'])->name('api.gtic.permisos.show');
        Route::put('/permisos/{permission}', [PermissionController::class, 'update'])->name('api.gtic.permisos.update');
        Route::delete('/permisos/{permission}', [PermissionController::class, 'destroy'])->name('api.gtic.permisos.destroy');
    });

    // General Users API
    Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
    Route::post('/users', [UserController::class, 'store'])->name('api.users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('api.users.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('api.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('api.users.destroy');

    // OGA Module API (ct-admin: 172.17.50.250)
    Route::prefix('administracion')->group(function () {
        Route::get('/bienes_publicos', [BieneController::class, 'index'])->name('api.administracion.bienes_publicos.index');
        Route::get('/inventario_oficinas', [InventarioOficController::class, 'index'])->name('api.administracion.inventario_oficinas.index');
        Route::get('/salida_oficinas', [SalidaOficController::class, 'index'])->name('api.administracion.salida_oficinas.index');
        Route::get('/consumibles', [CartuchoController::class, 'index'])->name('api.administracion.consumibles.index');

        // Documentos
        Route::get('/documentos', [DocsController::class, 'index'])->name('api.administracion.documentos.index');
        Route::post('/documentos', [DocsController::class, 'store'])->name('api.administracion.documentos.store');
        Route::get('/documentos/{id}', [DocsController::class, 'edit'])->name('api.administracion.documentos.show');
        Route::post('/documentos/{id}', [DocsController::class, 'update'])->name('api.administracion.documentos.update');
        Route::put('/documentos/{id}', [DocsController::class, 'update'])->name('api.administracion.documentos.update.put');
        Route::delete('/documentos/{id}', [DocsController::class, 'destroy'])->name('api.administracion.documentos.destroy');
        Route::delete('/documentos/archivo/{id}', [DocsController::class, 'destroyArchivo'])->name('api.administracion.documentos.archivo.destroy');

        // Estanterias
        Route::get('/estanterias', [EstanteriaController::class, 'index'])->name('api.administracion.estanterias.index');
        Route::post('/estanterias', [EstanteriaController::class, 'store'])->name('api.administracion.estanterias.store');
        Route::get('/estanterias/{id}', [EstanteriaController::class, 'edit'])->name('api.administracion.estanterias.show');
        Route::post('/estanterias/{id}', [EstanteriaController::class, 'update'])->name('api.administracion.estanterias.update');
        Route::put('/estanterias/{id}', [EstanteriaController::class, 'update'])->name('api.administracion.estanterias.update.put');
        Route::delete('/estanterias/{id}', [EstanteriaController::class, 'destroy'])->name('api.administracion.estanterias.destroy');

        // Categorias
        Route::get('/categorias', [CategoriaController::class, 'index'])->name('api.administracion.categorias.index');
        Route::post('/categorias', [CategoriaController::class, 'store'])->name('api.administracion.categorias.store');
        Route::post('/categorias/store-ajax', [CategoriaController::class, 'storeAjax'])->name('api.administracion.categorias.store-ajax');
        Route::get('/categorias/{id}', [CategoriaController::class, 'edit'])->name('api.administracion.categorias.show');
        Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('api.administracion.categorias.update');
        Route::post('/categorias/{id}', [CategoriaController::class, 'update'])->name('api.administracion.categorias.update.post');
        Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('api.administracion.categorias.destroy');
    });

    // OGH Module API (ct-gh: 172.17.50.251)
    Route::prefix('gestion-humana')->group(function () {
        Route::get('/digitalizacion_documentos', [DigitalizacioneController::class, 'index'])->name('api.gestion_humana.digitalizacion_documentos.index');
    });
});
