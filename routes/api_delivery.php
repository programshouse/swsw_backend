<?php

use App\Http\Controllers\Delivery\AuthController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;




Route::middleware('auth:sanctum')->group(function () {
    
    // get governments and governments areas for register page
    Route::get('/governments', [AuthController::class, 'get_all_governments']);
    Route::get('/governments/{government}/areas', [AuthController::class, 'get_government_areas']);
    
    // get all shifts
    Route::get('/shifts', [ShiftController::class, 'index']);
});
