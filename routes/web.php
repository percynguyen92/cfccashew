<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\CuttingTestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Resource routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('bills', BillController::class);
    
    // Container routes with custom show route for container number
    Route::resource('containers', ContainerController::class)->except(['show']);
    Route::get('containers/{container}', [ContainerController::class, 'show'])
        ->name('containers.show')
        ->where('container', '[A-Z]{4}\d{7}|\d+'); // Match container number format or ID
    
    Route::resource('cutting-tests', CuttingTestController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
