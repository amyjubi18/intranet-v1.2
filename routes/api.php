<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
Route::middleware("auth:sanctum")->get("/user", function (Request $request) {
return $request->user();
});
Route::get("/dashboard/metrics", [DashboardController::class, "metrics"])->name("api.dashboard.metrics");

