<?php

use App\Http\Controllers\Api\V1\BillController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::apiResource('bills', BillController::class);
});

/*
Route::prefix('v1')->group(function () {
    // Bills
    Route::get('/bills', 'BillController@index');           // Danh sách + search + pagination
    Route::post('/bills', 'BillController@store');          // Tạo mới
    Route::get('/bills/{id}', 'BillController@show');       // Chi tiết
    Route::put('/bills/{id}', 'BillController@update');     // Cập nhật
    Route::delete('/bills/{id}', 'BillController@destroy'); // Xóa
    
    // Containers
    Route::get('/bills/{billId}/containers', 'ContainerController@index');
    Route::post('/bills/{billId}/containers', 'ContainerController@store');
    Route::put('/containers/{id}', 'ContainerController@update');
    Route::delete('/containers/{id}', 'ContainerController@destroy');
    
    // Cutting Tests
    Route::get('/bills/{billId}/cutting-tests', 'CuttingTestController@index');
    Route::post('/bills/{billId}/cutting-tests', 'CuttingTestController@store');
    Route::get('/containers/{containerId}/cutting-tests', 'CuttingTestController@containerTests');
    Route::post('/containers/{containerId}/cutting-tests', 'CuttingTestController@storeContainerTest');
    Route::put('/cutting-tests/{id}', 'CuttingTestController@update');
    Route::delete('/cutting-tests/{id}', 'CuttingTestController@destroy');
});
*/