<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\CuttingTestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Resource routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('bills', BillController::class);
    Route::resource('containers', ContainerController::class);
    Route::resource('cutting-tests', CuttingTestController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
