<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ConstanciaTrabajoController;
use App\Http\Controllers\ReciboPagoController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\Auth\SSOController;

/*
|--------------------------------------------------------------------------
| Web Routes - Portal Central & Núcleo Compartido
|--------------------------------------------------------------------------
*/

// Ruta raíz: redirige al inicio
Route::get('/', function () {
    return redirect()->route('inicio');
});

// Pantalla principal del sistema
Route::get('/inicio', function () {
    $currentModule = config('modules.current');
    $currentHost = request()->getHost();
    $mainHost = parse_url(preg_match('#^https?://#', config('modules.main_url', 'http://172.17.50.219')) ? config('modules.main_url', 'http://172.17.50.219') : 'http://' . config('modules.main_url', 'http://172.17.50.219'), PHP_URL_HOST);

    $isMain = empty($currentModule) || in_array($currentModule, ['main', 'portal', 'all']) || ($currentHost === $mainHost) || str_contains($currentHost, '219') || str_contains($currentHost, 'localhost');

    if (!$isMain) {
        return redirect()->route('sso.go', 'main');
    }

    return view('inicio');
})->middleware(['auth', 'verified'])->name('inicio');

// Rutas de autenticación (Login, Logout, Registro, Contraseñas)
require __DIR__ . '/auth.php';

// Perfil de usuario (común para todos los entornos)
Route::middleware(['auth', 'session.timeout', 'prevent.concurrent'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->middleware('guest')->name('password.request');
});

/* =========================================================================
 * APIs y Servicios Compartidos
 * ========================================================================= */
Route::get('/buscar', [BusquedaController::class, 'search'])->middleware(['auth']);

Route::middleware(['auth'])->prefix('api')->group(function () {
    // Búsqueda
    Route::get('/buscar', [BusquedaController::class, 'search'])->name('api.buscar');
    Route::get('/buscar/ruta/{tipo}/{id}', [BusquedaController::class, 'getRoute'])->name('api.buscar.ruta');

    // Notificaciones
    Route::prefix('notificaciones')->group(function () {
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.notificaciones.markAllRead');
        Route::post('/mark-read/{type}', [NotificationController::class, 'markAsRead'])->name('api.notificaciones.markRead');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('api.notificaciones.unreadCount');
    });

    // Solicitudes de RRHH / Personal
    Route::post('/constancia-trabajo/solicitar', [ConstanciaTrabajoController::class, 'sendRequest'])->name('constancia-trabajo.solicitar');
    Route::post('/recibo-pago/solicitar', [ReciboPagoController::class, 'sendRequest'])->name('recibo-pago.solicitar');

    // Noticias API
    Route::get('/noticias', [NoticiaController::class, 'apiIndex'])->name('api.noticias.index');
    Route::get('/noticias/{id}', [NoticiaController::class, 'apiShow'])->name('api.noticias.show');
    Route::post('/noticias', [NoticiaController::class, 'apiStore'])->name('api.noticias.store');
});

// Gestión de Noticias
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
Route::get('/sso/go/{module}', [SSOController::class, 'go'])->name('sso.go')->middleware('auth');
Route::get('/sso/verify', [SSOController::class, 'verify'])->name('sso.verify');


/* =========================================================================
 * CARGA MODULAR SEGÚN EL ENTORNO (APP_MODULE en .env)
 * =========================================================================
 * Si no está configurado o es 'main'/'portal', carga todos los módulos
 * (ideal para el portal principal 172.17.50.219 y desarrollo local).
 * Si es 'administracion', 'gestion-humana' o 'gtic', carga ÚNICAMENTE lo suyo.
 * ========================================================================= */
$currentModule = config('modules.current');
$isAll = empty($currentModule) || in_array($currentModule, ['main', 'portal', 'all']);

if ($isAll || in_array($currentModule, ['administracion', 'admin'])) {
    require __DIR__ . '/modules/administracion.php';
}

if ($isAll || in_array($currentModule, ['gestion-humana', 'gh', 'gestion_humana'])) {
    require __DIR__ . '/modules/gestion_humana.php';
}

if ($isAll || in_array($currentModule, ['gtic'])) {
    require __DIR__ . '/modules/gtic.php';
}
