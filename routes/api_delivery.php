<?php

use App\Http\Controllers\Delivery\AreaController;
use App\Http\Controllers\Delivery\AuthController;
use App\Http\Controllers\Delivery\GovernmentController;
use App\Http\Controllers\Delivery\OrderController;
use App\Http\Controllers\Delivery\ShiftController;
use App\Http\Controllers\Delivery\TicketController;
use Illuminate\Support\Facades\Route;



Route::prefix('register')->group(function () {

    // get governments and governments areas for register page
    Route::get('/governments', [GovernmentController::class, 'index']);
    Route::get('/governments/{government}/areas', [AreaController::class, 'index']);

    // get all shifts
    Route::get('/shifts', [ShiftController::class, 'index']);
});

Route::prefix('delivery')->group(function () {

    // auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // password reset
    Route::get('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);


    Route::middleware('auth:api_delivery')->group(function () {

        // shift
        Route::post('/start', [ShiftController::class, 'startShift']);
        Route::post('/end', [ShiftController::class, 'endShift']);

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/profile/update', [AuthController::class, 'updateProfile']);

        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);

            Route::post('/{order}/status', [OrderController::class, 'updateStatus']);

            Route::post('/{order}/report', [TicketController::class, 'store']);

            Route::post('/{order}/accept', [OrderController::class, 'accept']);
            Route::post('/{order}/reject', [OrderController::class, 'reject']);
        });
    });
});
