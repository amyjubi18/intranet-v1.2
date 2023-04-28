<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoriaController;
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
use App\Http\Controllers\CategoriaGeController;
use App\Http\Controllers\CategoriaEsController;
use App\Http\Controllers\SubCategoriaController;
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

Route::middleware('auth')->group(function () {




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

    //permisos
    Route::resource('gtic/permisos', PermissionController::class)->names('gtic.permisos')->parameters(['permisos' => 'permission']);


    //roles
    Route::resource('gtic/roles', RoleController::class)->names('gtic.roles');

    //Administracion

    Route::resource('administracion/docs', DocsController::class)->names('administracion.docs')->parameters(['docs' => 'documento']);
    ///Categoria--------//
    Route::resource('/administracion/categorias', CategoriaController::class)->names('administracion.categorias');

    //Exportar excel
    Route::get('/administracion/bienes_publicos/index', [ExportController::class, 'index'])->name('index');
    Route::get('/export', [ExportController::class, 'export'])->name('export');

    });

    /*Bienes*/
    Route::resource('administracion/bienes_publicos', BieneController::class)->names('administracion.bienes_publicos')->parameters(['bienes_publicos' => 'biene']);

    /*Unidad Administrativa*/
    Route::resource('administracion/unidad_administrativa', UnidadAdministrativaController::class)->names('administracion.unidad_administrativa');

    /*Forma de adquisición*/
    Route::resource('administracion/forma_adquisicion', FormaAdquisicionController::class)->names('administracion.forma_adquisicion');


/*Estado del uso*/
Route::resource('/administracion/estado_del_uso', EstadoUsoController::class)->names('administracion.estado_del_uso');


/*Categoria General*/
Route::resource('/administracion/categoria_general', CategoriaGeController::class)->names('administracion.categoria_general');


/*SubCategoria*/
Route::resource('/administracion/sub_categoria', SubCategoriaController::class)->names('administracion.sub_categoria')->parameters(['sub_categoria' => 'sub_categoria']);


/*Categoria Especifica*/
Route::resource('/administracion/categoria_especifica', CategoriaEsController::class)->names('administracion.categoria_especifica');

require __DIR__.'/auth.php';
