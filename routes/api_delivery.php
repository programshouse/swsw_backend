<?php

use App\Http\Controllers\Delivery\AreaController;
use App\Http\Controllers\Delivery\AuthController;
use App\Http\Controllers\Delivery\GovernmentController;
use App\Http\Controllers\Delivery\OrderController;
use App\Http\Controllers\Delivery\RateStoreController;
use App\Http\Controllers\Delivery\ShiftController;
use App\Http\Controllers\Delivery\TicketController;
use App\Http\Controllers\Delivery\VehicleController;
use App\Http\Controllers\Delivery\EmailOtpPasswordController;
use App\Http\Controllers\Delivery\OfferController;
use App\Http\Controllers\Delivery\PointController;
use App\Http\Controllers\Delivery\DeliveryOfferController;
use App\Http\Controllers\orders\OrderHistoryController;
use App\Http\Controllers\Admin\AppPageController;
use App\Http\Middleware\EnsureDeliveryWorking;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseTokenController;
use App\Http\Controllers\NotificationController;




Route::post('delivery/password/send-otp', [EmailOtpPasswordController::class, 'sendOtp']);
Route::post('delivery/password/verify-otp', [EmailOtpPasswordController::class, 'verifyOtp']);
Route::post('delivery/password/reset', [EmailOtpPasswordController::class, 'resetPassword']);

Route::prefix('register')->group(function () {

    // get governments and governments areas for register page
    Route::get('/governments', [GovernmentController::class, 'index']);
    Route::get('/governments/{government}/areas', [AreaController::class, 'index']);

    Route::get('/vehicles', [VehicleController::class, 'index']);

    // get all shifts
    Route::get('/shifts', [ShiftController::class, 'index']);
    Route::get('/delivery/status/{id}', [AuthController::class, 'deliveryStatus']);
});

Route::prefix('delivery')->group(function () {

    // auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // password reset
    Route::get('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:api_delivery')->group(function () {

        // logout
        Route::post('/logout', [AuthController::class, 'logout']);

        // delivery profile
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/profile/update', [AuthController::class, 'updateProfile']);

        // delivery rates
        Route::get('/kitchen/rates', [RateStoreController::class, 'rates_by_kitchen']);
        Route::get('/client/rates', [RateStoreController::class, 'rates_by_client']);

        Route::get('/my-rates', [RateStoreController::class, 'myRates']);


        // start shift
        Route::post('/start-Shift', [ShiftController::class, 'startShift'])->middleware(EnsureDeliveryWorking::class);
        // end shift
        Route::post('/end-Shift', [ShiftController::class, 'endShift']);
        Route::post('/update-location', [ShiftController::class, 'updateLocation']);
        Route::get('/break-status', [OrderController::class, 'breakStatus']);
        Route::get(
            '/issue-types',
            [TicketController::class, 'issueTypes']
        );

        Route::get('/offers', [OfferController::class, 'offers']);
        Route::post('/offers/{offer}/apply', [OfferController::class, 'applyOffer']);
        // orders 
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);

            Route::post('/{order}/status', [OrderController::class, 'updateStatus'])->middleware(EnsureDeliveryWorking::class);
            Route::get('/reports/weekly', [OrderController::class, 'weeklyReports']);

            Route::post('/{order}/issue', [TicketController::class, 'store']);

            Route::post('/{order}/accept', [OrderController::class, 'accept'])->middleware(EnsureDeliveryWorking::class);         //notify
            Route::post('/{order}/reject', [OrderController::class, 'reject'])->middleware(EnsureDeliveryWorking::class);             //notify
            Route::post('/orders/{order}/no-response', [OrderController::class, 'noResponse']);
            Route::post('{order}/transfer', [OrderController::class, 'transfer']);
        });

        Route::get('/my-rewards', [RateStoreController::class, 'myRewards']);
        Route::post('/rates', [RateStoreController::class, 'storeRates']);
        Route::get('/rates', [RateStoreController::class, 'getRates']);

        Route::get('/points', [PointController::class, 'index']);

        Route::get('/app-pages', [AppPageController::class, 'appPage']);

        Route::post(
            '/firebase-token',
            [FirebaseTokenController::class, 'store']
        );

        Route::post(
            '/firebase-token/language',
            [FirebaseTokenController::class, 'updateLanguage']
        );

        Route::delete(
            '/firebase-token',
            [FirebaseTokenController::class, 'destroy']
        );

        Route::get(
            '/notifications',
            [NotificationController::class, 'index']
        );

        Route::post(
            '/notifications/{notification}/read',
            [NotificationController::class, 'markAsRead']
        );

        Route::post(
            '/notifications/read-all',
            [NotificationController::class, 'markAllAsRead']
        );
    });
});
