<?php

use App\Http\Controllers\CommissionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
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

Route::post('/owner', [UserController::class, 'owner']);
Route::post('/users/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (){

    Route::group(['prefix' => '/users'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        
        Route::post('/{id}/pay', [UserController::class, 'paySalaries']);
    });
    
    Route::group(['prefix' => '/tickets'], function (){
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/{id}', [TicketController::class, 'show']);
        Route::post('/', [TicketController::class, 'store']);
        Route::put('/{id}', [TicketController::class, 'update']);
        Route::delete('/{id}', [TicketController::class, 'destroy']);
    });

    Route::group(['prefix' => '/commissions'], function (){
        Route::get('/', [CommissionController::class, 'index']);
        Route::get('/{id}', [CommissionController::class, 'show']);
        Route::delete('/{id}', [CommissionController::class, 'destroy']);
    });

});
