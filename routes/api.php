<?php

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('dashboard')->controller(DashboardController::class)->group(function () {
    Route::get('/stats', 'stats'); // Accepts ?range=week|month|year
    Route::get('/recent-bills', 'recentBills'); // Accepts ?range=week|month
    Route::get('/bills-missing-final-samples', 'billsMissingFinalSamples');
    Route::get('/containers-high-moisture', 'containersHighMoisture');
    Route::get('/moisture-distribution', 'moistureDistribution');
});