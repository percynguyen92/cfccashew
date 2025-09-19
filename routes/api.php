<?php

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Dashboard API routes
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/recent-bills', [DashboardController::class, 'recentBills']);
    Route::get('/bills-pending-final-tests', [DashboardController::class, 'billsPendingFinalTests']);
    Route::get('/bills-missing-final-samples', [DashboardController::class, 'billsMissingFinalSamples']);
    Route::get('/containers-high-moisture', [DashboardController::class, 'containersHighMoisture']);
    Route::get('/cutting-tests-high-moisture', [DashboardController::class, 'cuttingTestsHighMoisture']);
    Route::get('/moisture-distribution', [DashboardController::class, 'moistureDistribution']);
});