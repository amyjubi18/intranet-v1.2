<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DirectoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\ActivityLogController;

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

    // GTIC Module API
    Route::prefix('gtic')->group(function () {
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('api.gtic.activity-log.index');
        Route::get('/activity-log/{id}', [ActivityLogController::class, 'show'])->name('api.gtic.activity-log.show');
    });
});

