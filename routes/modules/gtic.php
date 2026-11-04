<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Livewire\ReportesController;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo: GTIC (Tecnología de la Información y Comunicación)
|--------------------------------------------------------------------------
| Contenedor: ct-gtic (172.17.50.252)
*/

Route::middleware(['auth', 'session.timeout', 'prevent.concurrent'])->group(function () {

    // Gestión de Usuarios
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.delete');

    Route::get('/gtic/register', [RegisteredUserController::class, 'create'])->name('gtic.register');

    // Permisos
    Route::resource('gtic/permisos', PermissionController::class)
        ->names('gtic.permisos')
        ->parameters(['permisos' => 'permission']);
    Route::get('/gtic/permisos/check-name', [PermissionController::class, 'checkName'])->name('gtic.permisos.check-name');

    // Roles
    Route::resource('gtic/roles', RoleController::class)
        ->names('gtic.roles');
    Route::get('/gtic/roles/check-name', [RoleController::class, 'checkName'])->name('gtic.roles.check-name');

    // Activity Log
    Route::get('/gtic/activity-log', [ActivityLogController::class, 'index'])->name('gtic.activity-log.index');
    Route::get('/gtic/activity-log/{id}', [ActivityLogController::class, 'show'])->name('gtic.activity-log.show');
    Route::get('/gtic/activity-log/export/excel', [ActivityLogController::class, 'exportExcel'])->name('gtic.activity-log.export.excel');
    Route::get('/gtic/activity-log/export/pdf', [ActivityLogController::class, 'exportPdf'])->name('gtic.activity-log.export.pdf');

    // Security Activity
    Route::get('/gtic/security-activity', [ActivityLogController::class, 'index'])->name('gtic.security-activity.index');
    Route::get('/gtic/security-activity/export', [ActivityLogController::class, 'export'])->name('gtic.security-activity.export');
    Route::get('/gtic/security-activity/failed', [ActivityLogController::class, 'failedAttempts'])->name('gtic.security-activity.failed');

    // Reportes Livewire
    Route::get('reportes', ReportesController::class);
});
