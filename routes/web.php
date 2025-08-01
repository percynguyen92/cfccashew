<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillController;

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

Route::get('/', [BillController::class, 'index'])->name('app.index');

/*
Route::get('/', function () {
    return redirect()->route('bills.index');
});

Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');

*/